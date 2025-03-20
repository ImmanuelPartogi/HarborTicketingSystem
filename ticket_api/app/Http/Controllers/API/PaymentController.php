<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $refundService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService
     * @param RefundService $refundService
     */
    public function __construct(PaymentService $paymentService, RefundService $refundService)
    {
        $this->paymentService = $paymentService;
        $this->refundService = $refundService;
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
            // Only log order ID and status, not the entire payload which may contain sensitive data
            $paymentData = $request->all();
            Log::info('Payment notification received', [
                'order_id' => $paymentData['order_id'] ?? 'not provided',
                'status' => $paymentData['transaction_status'] ?? 'not provided'
            ]);

            // Validate notification signature if available
            if (!$this->paymentService->validateNotificationSignature($request)) {
                Log::warning('Invalid payment notification signature');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 403);
            }

            DB::beginTransaction();
            try {
                $result = $this->paymentService->handlePaymentNotification($paymentData);
                DB::commit();

                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Notification processed successfully',
                    ]);
                } else {
                    Log::error('Failed to process payment notification', [
                        'order_id' => $paymentData['order_id'] ?? 'not provided',
                        'error' => $result['message']
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => $result['message'],
                    ], 400);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment notification: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment notification',
                'error' => 'An unexpected error occurred',
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
        try {
            // Get payment methods from config
            $vaBanks = config('payment.midtrans.payment_methods.virtual_account', []);
            $eWallets = config('payment.midtrans.payment_methods.e_wallet', []);
            $bankTransfers = config('payment.bank_transfer.accounts', []);

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
        } catch (\Exception $e) {
            Log::error('Error retrieving payment methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
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
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'error' => 'The requested payment could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving payment details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment details',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Request refund for a payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_code' => 'required|string|exists:bookings,booking_code',
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
            $booking = Booking::where('booking_code', $request->booking_code)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            // Check if the booking is eligible for refund
            if (!in_array($booking->status, ['CONFIRMED', 'RESCHEDULED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not eligible for refund',
                    'error' => 'This booking cannot be refunded in its current status: ' . $booking->status
                ], 400);
            }

            // Get the latest successful payment
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'SUCCESS')
                ->latest()
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No successful payment found for this booking',
                    'error' => 'No payment record found that can be refunded'
                ], 400);
            }

            // Calculate refund amount based on policy using the dedicated service
            $refundInfo = $this->refundService->calculateRefundAmount($booking);

            if ($refundInfo['refundAmount'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not eligible for refund',
                    'error' => $refundInfo['reason']
                ], 400);
            }

            // Process refund within a transaction
            DB::beginTransaction();
            try {
                $refund = $this->refundService->processRefund(
                    $booking,
                    $payment,
                    $refundInfo['refundAmount'],
                    $request->reason
                );
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Refund requested successfully',
                    'data' => [
                        'refund' => $refund,
                        'amount' => $refundInfo['refundAmount'],
                        'percentage' => $refundInfo['percentage'],
                        'status' => $refund->status
                    ],
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
            Log::error('Refund request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to request refund',
                'error' => 'An unexpected error occurred during refund processing'
            ], 500);
        }
    }

    /**
     * Get refund status.
     *
     * @param Request $request
     * @param string $refundId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRefundStatus(Request $request, $refundId)
    {
        try {
            $refund = \App\Models\Refund::findOrFail($refundId);

            // Check if the refund belongs to the authenticated user
            if ($refund->booking->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $status = $this->refundService->checkRefundStatus($refund);

            return response()->json([
                'success' => true,
                'data' => [
                    'refund' => $refund,
                    'status' => $status,
                    'estimated_completion' => $refund->estimated_completion_date
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund not found',
                'error' => 'The requested refund could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving refund status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve refund status',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
