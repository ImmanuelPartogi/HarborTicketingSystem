<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected $bookingService;
    protected $paymentService;
    protected $scheduleService;

    /**
     * Create a new controller instance.
     *
     * @param BookingService $bookingService
     * @param PaymentService $paymentService
     * @param ScheduleService $scheduleService
     */
    public function __construct(
        BookingService $bookingService,
        PaymentService $paymentService,
        ScheduleService $scheduleService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Create a new booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.id_number' => 'required|string|max:30',
            'passengers.*.id_type' => 'required|in:KTP,SIM,PASPOR',
            'passengers.*.dob' => 'required|date_format:Y-m-d|before:today',
            'passengers.*.gender' => 'required|in:MALE,FEMALE',
            'vehicles' => 'nullable|array',
            'vehicles.*.type' => 'required_with:vehicles|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicles.*.license_plate' => 'required_with:vehicles|string|max:20|regex:/^[A-Z0-9 -]+$/i',
            'vehicles.*.weight' => 'nullable|numeric|min:0|max:50000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // First check availability
        $availabilityCheck = $this->scheduleService->checkAvailability(
            $request->schedule_id,
            $request->booking_date,
            count($request->passengers),
            !empty($request->vehicles) ? $request->vehicles[0]['type'] : null,
            !empty($request->vehicles) ? count($request->vehicles) : 0
        );

        if (!$availabilityCheck['available']) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule is not available for this date or capacity is not sufficient',
                'errors' => ['availability' => $availabilityCheck['reason']]
            ], 422);
        }

        try {
            // Use database transaction to ensure data integrity
            $booking = DB::transaction(function () use ($request) {
                return $this->bookingService->createBooking($request->all(), $request->user()->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $booking,
                    'booking_code' => $booking->booking_code,
                    'expiration' => $booking->expires_at
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Booking creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Booking creation failed',
                'error' => 'An unexpected error occurred during booking creation'
            ], 500);
        }
    }

    /**
     * Get user's bookings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $page = $request->query('page', 1);
        $perPage = min($request->query('per_page', 10), 50); // Maximum 50 per page

        $query = $request->user()->bookings()->with([
            'schedule.route',
            'schedule.ferry',
            'passengers',
            'vehicles'
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'last_page' => $bookings->lastPage()
                ]
            ]
        ]);
    }

    /**
     * Get booking details by booking code.
     *
     * @param  string  $bookingCode
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $bookingCode)
    {
        try {
            $booking = Booking::where('booking_code', $bookingCode)
                ->where('user_id', $request->user()->id)
                ->with([
                    'schedule.route',
                    'schedule.ferry',
                    'passengers',
                    'vehicles',
                    'tickets',
                    'payments' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $booking
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => 'The requested booking could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve booking',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Process payment for a booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request, $bookingCode)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:BANK_TRANSFER,VIRTUAL_ACCOUNT,E_WALLET,CREDIT_CARD',
            'payment_channel' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = Booking::where('booking_code', $bookingCode)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            if ($booking->status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not in pending status',
                    'error' => 'This booking cannot be paid for in its current status: ' . $booking->status
                ], 400);
            }

            // Check if payment is already in progress
            $pendingPayment = $booking->payments()->where('status', 'PENDING')->first();
            if ($pendingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already in progress',
                    'data' => [
                        'payment' => $pendingPayment,
                        'payment_url' => $this->paymentService->getPaymentUrl($pendingPayment)
                    ]
                ], 400);
            }

            // Use transaction to ensure consistency
            DB::beginTransaction();
            try {
                $payment = $this->paymentService->createPayment($booking, $request->all());
                $paymentUrl = $this->paymentService->getPaymentUrl($payment);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated successfully',
                    'data' => [
                        'payment' => $payment,
                        'payment_url' => $paymentUrl
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => 'The requested booking could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => 'An unexpected error occurred during payment processing'
            ], 500);
        }
    }

    /**
     * Cancel a booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $bookingCode)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = Booking::where('booking_code', $bookingCode)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            if (!in_array($booking->status, ['PENDING', 'CONFIRMED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking cannot be cancelled',
                    'error' => 'This booking cannot be cancelled in its current status: ' . $booking->status
                ], 400);
            }

            // Use transaction for cancellation
            DB::beginTransaction();
            try {
                $booking = $this->bookingService->cancelBooking(
                    $booking,
                    $request->reason,
                    'USER',
                    $request->user()->id
                );
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Booking cancelled successfully',
                    'data' => [
                        'booking' => $booking
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => 'The requested booking could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Booking cancellation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Booking cancellation failed',
                'error' => 'An unexpected error occurred during cancellation'
            ], 500);
        }
    }

    /**
     * Reschedule a booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function reschedule(Request $request, $bookingCode)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = Booking::where('booking_code', $bookingCode)
                ->where('user_id', $request->user()->id)
                ->with(['passengers', 'vehicles'])
                ->firstOrFail();

            if (!in_array($booking->status, ['CONFIRMED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking cannot be rescheduled',
                    'error' => 'This booking cannot be rescheduled in its current status: ' . $booking->status
                ], 400);
            }

            // Check if new schedule is available
            $passengerCount = $booking->passengers->count();
            $vehicleType = $booking->vehicles->isNotEmpty() ? $booking->vehicles->first()->type : null;
            $vehicleCount = $booking->vehicles->count();

            $availabilityCheck = $this->scheduleService->checkAvailability(
                $request->schedule_id,
                $request->booking_date,
                $passengerCount,
                $vehicleType,
                $vehicleCount
            );

            if (!$availabilityCheck['available']) {
                return response()->json([
                    'success' => false,
                    'message' => 'New schedule is not available',
                    'errors' => ['availability' => $availabilityCheck['reason']]
                ], 422);
            }

            // Use transaction for rescheduling
            DB::beginTransaction();
            try {
                $booking = $this->bookingService->rescheduleBooking(
                    $booking,
                    $request->all(),
                    'USER',
                    $request->user()->id
                );
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Booking rescheduled successfully',
                    'data' => [
                        'booking' => $booking
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => 'The requested booking could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Booking reschedule error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Booking reschedule failed',
                'error' => 'An unexpected error occurred during rescheduling'
            ], 500);
        }
    }

    /**
     * Get payment status for a booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentStatus(Request $request, $bookingCode)
    {
        try {
            $booking = Booking::where('booking_code', $bookingCode)
                ->where('user_id', $request->user()->id)
                ->with(['payments' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])
                ->firstOrFail();

            $latestPayment = $booking->payments->first();

            if (!$latestPayment) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'NO_PAYMENT',
                        'message' => 'No payment found for this booking'
                    ]
                ]);
            }

            $status = $this->paymentService->checkPaymentStatus($latestPayment);

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'payment' => $latestPayment
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => 'The requested booking could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Payment status check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
