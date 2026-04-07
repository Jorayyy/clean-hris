<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollGroupController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EmployeeMiddleware;

use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\SupportTicketController as EmployeeTicketController;
use App\Http\Controllers\Admin\SupportTicketController as AdminTicketController;
use App\Http\Controllers\WebBundyController;

// Public login/bundy routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/web-bundy/punch', [WebBundyController::class, 'punch'])->name('bundy.punch');

Route::get('/', function () {
    return redirect('/login');
});

// Admin Protected Routes
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::resource('attendance', AttendanceController::class);
    Route::resource('payroll', PayrollController::class);
    Route::resource('payroll-groups', PayrollGroupController::class);
    
    Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::get('salaries/{salary}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
    Route::put('salaries/{salary}', [SalaryController::class, 'update'])->name('salaries.update');
    Route::delete('salaries/{salary}', [SalaryController::class, 'destroy'])->name('salaries.destroy');

    Route::post('/payroll/{payroll}/process', [PayrollController::class, 'processPayroll'])->name('payroll.process');
    Route::get('/payroll/item/{id}/payslip', [PayrollController::class, 'generatePayslip'])->name('payroll.payslip');

    // Admin Tickets
    Route::get('admin/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('admin/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
    Route::put('admin/tickets/{id}', [AdminTicketController::class, 'update'])->name('admin.tickets.update');
});

// Employee Protected Routes
Route::middleware(['auth', EmployeeMiddleware::class])->prefix('employee')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/attendance', [EmployeeAttendanceController::class, 'index'])->name('employee.attendance');
    Route::get('/payslip/{id}', [EmployeeDashboardController::class, 'showPayslip'])->name('employee.payslip');
    
    // Employee Tickets
    Route::get('/tickets', [EmployeeTicketController::class, 'index'])->name('employee.tickets.index');
    Route::get('/tickets/create', [EmployeeTicketController::class, 'create'])->name('employee.tickets.create');
    Route::post('/tickets', [EmployeeTicketController::class, 'store'])->name('employee.tickets.store');
    Route::get('/tickets/{id}', [EmployeeTicketController::class, 'show'])->name('employee.tickets.show');
});

