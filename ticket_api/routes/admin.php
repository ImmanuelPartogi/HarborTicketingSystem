<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FerryController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

// Auth Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Protected Admin Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth:admin']], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);

    // Ferry Management
    Route::resource('ferries', FerryController::class);
    Route::get('admin/ferries/{ferry}/delete', [FerryController::class, 'delete'])->name('admin.ferries.delete');

    // Route Management
    Route::resource('routes', RouteController::class);

    // Schedule Management
    Route::resource('schedules', ScheduleController::class);
    Route::get('schedules/{schedule}/dates', [ScheduleController::class, 'dates'])->name('schedules.dates');
    Route::post('schedules/{schedule}/dates', [ScheduleController::class, 'storeDates'])->name('schedules.dates.store');
    Route::put('schedules/{schedule}/dates/{date}', [ScheduleController::class, 'updateDate'])->name('schedules.dates.update');
    Route::put('schedules/dates/{date}', [ScheduleController::class, 'updateDate'])->name('schedules.dates.update');
    Route::delete('schedules/dates/{date}', [ScheduleController::class, 'deleteDate'])->name('schedules.dates.destroy');

    // Booking Management
    Route::resource('bookings', BookingController::class);
    Route::get('bookings/{booking}/tickets', [BookingController::class, 'tickets'])->name('bookings.tickets');
    Route::post('bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');

    // Reports
    Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/routes', [ReportController::class, 'routes'])->name('reports.routes');
    Route::get('reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('reports/export/daily', [ReportController::class, 'exportDaily'])->name('reports.export.daily');
    Route::get('reports/export/monthly', [ReportController::class, 'exportMonthly'])->name('reports.export.monthly');
    Route::get('reports/export/routes', [ReportController::class, 'exportRoutes'])->name('reports.export.routes');
    Route::get('reports/export/occupancy', [ReportController::class, 'exportOccupancy'])->name('reports.export.occupancy');
});
