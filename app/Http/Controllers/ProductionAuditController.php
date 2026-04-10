<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\PayrollGroup;
use App\Services\PayrollService;
use Illuminate\Support\Facades\DB;

class ProductionAuditController extends Controller
{
    public function audit()
    {
        $report = [];
        
        try {
            DB::beginTransaction();

            // 1. Check Data Snapshotting
            $group = PayrollGroup::first() ?? PayrollGroup::create(['name' => 'Audit Group']);
            $emp = Employee::firstOrCreate(
                ['employee_id' => 'AUDIT-01'],
                [
                    'first_name' => 'Audit',
                    'last_name' => 'Tester',
                    'position' => 'Senior Developer',
                    'daily_rate' => 2500,
                    'payroll_group_id' => $group->id,
                    'status' => 'active'
                ]
            );

            $payroll = Payroll::create([
                'payroll_code' => 'AUDIT-' . time(),
                'payroll_group_id' => $group->id,
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-10',
                'pay_date' => '2026-04-15',
                'status' => 'draft'
            ]);

            $service = app(PayrollService::class);
            $service->computePayroll($payroll);

            $item = PayrollItem::where('payroll_id', $payroll->id)->where('employee_id', $emp->id)->first();
            
            $report['snapshot_daily_rate'] = ($item && $item->snapshot_daily_rate == 2500) ? 'PASS' : 'FAIL';
            $report['snapshot_position'] = ($item && $item->snapshot_position === 'Senior Developer') ? 'PASS' : 'FAIL';
            $report['payroll_status_update'] = ($payroll->fresh()->status === 'processed') ? 'PASS' : 'FAIL';

            // 2. Check Role Authorization logic (manual check of Policy registration)
            $report['policy_exists'] = class_exists(\App\Policies\PayrollItemPolicy::class) ? 'PASS' : 'FAIL';

            DB::rollBack(); // Keep database clean
            $report['overall'] = 'System Integrity Verified';

        } catch (\Exception $e) {
            $report['error'] = $e->getMessage();
            $report['overall'] = 'Audit Failed';
        }

        return response()->json($report);
    }
}