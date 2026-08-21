<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Models\Schedule;
use App\Models\ScheduleGroup;
use App\Models\Shift;
use App\Models\Site;
use App\Services\ScheduleValidationService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(protected ScheduleValidationService $scheduleValidator) {}

    public function index()
    {
        $shifts = Shift::all();
        $schedules = Schedule::with(['employee', 'payrollGroup', 'scheduleGroup', 'shift'])
            ->where('is_template', false)
            ->latest()
            ->get();
        $sites = Site::with('scheduleGroup')->withCount('employees')->get();
        $overriddenEmployees = Employee::whereHas('schedules', function ($query) {
            $query->where('is_template', false);
        })
            ->with(['site', 'schedules' => fn ($q) => $q->where('is_template', false)->with('shift')])
            ->get();
        $employeeCount = Employee::count();
        $directAssignmentCount = Schedule::where('is_template', false)
            ->whereNotNull('employee_id')
            ->distinct('employee_id')
            ->count('employee_id');

        return view('admin.schedules.index', compact('shifts', 'schedules', 'sites', 'overriddenEmployees', 'employeeCount', 'directAssignmentCount'));
    }

    public function shiftsIndex()
    {
        $shifts = Shift::all();

        return view('admin.schedules.shifts.index', compact('shifts'));
    }

    public function shiftsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'time_in' => 'required',
            'time_out' => 'required',
            'color' => 'required|string|max:7',
        ]);

        Shift::create($request->all());

        return back()->with('success', 'Shift created successfully.');
    }

    public function shiftsUpdate(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'time_in' => 'required',
            'time_out' => 'required',
            'color' => 'required|string|max:7',
        ]);

        $shift->update($request->all());

        return back()->with('success', 'Shift updated successfully.');
    }

    public function shiftsDestroy(Shift $shift)
    {
        $shift->delete();

        return back()->with('success', 'Shift deleted successfully.');
    }

    public function visualPlotting()
    {
        $employees = Employee::with(['schedules', 'site'])->get();
        $shifts = Shift::where('is_active', true)->get();
        $sites = Site::all();

        return view('admin.schedules.plotting.index', compact('employees', 'shifts', 'sites'));
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'shift_id' => 'required|exists:shifts,id',
            'days' => 'required|array',
        ]);

        $shift = Shift::find($request->shift_id);

        // §2.4: conflict warnings before writing; overrides must be acknowledged
        if (! $request->boolean('acknowledge_conflicts')) {
            $violations = $this->collectViolations(
                Employee::whereIn('id', $request->employee_ids)->get(),
                $request->days,
                $shift->time_in,
                $shift->time_out
            );

            if (! empty($violations)) {
                return response()->json([
                    'success' => false,
                    'requires_acknowledgment' => true,
                    'conflicts' => $violations,
                    'message' => 'Schedule conflicts detected. Review and acknowledge to proceed.',
                ], 422);
            }
        }

        foreach ($request->employee_ids as $empId) {
            Schedule::updateOrCreate(
                ['employee_id' => $empId, 'is_template' => false],
                [
                    'shift_id' => $shift->id,
                    'time_in' => $shift->time_in,
                    'time_out' => $shift->time_out,
                    'days' => $request->days,
                    'is_template' => false,
                    'assigned_by' => auth()->id(),
                ]
            );
        }

        if ($request->boolean('acknowledge_conflicts')) {
            $this->logConflictOverride('bulkAssign', [
                'employee_ids' => $request->employee_ids,
                'shift_id' => $shift->id,
                'days' => $request->days,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Schedules updated successfully.']);
    }

    public function create(Request $request)
    {
        $isTemplate = $request->has('template');
        $employees = Employee::all();
        $payrollGroups = PayrollGroup::all();
        $scheduleGroups = ScheduleGroup::all();
        $shifts = Shift::where('is_active', true)->get();

        return view('admin.schedules.create', compact('employees', 'payrollGroups', 'scheduleGroups', 'isTemplate', 'shifts'));
    }

    public function store(Request $request)
    {
        if ($request->has('is_template')) {
            $request->validate([
                'name' => 'required',
                'time_in' => 'required',
                'time_out' => 'required',
            ]);

            Schedule::create([
                'name' => $request->name,
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'is_template' => true,
                'days' => [], // Templates don't need assigned days
            ]);

            return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
        }

        $request->validate([
            'time_in' => 'required',
            'time_out' => 'required',
            'days' => 'required|array',
            'target_type' => 'required|in:individual,group,payroll',
            'employee_id' => 'required_if:target_type,individual|nullable|exists:employees,id',
            'schedule_group_id' => 'required_if:target_type,group|nullable|exists:schedule_groups,id',
            'payroll_group_id' => 'required_if:target_type,payroll|nullable|exists:payroll_groups,id',
        ]);

        // §2.4: conflict warnings before writing; overrides must be acknowledged
        if (! $request->boolean('acknowledge_conflicts')) {
            $violations = $this->collectViolations(
                $this->employeesForTarget($request),
                $request->days,
                $request->time_in,
                $request->time_out
            );

            if (! empty($violations)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('schedule_conflicts', $violations);
            }
        }

        $schedule = Schedule::create([
            'name' => $request->name,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'days' => $request->days,
            'employee_id' => $request->target_type === 'individual' ? $request->employee_id : null,
            'schedule_group_id' => $request->target_type === 'group' ? $request->schedule_group_id : null,
            'payroll_group_id' => $request->target_type === 'payroll' ? $request->payroll_group_id : null,
            'is_template' => false,
        ]);

        if ($request->boolean('acknowledge_conflicts')) {
            $this->logConflictOverride('store', ['schedule_id' => $schedule->id, 'days' => $request->days]);
        }

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $employees = Employee::all();
        $payrollGroups = PayrollGroup::all();
        $scheduleGroups = ScheduleGroup::all();

        return view('admin.schedules.edit', compact('schedule', 'employees', 'payrollGroups', 'scheduleGroups'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'time_in' => 'required',
            'time_out' => 'required',
            'days' => 'required|array',
            'target_type' => 'required|in:individual,group,payroll',
            'employee_id' => 'required_if:target_type,individual|nullable|exists:employees,id',
            'schedule_group_id' => 'required_if:target_type,group|nullable|exists:schedule_groups,id',
            'payroll_group_id' => 'required_if:target_type,payroll|nullable|exists:payroll_groups,id',
        ]);

        // §2.4: exclude the schedule being edited from its own overlap check
        if (! $request->boolean('acknowledge_conflicts')) {
            $violations = $this->collectViolations(
                $this->employeesForTarget($request),
                $request->days,
                $request->time_in,
                $request->time_out,
                $schedule->id
            );

            if (! empty($violations)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('schedule_conflicts', $violations);
            }
        }

        $schedule->update([
            'name' => $request->name,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'days' => $request->days,
            'employee_id' => $request->target_type === 'individual' ? $request->employee_id : null,
            'schedule_group_id' => $request->target_type === 'group' ? $request->schedule_group_id : null,
            'payroll_group_id' => $request->target_type === 'payroll' ? $request->payroll_group_id : null,
        ]);

        if ($request->boolean('acknowledge_conflicts')) {
            $this->logConflictOverride('update', ['schedule_id' => $schedule->id, 'days' => $request->days]);
        }

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Schedule deleted.');
    }

    protected function employeesForTarget(Request $request)
    {
        return match ($request->target_type) {
            'individual' => Employee::where('id', $request->employee_id)->get(),
            'group' => Employee::where('schedule_group_id', $request->schedule_group_id)->get(),
            'payroll' => Employee::where('payroll_group_id', $request->payroll_group_id)->get(),
            default => collect(),
        };
    }

    /**
     * @return string[] violations, prefixed with the employee name
     */
    protected function collectViolations($employees, array $days, string $timeIn, string $timeOut, ?int $ignoreScheduleId = null): array
    {
        $violations = [];

        foreach ($employees as $employee) {
            $violations = array_merge(
                $violations,
                $this->scheduleValidator->validateRecurringPattern($employee, $days, $timeIn, $timeOut, $ignoreScheduleId)
            );
        }

        return $violations;
    }

    /**
     * Explicit conflict overrides are audit-logged (§2.4).
     */
    protected function logConflictOverride(string $action, array $context): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'schedule_conflict_override',
            'model_type' => Schedule::class,
            'model_id' => $context['schedule_id'] ?? 0,
            'details' => [
                'description' => "Scheduler acknowledged conflicts during {$action}",
                'context' => $context,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
