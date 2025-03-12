<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;

class PaymentService
{
    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        // Initialize Midtrans configuration
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production');
        MidtransConfig::$isSanitized = config('services.midtrans.sanitize', true);
    }

    /**
     * Create a new payment.
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return Payment
     */
    public function createPayment(Booking $booking, array $paymentData)
    {
        // Set expiry time (24 hours from now)
        $expiryTime = Carbon::now()->addHours(24);

        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'payment_method' => $paymentData['payment_method'],
            'payment_channel' => $paymentData['payment_channel'],
            'status' => 'PENDING',
            'expiry_date' => $expiryTime,
        ]);

        // Create Midtrans transaction
        if ($payment->payment_method === 'VIRTUAL_ACCOUNT' || $payment->payment_method === 'E_WALLET') {
            $this->createMidtransTransaction($payment, $booking);
        }

        return $payment;
    }

    /**
     * Create a Midtrans transaction.
     *
     * @param Payment $payment
     * @param Booking $booking
     * @return array
     */
    private function createMidtransTransaction(Payment $payment, Booking $booking)
    {
        $primaryPassenger = $booking->primaryPassenger();
        $user = $booking->user;

        $items = [];

        // Add passenger tickets to items
        foreach ($booking->passengers as $passenger) {
            $items[] = [
                'id' => 'PASSENGER-' . $passenger->id,
                'price' => $booking->schedule->route->base_price,
                'quantity' => 1,
                'name' => 'Tiket Penumpang: ' . $passenger->name,
            ];
        }

        // Add vehicle tickets to items if any
        foreach ($booking->vehicles as $vehicle) {
            $vehiclePrice = $booking->schedule->route->getPriceForVehicle($vehicle->type);
            $items[] = [
                'id' => 'VEHICLE-' . $vehicle->id,
                'price' => $vehiclePrice,
                'quantity' => 1,
                'name' => 'Tiket Kendaraan: ' . $vehicle->type_name . ' ' . $vehicle->license_plate,
            ];
        }

        // Transaction details
        $transactionDetails = [
            'order_id' => $booking->booking_code . '-' . time(),
            'gross_amount' => $booking->total_amount,
        ];

        // Customer details
        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];

        // Transaction data
        $transactionData = [
            'transaction_details' => $transactionDetails,
            'item_details' => $items,
            'customer_details' => $customerDetails,
            'expiry' => [
                'start_time' => Carbon::now()->format('Y-m-d H:i:s O'),
                'unit' => 'hour',
                'duration' => 24,
            ],
        ];

        // Add specific payment options based on method
        if ($payment->payment_method === 'VIRTUAL_ACCOUNT') {
            $transactionData['enabled_payments'] = ['bca_va', 'bni_va', 'bri_va', 'mandiri_bill'];
        } elseif ($payment->payment_method === 'E_WALLET') {
            $transactionData['enabled_payments'] = ['gopay', 'shopeepay'];
        }

        try {
            // Create Midtrans Snap transaction
            $snapToken = MidtransSnap::getSnapToken($transactionData);

            // Update payment with transaction details
            $payment->update([
                'transaction_id' => $transactionDetails['order_id'],
                'payload' => json_encode([
                    'snap_token' => $snapToken,
                    'transaction_data' => $transactionData,
                ]),
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'payment' => $payment,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());

            // Update payment status to failed
            $payment->update([
                'status' => 'FAILED',
                'payload' => json_encode([
                    'error' => $e->getMessage(),
                    'transaction_data' => $transactionData,
                ]),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat transaksi pembayaran.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment notification from Midtrans.
     *
     * @param array $notificationData
     * @return array
     */
    public function handlePaymentNotification(array $notificationData)
    {
        $orderId = $notificationData['order_id'];
        $transactionStatus = $notificationData['transaction_status'];
        $fraudStatus = $notificationData['fraud_status'] ?? null;

        // Extract booking_code from order_id (format: BOOKING_CODE-TIMESTAMP)
        $bookingCode = explode('-', $orderId)[0];

        // Find the booking and payment
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Booking not found',
            ];
        }

        $payment = Payment::where('booking_id', $booking->id)
            ->where('transaction_id', $orderId)
            ->first();

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found',
            ];
        }

        // Update payment based on notification
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                // Payment challenged by fraud detection
                $payment->status = 'PENDING';
            } else if ($fraudStatus == 'accept') {
                // Payment successful
                $payment->status = 'SUCCESS';
                $payment->payment_date = Carbon::now();
            }
        } else if ($transactionStatus == 'settlement') {
            // Payment successful
            $payment->status = 'SUCCESS';
            $payment->payment_date = Carbon::now();
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            // Payment failed
            $payment->status = $transactionStatus == 'expire' ? 'EXPIRED' : 'FAILED';
        } else if ($transactionStatus == 'pending') {
            // Payment pending
            $payment->status = 'PENDING';
        }

        $payment->payload = json_encode(array_merge(
            json_decode($payment->payload ?? '{}', true),
            ['notification' => $notificationData]
        ));

        $payment->save();

        // If payment is successful, update booking status
        if ($payment->status === 'SUCCESS' && $booking->status === 'PENDING') {
            $bookingService = app(BookingService::class);
            $bookingService->confirmBooking($booking, []);
        }

        return [
            'success' => true,
            'message' => 'Notification processed',
            'payment' => $payment,
        ];
    }

    /**
     * Process a refund.
     *
     * @param Booking $booking
     * @param float $amount
     * @param string $reason
     * @param int|null $adminId
     * @return Refund
     */
    public function processRefund(Booking $booking, float $amount, string $reason, ?int $adminId = null)
    {
        // Find the successful payment
        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', 'SUCCESS')
            ->latest()
            ->first();

        if (!$payment) {
            throw new \Exception('No successful payment found for this booking.');
        }

        // Check if amount is valid
        if ($amount <= 0 || $amount > $payment->amount) {
            throw new \Exception('Invalid refund amount.');
        }

        // Create refund record
        $refund = Refund::create([
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'PENDING',
            'refunded_by' => $adminId,
        ]);

        // Update payment status
        if ($amount == $payment->amount) {
            $payment->status = 'REFUNDED';
        } else {
            $payment->status = 'PARTIAL_REFUND';
        }

        $payment->refund_amount = $amount;
        $payment->refund_date = Carbon::now();
        $payment->save();

        // For integration with actual payment gateway refund process,
        // additional code would be required here

        // For now, we'll just approve the refund automatically
        $refund->status = 'APPROVED';
        $refund->save();

        return $refund;
    }

    /**
     * Get a payment gateway URL for a payment.
     *
     * @param Payment $payment
     * @return string|null
     */
    public function getPaymentUrl(Payment $payment)
    {
        // For Midtrans, we would return a redirect URL or Snap token
        if ($payment->payment_method === 'VIRTUAL_ACCOUNT' || $payment->payment_method === 'E_WALLET') {
            $payload = json_decode($payment->payload, true);

            if (isset($payload['snap_token'])) {
                return $payload['snap_token'];
            }
        }

        return null;
    }

    /**
     * Check the status of a payment.
     *
     * @param Payment $payment
     * @return string
     */
    public function checkPaymentStatus(Payment $payment)
    {
        // For Midtrans, we would check the status via API
        // For simplicity, we'll just return the current status
        return $payment->status;
    }
}
