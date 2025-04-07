<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected $bookingService;
    protected $paymentService;
    protected $ticketService;

    /**
     * Create a new controller instance.
     *
     * @param BookingService $bookingService
     * @param PaymentService $paymentService
     * @param TicketService $ticketService
     */
    public function __construct(
        BookingService $bookingService,
        PaymentService $paymentService,
        TicketService $ticketService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->ticketService = $ticketService;
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
            'passenger_count' => 'required|integer|min:1',
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
            Log::error('Booking creation failed', [
                'user_id' => $request->user()->id,
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

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
        $perPage = $request->query('per_page', 15);

        $query = $request->user()->bookings()->with(['schedule.route', 'schedule.ferry', 'passengers', 'vehicles']);

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookings
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
            'payment_method' => 'required|in:VIRTUAL_ACCOUNT,E_WALLET,BANK_TRANSFER,CREDIT_CARD',
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

        if ($booking->status !== 'PENDING' && $booking->status !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not in pending status'
            ], 400);
        }

        try {
            Log::info('Processing payment for booking', [
                'booking_id' => $booking->id,
                'booking_code' => $bookingCode,
                'payment_method' => $request->payment_method,
                'payment_channel' => $request->payment_channel
            ]);

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
            Log::error('Payment processing failed', [
                'booking_id' => $booking->id,
                'booking_code' => $bookingCode,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment for a booking using ID instead of booking code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPaymentById(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:VIRTUAL_ACCOUNT,E_WALLET,BANK_TRANSFER,CREDIT_CARD',
            'payment_channel' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari booking dengan ID numerik
        $booking = Booking::findOrFail($id);

        // Pastikan hanya pemilik booking yang dapat melakukan pembayaran
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($booking->status !== 'PENDING' && $booking->status !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not in pending status'
            ], 400);
        }

        try {
            Log::info('Processing payment for booking by ID', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'payment_method' => $request->payment_method,
                'payment_channel' => $request->payment_channel
            ]);

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
            Log::error('Payment processing failed', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'error' => $e->getMessage()
            ]);

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
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'booking_code' => $bookingCode,
                'error' => $e->getMessage()
            ]);

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
            Log::error('Booking reschedule failed', [
                'booking_id' => $booking->id,
                'booking_code' => $bookingCode,
                'error' => $e->getMessage()
            ]);

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

    /**
     * Get payment status for a booking by ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentStatusById(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
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

    /**
     * Generate tickets for a booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateTickets(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Only allow for CONFIRMED bookings
            if ($booking->status !== 'CONFIRMED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking must be confirmed to generate tickets'
                ], 400);
            }

            $ticketService = app(TicketService::class);
            $result = $ticketService->generateTicketsForBooking($booking);

            return response()->json([
                'success' => true,
                'message' => 'Tickets generated successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating tickets', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tickets for a booking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $bookingCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTickets(Request $request, $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', $request->user()->id)
            ->with(['tickets' => function ($query) {
                $query->with(['passenger', 'vehicle']);
            }])
            ->firstOrFail();

        if ($booking->tickets->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tickets' => [],
                    'message' => 'No tickets found for this booking'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => $booking->tickets
            ]
        ]);
    }

    /**
     * Get booking details by ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showById(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
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
}
