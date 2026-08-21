<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionController extends Controller
{
    /**
     * Punch fields an employee may propose to correct. Anything else in the
     * payload is dropped — approvals are the only path that mutates
     * attendances (§2.3).
     */
    protected array $correctableFields = [
        'time_in', 'time_out', 'lunch_out', 'lunch_in', 'break1_out', 'break1_in', 'break2_out', 'break2_in',
    ];

    public function index(Request $request)
    {
        $query = AttendanceCorrection::with(['employee', 'attendance', 'submitter', 'approver'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'employee_note' => 'nullable|string|max:2000',
            'new_values' => 'required|array',
            'new_values.*' => 'nullable|date_format:H:i:s,H:i',
        ]);

        $attendance = Attendance::findOrFail($validated['attendance_id']);

        $proposed = [];
        foreach ($this->correctableFields as $field) {
            if (array_key_exists($field, $validated['new_values']) && $validated['new_values'][$field] !== null) {
                $proposed[$field] = strlen($validated['new_values'][$field]) === 5
                    ? $validated['new_values'][$field].':00'
                    : $validated['new_values'][$field];
            }
        }

        if (empty($proposed)) {
            return back()->with('error', 'No correctable punch fields were provided.');
        }

        // Never mutate the Attendance row directly — route through approval.
        AttendanceCorrection::create([
            'employee_id' => $attendance->employee_id,
            'attendance_id' => $attendance->id,
            'submitted_by' => auth()->id(),
            'flagged_reason' => 'employee_submitted',
            'employee_note' => $validated['employee_note'] ?? null,
            'old_values' => array_intersect_key($attendance->getAttributes(), array_flip($this->correctableFields)),
            'new_values' => $proposed,
            'status' => AttendanceCorrection::STATUS_PENDING_MANAGER,
        ]);

        return back()->with('success', 'Correction submitted for manager approval.');
    }

    public function approve(Request $request, AttendanceCorrection $correction, PayrollService $payrollService)
    {
        if (! $correction->isOpen()) {
            return back()->with('error', 'This correction has already been resolved.');
        }

        DB::transaction(function () use ($correction, $payrollService) {
            $attendance = $correction->attendance;

            // Apply the approved values to the attendance row. The AuditObserver
            // captures this write on Attendance::updated.
            $attendance->update($correction->new_values);

            // Recompute derived stats with the already-loaded models.
            $stats = $payrollService->calculateAttendanceStats(
                $attendance->time_in,
                $attendance->time_out,
                $attendance->employee_id,
                $attendance->date,
                $attendance->fresh(),
                $attendance->employee
            );
            $attendance->update($stats);

            $correction->update([
                'status' => AttendanceCorrection::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Correction approved and applied to the attendance record.');
    }

    public function reject(Request $request, AttendanceCorrection $correction)
    {
        $request->validate(['rejection_note' => 'nullable|string|max:2000']);

        if (! $correction->isOpen()) {
            return back()->with('error', 'This correction has already been resolved.');
        }

        $correction->update([
            'status' => AttendanceCorrection::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'employee_note' => trim(($correction->employee_note ? $correction->employee_note."\n" : '').'Rejected: '.($request->input('rejection_note') ?? '')),
        ]);

        return back()->with('success', 'Correction rejected.');
    }
}
