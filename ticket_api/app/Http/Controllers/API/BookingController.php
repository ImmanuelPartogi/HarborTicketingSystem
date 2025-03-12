<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected $bookingService;
    protected $paymentService;

    /**
     * Create a new controller instance.
     *
     * @param BookingService $bookingService
     * @param PaymentService $paymentService
     */
    public function __construct(BookingService $bookingService, PaymentService $paymentService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
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
            'passengers.*.dob' => 'required|date_format:Y-m-d',
            'passengers.*.gender' => 'required|in:MALE,FEMALE',
            'vehicles' => 'nullable|array',
            'vehicles.*.type' => 'required_with:vehicles|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicles.*.license_plate' => 'required_with:vehicles|string|max:20',
            'vehicles.*.weight' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking = $this->bookingService->createBooking($request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $booking,
                    'booking_code' => $booking->booking_code
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking creation failed',
                'error' => $e->getMessage()
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
        $query = $request->user()->bookings()->with(['schedule.route', 'schedule.ferry', 'passengers', 'vehicles']);

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings
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

        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not in pending status'
            ], 400);
        }

        try {
            $payment = $this->paymentService->createPayment($booking, $request->all());
            $paymentUrl = $this->paymentService->getPaymentUrl($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment' => $payment,
                    'payment_url' => $paymentUrl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
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

        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!in_array($booking->status, ['PENDING', 'CONFIRMED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled'
            ], 400);
        }

        try {
            $booking = $this->bookingService->cancelBooking(
                $booking,
                $request->reason,
                'USER',
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking' => $booking
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cancellation failed',
                'error' => $e->getMessage()
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

        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!in_array($booking->status, ['CONFIRMED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be rescheduled'
            ], 400);
        }

        try {
            $booking = $this->bookingService->rescheduleBooking(
                $booking,
                $request->all(),
                'USER',
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking rescheduled successfully',
                'data' => [
                    'booking' => $booking
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking reschedule failed',
                'error' => $e->getMessage()
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
    }
}
