<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Employee;
use App\Models\Dtr;
use App\Models\Attendance;
use App\Models\DeductionType;
use App\Models\AllowanceType;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class PayrollItemController extends Controller
{
    public function create(Request $request)
    {
        $payroll = Payroll::findOrFail($request->payroll_id);
        
        $query = Employee::where('status', 'active');
        
        if ($payroll->employee_id) {
            // If it's an individual payroll, only show that employee
            $query->where('id', $payroll->employee_id);
        } else {
            // If it's a group payroll, show employees in that group
            $query->where('payroll_group_id', $payroll->payroll_group_id);
        }

        $employees = $query->get();

        $settings = \App\Models\AppSetting::first();

        // Filter out employees who already have a payslip in this payroll
        $existingEmployeeIds = PayrollItem::where('payroll_id', $payroll->id)
            ->pluck('employee_id')
            ->toArray();
            
        $employees = $employees->reject(function($employee) use ($existingEmployeeIds) {
            return in_array($employee->id, $existingEmployeeIds);
        });

        $deductionTypes = DeductionType::where('is_active', true)->get();
        $allowanceTypes = AllowanceType::where('is_active', true)->get();
        
        return view('payroll_items.create', compact('payroll', 'employees', 'deductionTypes', 'allowanceTypes', 'settings'));
    }

    public function getEmployeeBasis(Request $request)
    {
        $employeeId = $request->employee_id;
        $payrollId = $request->payroll_id;
        
        $payroll = Payroll::findOrFail($payrollId);
        $employee = Employee::findOrFail($employeeId);

        // Standardize dates to Y-m-d strings to avoid object/string mismatch in queries
        $startDate = \Carbon\Carbon::parse($payroll->start_date)->toDateString();
        $endDate = \Carbon\Carbon::parse($payroll->end_date)->toDateString();

        // Find finalized DTR summary that covers this period
        $dtr = Dtr::where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate)
            ->where('status', 'finalized')
            ->first();

        // Get detailed attendance logs for the period
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        $settings = \App\Models\AppSetting::first();

        return response()->json([
            'payroll_id' => $payrollId,
            'period' => $startDate . ' to ' . $endDate,
            'employee' => [
                'id' => $employee->id,
                'daily_rate' => $employee->daily_rate,
                'position' => $employee->position,
            ],
            'settings' => [
                'late_rate' => $settings->late_rate ?? 1.0,
                'undertime_rate' => $settings->undertime_rate ?? 1.0,
            ],
            'dtr' => $dtr ? [
                'total_regular_hours' => $dtr->total_regular_hours,
                'total_overtime_hours' => $dtr->total_overtime_hours,
                'total_night_diff_hours' => $dtr->total_night_diff_hours ?? 0,
                'total_holiday_hours' => $dtr->total_holiday_hours ?? 0,
                'incentives' => $dtr->incentives ?? 0,
                'is_ot_authorized' => (bool)$dtr->is_ot_authorized,
                'is_nd_authorized' => (bool)$dtr->is_nd_authorized,
                'is_holiday_authorized' => (bool)$dtr->is_holiday_authorized,
                'total_late_minutes' => $dtr->total_late_minutes,
                'total_undertime_minutes' => $dtr->total_undertime_minutes,
                'total_absent_days' => $dtr->total_absent_days,
            ] : null,
            'attendances' => $attendances
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payroll_id' => 'required',
            'employee_id' => 'required',
            'total_days' => 'required|numeric',
            'total_hours' => 'required|numeric',
            'basic_pay' => 'required|numeric',
            'overtime_pay' => 'nullable|numeric',
                'night_diff' => 'nullable|numeric',
            'bonuses' => 'nullable|numeric',
            'allowances' => 'nullable|array',
            'allowances.*.type' => 'nullable|string',
            'allowances.*.amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.type' => 'nullable|string',
            'deductions.*.amount' => 'nullable|numeric|min:0',
        ]);

        $settings = AppSetting::first();
        $allowances_log = $this->normalizeLineItems($request->input('allowances', []));
        $manualDeductions = $this->normalizeLineItems($request->input('deductions', []));
        $statutoryDeductions = $this->buildStatutoryDeductions((float) $data['basic_pay'], $settings);
        $deductions_log = array_values(array_merge($statutoryDeductions['lines'], $manualDeductions));
        $manualDeductionTotal = array_sum(array_column($manualDeductions, 'amount'));

        $overtime_pay = (float) ($request->input('overtime_pay', 0) ?: 0);
        $night_diff = (float) ($request->input('night_diff', 0) ?: 0);
        $bonuses = (float) ($request->input('bonuses', 0) ?: 0);
        $total_allowances = array_sum(array_column($allowances_log, 'amount'));
        $total_deductions = array_sum(array_column($deductions_log, 'amount'));
        $net_pay = max(0, ((float) $data['basic_pay']) + $overtime_pay + $night_diff + $bonuses + $total_allowances - $total_deductions);

        $payrollItem = PayrollItem::create([
            'payroll_id' => $data['payroll_id'],
            'employee_id' => $data['employee_id'],
            'total_days' => $data['total_days'],
            'total_hours' => $data['total_hours'],
            'basic_pay' => $data['basic_pay'],
            'overtime_pay' => $overtime_pay,
            'night_diff' => $night_diff,
            'bonuses' => $bonuses,
            'net_pay' => $net_pay,
            'allowances_json' => $allowances_log,
            'deductions_json' => $deductions_log,
            'deductions_sss' => $statutoryDeductions['sss'],
            'deductions_pagibig' => $statutoryDeductions['pagibig'],
            'deductions_philhealth' => $statutoryDeductions['philhealth'],
            'other_deductions' => $manualDeductionTotal,
        ]);

        // Auto-update payroll status to 'processing' if it was 'draft'
        $payroll = Payroll::find($data['payroll_id']);
        if ($payroll && $payroll->status === 'draft') {
            $payroll->update(['status' => 'processing']);
        }

        return redirect()->route('payroll.show', $data['payroll_id'])->with('success', 'Payslip created successfully.');
    }

    public function edit(PayrollItem $payrollItem)
    {
        return view('payroll_items.edit', compact('payrollItem'));
    }

    public function update(Request $request, PayrollItem $payrollItem)
    {
        $data = $request->validate([
            'total_days' => 'required|numeric',
            'total_hours' => 'required|numeric',
            'basic_pay' => 'required|numeric',
            'overtime_pay' => 'nullable|numeric',
            'night_diff' => 'nullable|numeric',
            'bonuses' => 'nullable|numeric',
            'deductions_sss' => 'nullable|numeric',
            'deductions_pagibig' => 'nullable|numeric',
            'deductions_philhealth' => 'nullable|numeric',
            'other_deductions' => 'nullable|numeric',
            'allowances' => 'nullable|array',
            'allowances.*.type' => 'nullable|string',
            'allowances.*.amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*.type' => 'nullable|string',
            'deductions.*.amount' => 'nullable|numeric|min:0',
        ]);

        $settings = AppSetting::first();
        $allowancesLog = $this->normalizeLineItems($request->input('allowances', $payrollItem->allowances_json ?? []));
        $manualDeductions = $this->normalizeLineItems($request->input('deductions', []));
        $hasLegacyDeductionFields = $request->filled('deductions_sss')
            || $request->filled('deductions_pagibig')
            || $request->filled('deductions_philhealth')
            || $request->filled('other_deductions');
        $manualDeductionTotal = array_sum(array_column($manualDeductions, 'amount'));

        if ($hasLegacyDeductionFields) {
            $deductionsLog = array_values(array_filter([
                ['type' => 'SSS', 'amount' => round((float) ($request->input('deductions_sss', 0) ?: 0), 2)],
                ['type' => 'PAGIBIG', 'amount' => round((float) ($request->input('deductions_pagibig', 0) ?: 0), 2)],
                ['type' => 'PHILHEALTH', 'amount' => round((float) ($request->input('deductions_philhealth', 0) ?: 0), 2)],
                ['type' => 'OTHER', 'amount' => round((float) ($request->input('other_deductions', 0) ?: 0), 2)],
            ], static fn (array $item) => $item['amount'] > 0));

            $statutoryDeductions = [
                'sss' => round((float) ($request->input('deductions_sss', 0) ?: 0), 2),
                'pagibig' => round((float) ($request->input('deductions_pagibig', 0) ?: 0), 2),
                'philhealth' => round((float) ($request->input('deductions_philhealth', 0) ?: 0), 2),
                'other' => round((float) ($request->input('other_deductions', 0) ?: 0), 2),
            ];
        } else {
            $statutoryDeductions = $this->buildStatutoryDeductions((float) $data['basic_pay'], $settings);
            $deductionsLog = array_values(array_merge($statutoryDeductions['lines'], $manualDeductions));
            $statutoryDeductions['other'] = $manualDeductionTotal;
        }

        $data['allowances_json'] = $allowancesLog;
        $data['deductions_json'] = $deductionsLog;
        $data['deductions_sss'] = $statutoryDeductions['sss'];
        $data['deductions_pagibig'] = $statutoryDeductions['pagibig'];
        $data['deductions_philhealth'] = $statutoryDeductions['philhealth'];
        $data['other_deductions'] = $statutoryDeductions['other'];
        $data['overtime_pay'] = (float) ($data['overtime_pay'] ?? 0);
        $data['night_diff'] = (float) ($data['night_diff'] ?? 0);
        $data['bonuses'] = (float) ($data['bonuses'] ?? 0);
        $data['net_pay'] = max(0, ((float) $data['basic_pay']) + $data['overtime_pay'] + $data['night_diff'] + $data['bonuses'] + array_sum(array_column($allowancesLog, 'amount')) - array_sum(array_column($deductionsLog, 'amount')));

        $payrollItem->update($data);

        return redirect()->route('payroll.show', $payrollItem->payroll_id)->with('success', 'Payslip updated successfully.');
    }

    public function destroy(PayrollItem $payrollItem)
    {
        $payrollId = $payrollItem->payroll_id;
        $payrollItem->delete();
        return redirect()->route('payroll.show', $payrollId)->with('success', 'Payslip deleted.');
    }

    private function normalizeLineItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = trim((string) ($item['type'] ?? ''));
            $amount = (float) ($item['amount'] ?? 0);

            if ($type === '' || $amount < 0) {
                continue;
            }

            $normalized[] = [
                'type' => $type,
                'amount' => round($amount, 2),
            ];
        }

        return $normalized;
    }

    private function buildStatutoryDeductions(float $basicPay, ?AppSetting $settings): array
    {
        $rates = [
            'sss' => $settings->sss_rate ?? 0.0450,
            'pagibig' => $settings->pagibig_rate ?? 0.0200,
            'philhealth' => $settings->philhealth_rate ?? 0.0500,
        ];

        $lines = [];
        $totals = [];

        foreach ($rates as $key => $rate) {
            $amount = round($basicPay * (float) $rate, 2);
            $totals[$key] = $amount;

            if ($amount > 0) {
                $lines[] = [
                    'type' => strtoupper($key),
                    'amount' => $amount,
                ];
            }
        }

        return [
            'lines' => $lines,
            'sss' => $totals['sss'] ?? 0,
            'pagibig' => $totals['pagibig'] ?? 0,
            'philhealth' => $totals['philhealth'] ?? 0,
            'other' => 0,
        ];
    }
}
