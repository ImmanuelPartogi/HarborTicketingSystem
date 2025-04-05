<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $ticketService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService
     * @param TicketService $ticketService
     */
    public function __construct(PaymentService $paymentService, TicketService $ticketService)
    {
        $this->paymentService = $paymentService;
        $this->ticketService = $ticketService;
    }

    /**
     * Handle payment notification from payment gateway.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notification(Request $request)
    {
        try {
            Log::info('Payment notification received', $request->all());

            // Validate that the request came from Midtrans
            $this->validateMidtransNotification($request);

            $result = $this->paymentService->handlePaymentNotification($request->all());

            if ($result['success']) {
                // If payment is successful and booking is confirmed, generate tickets
                if (isset($result['booking_status']) && $result['booking_status'] === 'CONFIRMED') {
                    $booking = Booking::where('booking_code', $result['booking_code'] ?? '')->first();
                    if ($booking) {
                        $this->ticketService->generateTicketsForBooking($booking);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Notification processed successfully',
                ]);
            } else {
                Log::error('Failed to process payment notification', $result);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment notification: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate that the notification came from Midtrans.
     *
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    protected function validateMidtransNotification(Request $request)
    {
        // For production, you would validate the Midtrans signature
        // This is just a simplified example
        if (!$request->has('transaction_id') || !$request->has('order_id')) {
            throw new \Exception('Invalid Midtrans notification: Missing required fields');
        }

        return true;
    }

    /**
     * Get payment methods available.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods()
    {
        // Get payment methods from config
        $vaBanks = config('payment.midtrans.payment_methods.virtual_account');
        $eWallets = config('payment.midtrans.payment_methods.e_wallet');
        $bankTransfers = config('payment.bank_transfer.accounts');

        $methods = [
            'virtual_account' => array_keys(array_filter($vaBanks)),
            'e_wallet' => array_keys(array_filter($eWallets)),
            'bank_transfer' => array_map(function ($bank) {
                return [
                    'bank' => $bank['bank'],
                    'account_number' => $bank['account_number'],
                    'account_name' => $bank['account_name'],
                ];
            }, $bankTransfers),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'payment_methods' => $methods,
            ],
        ]);
    }

    /**
     * Get payment details.
     *
     * @param string $paymentId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentDetails(Request $request, $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // Check if the payment belongs to the authenticated user
        $booking = $payment->booking;
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get payment status
        $status = $this->paymentService->checkPaymentStatus($payment);

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $payment,
                'status' => $status,
                'booking' => $booking,
            ],
        ]);
    }

    /**
     * Payment status page for redirect from payment gateways.
     *
     * @param Request $request
     * @param string $paymentId
     * @return \Illuminate\Http\Response
     */
    public function paymentStatusPage(Request $request, $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        $booking = $payment->booking;

        // Refresh payment status from Midtrans
        $status = $this->paymentService->checkPaymentStatus($payment);

        return view('payment.status', [
            'payment' => $payment,
            'booking' => $booking,
            'status' => $status
        ]);
    }

    /**
     * Get payment status directly.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentStatus($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            // Check status dengan Midtrans & update database
            $status = $this->paymentService->checkPaymentStatus($payment);

            if ($status === 'SUCCESS' && $payment->booking->tickets()->count() === 0) {
                Log::info('Payment is successful, auto-generating tickets', [
                    'payment_id' => $id,
                    'booking_id' => $payment->booking_id
                ]);

                // Pastikan booking sudah CONFIRMED
                $booking = $payment->booking;
                if ($booking->status !== 'CONFIRMED') {
                    $booking->update(['status' => 'CONFIRMED']);
                    $booking->refresh();
                }

                // Generate tickets langsung menggunakan ticketService yang sudah di-inject
                $this->ticketService->generateTicketsForBooking($booking);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'payment' => $payment,
                    'booking' => $payment->booking
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payment status', [
                'payment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual check and update payment status.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function manualCheckAndUpdate($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $oldStatus = $payment->status;

            // Force check status dengan Midtrans & update database
            $status = $this->paymentService->checkPaymentStatus($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => [
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'payment' => $payment,
                    'booking' => $payment->booking
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error manually updating payment status', [
                'payment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finish redirect URL for Midtrans.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $transactionStatus = $request->get('transaction_status');
        $fraudStatus = $request->get('fraud_status');

        Log::info('Midtrans finish redirect', $request->all());

        // Extract booking code from order_id (ORDER-BOOKING123-123456)
        $bookingCode = '';
        if (preg_match('/ORDER-(.*)-\d+/', $orderId, $matches)) {
            $bookingCode = $matches[1];
        }

        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return view('payment.error', [
                'message' => 'Booking not found'
            ]);
        }

        // Get the latest payment
        $payment = $booking->payments()->latest()->first();

        if (!$payment) {
            return view('payment.error', [
                'message' => 'Payment not found'
            ]);
        }

        // Update payment status if needed
        $status = $this->paymentService->checkPaymentStatus($payment);

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            // Successful payment
            return view('payment.success', [
                'booking' => $booking,
                'payment' => $payment
            ]);
        } elseif ($transactionStatus == 'pending') {
            // Pending payment
            return view('payment.pending', [
                'booking' => $booking,
                'payment' => $payment
            ]);
        } else {
            // Failed payment
            return view('payment.failed', [
                'booking' => $booking,
                'payment' => $payment,
                'status' => $transactionStatus
            ]);
        }
    }

    /**
     * Unfinish redirect URL for Midtrans.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function unfinish(Request $request)
    {
        Log::info('Midtrans unfinish redirect', $request->all());

        $orderId = $request->get('order_id');

        // Extract booking code from order_id
        $bookingCode = '';
        if (preg_match('/ORDER-(.*)-\d+/', $orderId, $matches)) {
            $bookingCode = $matches[1];
        }

        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return view('payment.error', [
                'message' => 'Booking not found'
            ]);
        }

        // Get the latest payment
        $payment = $booking->payments()->latest()->first();

        return view('payment.unfinished', [
            'booking' => $booking,
            'payment' => $payment
        ]);
    }

    /**
     * Error redirect URL for Midtrans.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function error(Request $request)
    {
        Log::info('Midtrans error redirect', $request->all());

        return view('payment.error', [
            'message' => 'Payment error',
            'details' => $request->all()
        ]);
    }

    /**
     * Request refund for a payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestRefund(Request $request)
    {
        $request->validate([
            'booking_code' => 'required|string|exists:bookings,booking_code',
            'reason' => 'required|string|max:255',
        ]);

        $booking = Booking::where('booking_code', $request->booking_code)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Check if the booking is eligible for refund
        if (!in_array($booking->status, ['CONFIRMED', 'RESCHEDULED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not eligible for refund',
            ], 400);
        }

        try {
            // Get the latest successful payment
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'SUCCESS')
                ->latest()
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No successful payment found for this booking',
                ], 400);
            }

            // Calculate refund amount based on policy
            $departureTime = $booking->booking_date . ' ' . $booking->schedule->departure_time;
            $departureTime = \Carbon\Carbon::parse($departureTime);
            $now = \Carbon\Carbon::now();

            $hoursUntilDeparture = $now->diffInHours($departureTime, false);
            $fullRefundHours = config('payment.cancellation_policy.full_refund_hours_before', 48);
            $partialRefundHours = config('payment.cancellation_policy.partial_refund_hours_before', 24);
            $partialRefundPercentage = config('payment.cancellation_policy.partial_refund_percentage', 50);

            $refundAmount = 0;

            if ($hoursUntilDeparture >= $fullRefundHours) {
                // Full refund
                $refundAmount = $payment->amount;
            } elseif ($hoursUntilDeparture >= $partialRefundHours) {
                // Partial refund
                $refundAmount = $payment->amount * ($partialRefundPercentage / 100);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not eligible for refund as it is less than ' . $partialRefundHours . ' hours until departure',
                ], 400);
            }

            // Process refund
            $refund = $this->paymentService->processRefund($booking, $refundAmount, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Refund requested successfully',
                'data' => [
                    'refund' => $refund,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Refund request failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
