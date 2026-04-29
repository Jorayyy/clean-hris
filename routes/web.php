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
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EmployeeMiddleware;

use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\SupportTicketController as EmployeeTicketController;
use App\Http\Controllers\Admin\SupportTicketController as AdminTicketController;
use App\Http\Controllers\Admin\AuthorizedNetworkController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\DeductionTypeController;
use App\Http\Controllers\PayrollItemController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Employee\AttendanceCalendarController;
use App\Http\Controllers\WebBundyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SiteController;

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
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::get('attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::get('attendance/{employee}/monthly', [AttendanceController::class, 'getMonthlyAttendance'])->name('attendance.monthly');
    Route::get('attendance/{employee}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::resource('employees', EmployeeController::class);
    Route::resource('sites', SiteController::class);
    Route::resource('users', UserController::class);
    Route::resource('attendance', AttendanceController::class)->except(['show', 'create', 'edit']);
    Route::resource('payroll', PayrollController::class);
    Route::get('/payroll-items/basis', [PayrollItemController::class, 'getEmployeeBasis'])->name('payroll-items.basis');
    Route::resource('payroll-items', PayrollItemController::class);
    Route::get('/api/finalized-dtrs', [PayrollController::class, 'getFinalizedDtrs'])->name('payroll.api.finalized-dtrs');
    
    // Payroll Groups (Restricted to Super Admin)
    Route::middleware(['super_admin'])->group(function () {
        Route::resource('payroll-groups', PayrollGroupController::class);
    });

    Route::resource('schedules', ScheduleController::class);
    Route::resource('authorized-networks', AuthorizedNetworkController::class);
    Route::get('admin/settings', [AppSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('admin/settings', [AppSettingController::class, 'update'])->name('admin.settings.update');
    Route::resource('admin/settings/deductions', DeductionTypeController::class)->names('admin.settings.deductions');
    
    Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::get('salaries/{salary}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
    Route::put('salaries/{salary}', [SalaryController::class, 'update'])->name('salaries.update');
    Route::delete('salaries/{salary}', [SalaryController::class, 'destroy'])->name('salaries.destroy');

    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::get('/payroll/item/{id}/payslip', [PayrollController::class, 'generatePayslip'])->name('payroll.payslip');

    // DTR Management
    Route::prefix('admin/dtrs')->name('admin.dtrs.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\DtrController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\DtrController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\DtrController::class, 'store'])->name('store');
        Route::get('/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'show'])->name('show');
        Route::put('/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'update'])->name('update');
        Route::patch('/{dtr}/verify', [App\Http\Controllers\Admin\DtrController::class, 'verify'])->name('verify');
        Route::patch('/{dtr}/finalize', [App\Http\Controllers\Admin\DtrController::class, 'finalize'])->name('finalize');
        Route::post('/batch-verify', [App\Http\Controllers\Admin\DtrController::class, 'batchVerify'])->name('batch-verify');
        Route::post('/batch-finalize', [App\Http\Controllers\Admin\DtrController::class, 'batchFinalize'])->name('batch-finalize');
        Route::post('/batch-delete', [App\Http\Controllers\Admin\DtrController::class, 'batchDestroy'])->name('batch-delete');
        Route::post('/batch-authorize', [App\Http\Controllers\Admin\DtrController::class, 'batchAuthorize'])->name('batch-authorize');
        Route::delete('/{dtr}', [App\Http\Controllers\Admin\DtrController::class, 'destroy'])->name('destroy');
    });

    // Admin Tickets
    Route::get('admin/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('admin/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
    Route::put('admin/tickets/{id}', [AdminTicketController::class, 'update'])->name('admin.tickets.update');

    // Announcements
    Route::resource('announcements', AdminAnnouncementController::class);

    // Roles & Permissions (Now restricted to Super Admin only)
    Route::middleware(['super_admin'])->group(function () {
        Route::get('admin/roles', [App\Http\Controllers\Admin\RolePermissionController::class, 'index'])->name('admin.roles.index');
        Route::post('admin/roles', [App\Http\Controllers\Admin\RolePermissionController::class, 'store'])->name('admin.roles.store');
        Route::put('admin/roles/{role}', [App\Http\Controllers\Admin\RolePermissionController::class, 'update'])->name('admin.roles.update');
        Route::delete('admin/roles/{role}', [App\Http\Controllers\Admin\RolePermissionController::class, 'destroy'])->name('admin.roles.destroy');
    });

    // Audit Logs
    Route::get('admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
    Route::post('admin/audit-logs/prune', [AuditLogController::class, 'prune'])->name('admin.audit-logs.prune');

    // Queue Monitor
    Route::get('admin/queue-monitor', [QueueMonitorController::class, 'index'])->name('admin.queue-monitor.index');

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
    Route::get('/tickets/tk-create', [EmployeeTicketController::class, 'tkCreate'])->name('employee.tickets.tk-create');
    Route::post('/tickets', [EmployeeTicketController::class, 'store'])->name('employee.tickets.store');
    Route::get('/tickets/{id}', [EmployeeTicketController::class, 'show'])->name('employee.tickets.show');

    // Employee DTR View
    Route::get('/dtr', [\App\Http\Controllers\Employee\DtrController::class, 'index'])->name('employee.dtr.index');
    Route::get('/dtr/{id}', [\App\Http\Controllers\Employee\DtrController::class, 'show'])->name('employee.dtr.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'showEmployee'])->name('employee.profile');
});

