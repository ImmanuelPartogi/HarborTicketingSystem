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
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\HelpController;

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
    Route::put('routes/{route}/update-status', [RouteController::class, 'updateStatus'])->name('routes.update-status');

    // Schedule Management
    Route::resource('schedules', ScheduleController::class);
    Route::get('schedules/{schedule}/dates', [ScheduleController::class, 'dates'])->name('schedules.dates');
    Route::post('schedules/{schedule}/dates', [ScheduleController::class, 'storeDates'])->name('schedules.dates.store');
    Route::put('schedules/{schedule}/dates/{date}', [ScheduleController::class, 'updateDate'])->name('schedules.dates.update');
    Route::put('schedules/dates/{date}', [ScheduleController::class, 'updateDate'])->name('schedules.dates.update');
    Route::delete('schedules/dates/{date}', [ScheduleController::class, 'deleteDate'])->name('schedules.dates.destroy');
    Route::put('admin/schedules/{schedule}/dates/{dateId}', [ScheduleController::class, 'updateDate'])->name('schedules.dates.update');
    Route::put('schedules/{schedule}/reschedule', [ScheduleController::class, 'reschedule'])->name('schedules.reschedule');
    Route::delete('/schedules/dates/{dateId}', [ScheduleController::class, 'deleteDate'])->name('admin.schedules.dates.delete');

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

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/update-system', [SettingsController::class, 'updateSystem'])->name('settings.update-system');
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::post('/settings/update-profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');

    // Help Routes
    Route::get('/help', [HelpController::class, 'index'])->name('help');
    Route::get('/help/topic/{topic}', [HelpController::class, 'topic'])->name('help.topic');
    Route::get('/help/contact', [HelpController::class, 'contactSupport'])->name('help.contact');
    Route::post('/help/send-support', [HelpController::class, 'sendSupportRequest'])->name('help.send-support');
});
