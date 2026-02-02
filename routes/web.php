<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceImportController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('attendance.dashboard');
});

Route::prefix('attendance')->name('attendance.')->group(function () {
    // Import
    Route::get('/import', [AttendanceImportController::class, 'index'])->name('import');
    Route::post('/upload', [AttendanceImportController::class, 'upload'])->name('upload');
    Route::get('/status/{importId}', [AttendanceImportController::class, 'status'])->name('status');

    // Reports
    Route::get('/report', [AttendanceReportController::class, 'index'])->name('report');
    Route::get('/report/data', [AttendanceReportController::class, 'getData'])->name('report.data');

    // Professional Reports
    Route::get('/monthly', [AttendanceReportController::class, 'monthly'])->name('monthly');
    Route::get('/monthly/data', [AttendanceReportController::class, 'getMonthlyData'])->name('monthly.data');

    // Dashboard
    Route::get('/dashboard', [AttendanceReportController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/employee-stats', [DashboardController::class, 'getEmployeeChartData'])->name('dashboard.employee_stats');
});
