<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Http\Request;

class IndividualScheduleController extends Controller
{
    public function index()
    {
        // Show employees who have individual overrides
        $employees = Employee::whereHas('schedules')->with('site', 'schedules.shift')->get();
        return view('admin.settings.individual-schedules.index', compact('employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('last_name')->get();
        $shifts = Shift::where('is_active', true)->get();
        return view('admin.settings.individual-schedules.create', compact('employees', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'day_0_shift_id' => 'nullable|exists:shifts,id',
            'day_1_shift_id' => 'nullable|exists:shifts,id',
            'day_2_shift_id' => 'nullable|exists:shifts,id',
            'day_3_shift_id' => 'nullable|exists:shifts,id',
            'day_4_shift_id' => 'nullable|exists:shifts,id',
            'day_5_shift_id' => 'nullable|exists:shifts,id',
            'day_6_shift_id' => 'nullable|exists:shifts,id',
        ]);

        // Clear existing schedules for this employee to update
        Schedule::where('employee_id', $validated['employee_id'])->delete();

        for ($i = 0; $i < 7; $i++) {
            $shiftId = $validated["day_{$i}_shift_id"] ?? null;
            $isRestDay = ($request->has("day_{$i}_rest_day") || !$shiftId);

            Schedule::create([
                'employee_id' => $validated['employee_id'],
                'day_of_week' => $i,
                'shift_id' => $isRestDay ? null : $shiftId,
                'is_rest_day' => $isRestDay,
            ]);
        }

        return redirect()->route('admin.settings.individual-schedules.index')
            ->with('success', 'Individual schedule override created successfully.');
    }

    public function edit(Employee $employee)
    {
        $employee->load('schedules');
        $shifts = Shift::where('is_active', true)->get();
        
        // Map current schedules to day index for easier view access
        $currentSchedules = $employee->schedules->keyBy('day_of_week');
        
        return view('admin.settings.individual-schedules.edit', compact('employee', 'shifts', 'currentSchedules'));
    }

    public function destroy(Employee $employee)
    {
        $employee->schedules()->delete();
        return back()->with('info', "Individual override for {$employee->full_name} has been removed. They will now follow their Site Schedule.");
    }
}
