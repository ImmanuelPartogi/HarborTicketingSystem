<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FerryController;
use App\Http\Controllers\API\RouteController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\VehicleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public Ferry Data
    Route::get('/ferries', [FerryController::class, 'index']);
    Route::get('/ferries/{id}', [FerryController::class, 'show']);

    // Public Route Data
    Route::get('/routes', [RouteController::class, 'index']);
    Route::get('/routes/{id}', [RouteController::class, 'show']);
    Route::get('/routes/search', [RouteController::class, 'search']);
    Route::get('/routes/origins', [RouteController::class, 'origins']);
    Route::get('/routes/destinations', [RouteController::class, 'destinations']);
    Route::get('/routes/origin/{origin}/destinations', [RouteController::class, 'destinationsForOrigin']);

    // Schedule Search
    Route::get('/schedules/search', [ScheduleController::class, 'search']);
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    Route::post('/schedules/check-availability', [ScheduleController::class, 'checkAvailability']);

    // Payment Notification Callback (from Midtrans)
    Route::post('/payments/notification', [PaymentController::class, 'notification']);
});

// Protected Routes (requires authentication)
Route::prefix('v1')->middleware(['api.auth', 'throttle:60,1'])->group(function () {
    // User Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Booking
    Route::post('/bookings', [BookingController::class, 'create']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{bookingCode}', [BookingController::class, 'show']);
    Route::post('/bookings/{bookingCode}/pay', [BookingController::class, 'processPayment']);
    Route::post('/bookings/{bookingCode}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{bookingCode}/reschedule', [BookingController::class, 'reschedule']);
    Route::get('/bookings/{bookingCode}/payment-status', [BookingController::class, 'paymentStatus']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{ticketCode}', [TicketController::class, 'show']);
    Route::get('/tickets/{ticketCode}/download', [TicketController::class, 'download']);
    Route::get('/tickets/{ticketCode}/qr-code', [TicketController::class, 'qrCode']);
    Route::post('/tickets/{ticketCode}/check-in', [TicketController::class, 'checkIn']);
});

// Staff/Operator Routes (requires special token)
Route::prefix('v1/staff')->middleware(['api.auth', 'throttle:60,1'])->group(function () {
    Route::post('/tickets/validate', [TicketController::class, 'validate']);
    Route::post('/tickets/mark-boarded', [TicketController::class, 'markAsBoarded']);
});
