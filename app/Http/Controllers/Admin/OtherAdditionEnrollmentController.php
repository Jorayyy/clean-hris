<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowanceType;
use App\Models\Employee;
use App\Models\OtherAdditionEnrollment;
use App\Models\PayrollGroup;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OtherAdditionEnrollmentController extends Controller
{
    public function index()
    {
        $allowanceTypes = AllowanceType::where('is_active', true)->get();
        $payrollGroups = PayrollGroup::all();
        $recentEnrollments = OtherAdditionEnrollment::with(['employee', 'allowanceType'])
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.payroll.other_addition_enrollment', compact('allowanceTypes', 'payrollGroups', 'recentEnrollments'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'pay_type' => 'required',
            'payroll_group_id' => 'required',
            'payroll_period' => 'required',
            'action' => 'required',
            'upload_template' => 'required|file|mimes:csv,txt,xlsx'
        ]);

        $dates = explode(' to ', $request->payroll_period);
        $startDate = Carbon::parse($dates[0])->toDateString();
        $endDate = Carbon::parse($dates[1])->toDateString();

        if ($request->action === 'Reset other addition') {
            OtherAdditionEnrollment::where('payroll_period_start', $startDate)
                ->where('payroll_period_end', $endDate)
                ->delete();
        }

        $file = $request->file('upload_template');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Assuming first line is header

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $employeeId = $row[0]; // Employee ID
            $additionId = $row[2]; // other_addition_id
            $amount = $row[3];     // Amount

            $employee = Employee::where('employee_id', $employeeId)->first();
            $allowanceType = AllowanceType::find($additionId);

            if ($employee && $allowanceType) {
                OtherAdditionEnrollment::create([
                    'employee_id' => $employee->id,
                    'allowance_type_id' => $allowanceType->id,
                    'amount' => $amount,
                    'payroll_period_start' => $startDate,
                    'payroll_period_end' => $endDate,
                    'status' => 'pending'
                ]);
                $count++;
            }
        }
        fclose($handle);

        return redirect()->back()->with('success', "Successfully imported $count records.");
    }

    public function downloadTemplate()
    {
        $headers = ['Employee ID', 'Name [Optional]', 'other_addition_id', 'Amount'];
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=other_addition_template.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
