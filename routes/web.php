<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollGroupController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('employees', EmployeeController::class);
Route::resource('attendance', AttendanceController::class);
Route::resource('payroll', PayrollController::class);
Route::resource('payroll-groups', PayrollGroupController::class);

Route::post('/payroll/{payroll}/process', [PayrollController::class, 'processPayroll'])->name('payroll.process');
Route::get('/payroll/item/{id}/payslip', [PayrollController::class, 'generatePayslip'])->name('payroll.payslip');

