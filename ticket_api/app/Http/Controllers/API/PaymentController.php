<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
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

            $result = $this->paymentService->handlePaymentNotification($request->all());

            if ($result['success']) {
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
            'bank_transfer' => array_map(function($bank) {
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to request refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
