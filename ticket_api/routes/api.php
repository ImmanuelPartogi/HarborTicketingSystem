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
use App\Http\Controllers\API\TicketGenerationController;

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

    // Schedule Routes - Update to use the new getSchedule method for individual schedules
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::get('/schedules/search', [ScheduleController::class, 'search']);
    Route::get('/schedules/{id}', [ScheduleController::class, 'getSchedule']); // Changed method
    Route::post('/schedules/check-availability', [ScheduleController::class, 'checkAvailability']);

    // Payment Notification Callback (from Midtrans)
    Route::get('/payments/status/{id}', [PaymentController::class, 'getPaymentStatus']);

    // Dan tambahkan route public untuk callback (memastikan tidak membutuhkan autentikasi)
    Route::post('/payments/notification', [PaymentController::class, 'notification']);
    Route::get('/payments/finish', [PaymentController::class, 'finish']);
    Route::get('/payments/unfinish', [PaymentController::class, 'unfinish']);
    Route::get('/payments/error', [PaymentController::class, 'error']);
    Route::get('/payments/{id}/generate-tickets', [TicketGenerationController::class, 'autoGenerateTickets']);
});

// Rest of your routes remain unchanged
// Protected Routes (requires authentication)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
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
    Route::post('/bookings/id/{id}/pay', [BookingController::class, 'processPaymentById']);
    Route::get('/bookings/id/{id}', [BookingController::class, 'showById']);
    Route::get('/bookings/id/{id}/payment-status', [BookingController::class, 'paymentStatusById']);
    Route::post('/bookings/{id}/generate-tickets', [BookingController::class, 'generateTickets']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{ticketCode}', [TicketController::class, 'show']);
    Route::get('/tickets/{ticketCode}/download', [TicketController::class, 'download']);
    Route::get('/tickets/{ticketCode}/qr-code', [TicketController::class, 'qrCode']);
    Route::post('/tickets/{ticketCode}/check-in', [TicketController::class, 'checkIn']);
});

// Staff/Operator Routes
Route::prefix('v1/staff')->middleware(['auth:sanctum', 'verify.staff', 'throttle:60,1'])->group(function () {
    Route::post('/tickets/validate', [TicketController::class, 'validateTicket']);
    Route::post('/tickets/mark-boarded', [TicketController::class, 'markAsBoarded']);
});
