<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
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
use App\Http\Controllers\Admin\AuthorizedNetworkController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Employee\AttendanceCalendarController;
use App\Http\Controllers\WebBundyController;
use App\Http\Controllers\ProfileController;

// Public login/bundy routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/web-bundy', [WebBundyController::class, 'showBundy'])->name('bundy.show');
Route::post('/web-bundy/punch', [WebBundyController::class, 'punch'])->name('bundy.punch');

Route::get('/', function () {
    return redirect('/login');
});

// Admin Protected Routes
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('attendance/{employee}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::resource('employees', EmployeeController::class);
    Route::resource('attendance', AttendanceController::class)->except(['show']);
    Route::resource('payroll', PayrollController::class);
    Route::resource('payroll-groups', PayrollGroupController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::resource('authorized-networks', AuthorizedNetworkController::class);
    Route::get('admin/settings', [AppSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('admin/settings', [AppSettingController::class, 'update'])->name('admin.settings.update');
    
    Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::get('salaries/{salary}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
    Route::put('salaries/{salary}', [SalaryController::class, 'update'])->name('salaries.update');
    Route::delete('salaries/{salary}', [SalaryController::class, 'destroy'])->name('salaries.destroy');

    Route::post('/payroll/{payroll}/process', [PayrollController::class, 'processPayroll'])->name('payroll.process');
    Route::get('/payroll/item/{id}/payslip', [PayrollController::class, 'generatePayslip'])->name('payroll.payslip');

    // DTR Management
    Route::prefix('admin/dtrs')->name('admin.dtrs.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\DtrController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\DtrController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\DtrController::class, 'store'])->name('store');
        Route::get('/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'show'])->name('show');
        Route::patch('/{dtr}/verify', [App\Http\Controllers\Admin\DtrController::class, 'verify'])->name('verify');
        Route::patch('/{dtr}/finalize', [App\Http\Controllers\Admin\DtrController::class, 'finalize'])->name('finalize');
        Route::delete('/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'destroy'])->name('destroy');
    });

    // Admin Tickets
    Route::get('admin/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('admin/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
    Route::put('admin/tickets/{id}', [AdminTicketController::class, 'update'])->name('admin.tickets.update');

    // Profile
    Route::get('/admin/profile', [ProfileController::class, 'showAdmin'])->name('admin.profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Employee Protected Routes
Route::middleware(['auth', EmployeeMiddleware::class])->prefix('employee')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/attendance', [AttendanceCalendarController::class, 'index'])->name('employee.attendance');
    Route::get('/payslip/{id}', [EmployeeDashboardController::class, 'showPayslip'])->name('employee.payslip');
    
    // Employee Tickets
    Route::get('/tickets', [EmployeeTicketController::class, 'index'])->name('employee.tickets.index');
    Route::get('/tickets/create', [EmployeeTicketController::class, 'create'])->name('employee.tickets.create');
    Route::post('/tickets', [EmployeeTicketController::class, 'store'])->name('employee.tickets.store');
    Route::get('/tickets/{id}', [EmployeeTicketController::class, 'show'])->name('employee.tickets.show');

    // Employee DTR View
    Route::get('/dtr', [\App\Http\Controllers\Employee\DtrController::class, 'index'])->name('employee.dtr.index');
    Route::get('/dtr/{id}', [\App\Http\Controllers\Employee\DtrController::class, 'show'])->name('employee.dtr.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'showEmployee'])->name('employee.profile');
});

