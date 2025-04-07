<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentService
{
    protected $midtransUrl;
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;

    public function __construct()
    {
        // Initialize Midtrans configuration
        $this->isProduction = config('payment.midtrans.is_production', false);
        $this->midtransUrl = $this->isProduction
            ? config('payment.midtrans.production_url', 'https://api.midtrans.com')
            : config('payment.midtrans.sandbox_url', 'https://api.sandbox.midtrans.com');
        $this->serverKey = config('payment.midtrans.server_key', 'SB-Mid-server-jv_rZEY1OoQzsxdhc0GVb-uW');
        $this->clientKey = config('payment.midtrans.client_key', 'SB-Mid-client-8csuXJ7DmFhqmkMX');
    }

    /**
     * Create a payment for a booking.
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return Payment
     */
    public function createPayment(Booking $booking, array $paymentData)
    {
        Log::info('Creating payment for booking', [
            'booking_id' => $booking->id,
            'payment_data' => $paymentData
        ]);

        // Validate payment method and channel
        $paymentMethod = strtoupper($paymentData['payment_method']);
        $paymentChannel = strtoupper($paymentData['payment_channel']);

        // Create payment record
        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->amount = $booking->total_amount;
        $payment->payment_method = $paymentMethod;
        $payment->payment_channel = $paymentChannel;
        $payment->status = 'PENDING';

        // Simpan payment_code di payload
        $payloadData = [
            'payment_code' => $this->generatePaymentCode(),
            'payment_url' => null,
            'payment_data' => null
        ];
        $payment->payload = json_encode($payloadData);
        $payment->expiry_date = Carbon::now()->addHours(24); // 24 hours expiry
        $payment->save();

        // Process the payment with Midtrans based on the payment method
        try {
            $midtransData = $this->processPaymentWithMidtrans($payment, $booking);

            // Update payment with Midtrans response data
            $payment->transaction_id = $midtransData['transaction_id'] ?? null;

            // Update payload untuk menyimpan payment_url dan payment_data
            $payloadData = json_decode($payment->payload, true) ?: [];
            $payloadData['payment_url'] = $midtransData['payment_url'] ?? null;
            $payloadData['payment_data'] = $midtransData;
            $payment->payload = json_encode($payloadData);
            $payment->save();

            // Update booking status if needed
            if ($booking->status === 'DRAFT') {
                $booking->status = 'PENDING';
                $booking->save();
            }

            return $payment;
        } catch (\Exception $e) {
            Log::error('Midtrans payment processing error', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            // Mark payment as failed and propagate the error
            $payment->status = 'FAILED';
            $payment->notes = 'Payment gateway error: ' . $e->getMessage();
            $payment->save();

            throw $e;
        }
    }

    /**
     * Process the payment with Midtrans.
     *
     * @param Payment $payment
     * @param Booking $booking
     * @return array
     */
    protected function processPaymentWithMidtrans(Payment $payment, Booking $booking)
    {
        // Prepare customer details
        $user = $booking->user;
        $passenger = $booking->passengers->first();

        $customerDetails = [
            'first_name' => $user->name ?? $passenger->name ?? 'Customer',
            'email' => $user->email ?? $passenger->email ?? 'customer@example.com',
            'phone' => $user->phone ?? $passenger->phone ?? '081234567890',
        ];

        // Prepare transaction details
        $payloadData = json_decode($payment->payload, true) ?: [];
        $paymentCode = $payloadData['payment_code'] ?? ('PAY-' . strtoupper(Str::random(8)));
        $orderId = 'ORDER-' . $booking->booking_code . '-' . time();
        $amount = $payment->amount;

        // Item details
        $itemDetails = [
            [
                'id' => 'FERRY-' . $booking->schedule->id,
                'price' => $amount,
                'quantity' => 1,
                'name' => 'Ferry Ticket: ' . $booking->schedule->route->name . ' (' . $booking->booking_date . ')',
            ]
        ];

        // Base transaction data
        $transactionData = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'expiry' => [
                'unit' => 'hour',
                'duration' => 24,
            ],
        ];

        // Add specific payment method details
        switch ($payment->payment_method) {
            case 'VIRTUAL_ACCOUNT':
                $paymentType = 'bank_transfer';
                $bankTransfer = ['bank' => strtolower($payment->payment_channel)];

                // Add free text for certain banks if needed
                if (in_array(strtolower($payment->payment_channel), ['permata', 'bca'])) {
                    $bookingCode = $booking->booking_code;
                    $bankTransfer['free_text'] = [
                        'inquiry' => [
                            'en' => "Ferry Booking {$bookingCode}"
                        ],
                        'payment' => [
                            'en' => "Ferry Payment {$bookingCode}"
                        ]
                    ];
                }

                $transactionData['payment_type'] = $paymentType;
                $transactionData['bank_transfer'] = $bankTransfer;
                break;

            case 'E_WALLET':
                $paymentType = 'gopay';
                if (strtolower($payment->payment_channel) === 'dana') {
                    $paymentType = 'dana';
                } elseif (strtolower($payment->payment_channel) === 'ovo') {
                    $paymentType = 'ovo';
                } elseif (strtolower($payment->payment_channel) === 'shopeepay') {
                    $paymentType = 'shopeepay';
                }

                $transactionData['payment_type'] = $paymentType;

                // Add specific e-wallet configs
                if ($paymentType == 'gopay') {
                    $transactionData['gopay'] = ['enable_callback' => true];
                } elseif ($paymentType == 'shopeepay') {
                    $transactionData['shopeepay'] = ['callback_url' => url('/payment/callback')];
                }
                break;

            case 'CREDIT_CARD':
                $transactionData['payment_type'] = 'credit_card';
                $transactionData['credit_card'] = [
                    'secure' => true,
                    'bank' => 'bca',
                    'installment_term' => 0,
                    'bins' => ['481111', '410505'],
                ];
                break;

            case 'BANK_TRANSFER':
                // For manual bank transfers
                return [
                    'transaction_id' => $orderId,
                    'payment_type' => 'bank_transfer',
                    'bank' => strtolower($payment->payment_channel),
                    'account_number' => config("payment.bank_transfer.accounts.{$payment->payment_channel}.account_number", '1234567890'),
                    'account_name' => config("payment.bank_transfer.accounts.{$payment->payment_channel}.account_name", 'Ferry Company'),
                ];

            default:
                throw new \Exception("Unsupported payment method: {$payment->payment_method}");
        }

        // Call Midtrans API
        $response = $this->callMidtrans('/v2/charge', 'POST', $transactionData);
        Log::info('Midtrans charge response', ['response' => $response]);

        // Format the response
        $result = [
            'transaction_id' => $response['transaction_id'] ?? '',
            'payment_type' => $response['payment_type'] ?? '',
            'status_code' => $response['status_code'] ?? '',
            'transaction_status' => $response['transaction_status'] ?? '',
            'fraud_status' => $response['fraud_status'] ?? '',
            'transaction_time' => $response['transaction_time'] ?? '',
            'payment_url' => $response['redirect_url'] ?? null,
        ];

        // Add VA number for bank transfers
        if ($payment->payment_method === 'VIRTUAL_ACCOUNT') {
            $vaNumbers = $response['va_numbers'] ?? [];
            if (!empty($vaNumbers)) {
                $result['va_number'] = $vaNumbers[0]['va_number'] ?? '';
                $result['bank'] = $vaNumbers[0]['bank'] ?? '';
            } else if (isset($response['permata_va_number'])) {
                $result['va_number'] = $response['permata_va_number'];
                $result['bank'] = 'permata';
            }
        }

        // Add actions for e-wallets
        if (isset($response['actions'])) {
            $result['actions'] = $response['actions'];
        }

        return $result;
    }

    /**
     * Call Midtrans API.
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array
     */
    protected function callMidtrans($endpoint, $method = 'GET', $data = [])
    {
        $url = $this->midtransUrl . $endpoint;
        $auth = base64_encode($this->serverKey . ':');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

            if ($method === 'GET') {
                $response = $response->get($url);
            } else {
                $response = $response->post($url, $data);
            }

            if ($response->successful()) {
                return $response->json();
            }

            // If the request was not successful, log the error
            Log::error('Midtrans API error', [
                'url' => $url,
                'method' => $method,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            throw new \Exception('Midtrans API error: ' . ($response->json()['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('Midtrans API exception', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            throw $e;
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
        Log::info('Payment notification received', $notificationData);

        // Verify the notification with Midtrans
        $transactionStatus = $notificationData['transaction_status'] ?? null;
        $transactionId = $notificationData['transaction_id'] ?? null;
        $orderId = $notificationData['order_id'] ?? null;
        $statusCode = $notificationData['status_code'] ?? null;

        if (!$transactionId || !$orderId) {
            return [
                'success' => false,
                'message' => 'Invalid notification data'
            ];
        }

        // Extract booking code from order ID
        $matches = [];
        preg_match('/ORDER-(.*)-\d+/', $orderId, $matches);
        $bookingCode = $matches[1] ?? null;

        if (!$bookingCode) {
            return [
                'success' => false,
                'message' => 'Invalid order ID format'
            ];
        }

        // Find the booking and payment
        $booking = Booking::where('booking_code', $bookingCode)->first();
        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Booking not found'
            ];
        }

        $payment = Payment::where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found'
            ];
        }

        // Update payment and booking status based on transaction status
        $payment->transaction_id = $transactionId;

        // Update payload dengan notification data
        $payloadData = json_decode($payment->payload, true) ?: [];
        $payloadData['payment_data'] = $notificationData;
        $payment->payload = json_encode($payloadData);

        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $payment->status = 'SUCCESS';
                $payment->payment_date = Carbon::now();
                $booking->status = 'CONFIRMED';

                // Generate tickets with retry mechanism
                $this->generateTicketsWithRetry($booking);
                break;

            case 'pending':
                $payment->status = 'PENDING';
                break;

            case 'deny':
            case 'cancel':
            case 'expire':
                $payment->status = 'FAILED';
                break;

            case 'refund':
                $payment->status = 'REFUNDED';
                break;

            default:
                $payment->status = 'UNKNOWN';
                break;
        }

        $payment->save();
        $booking->save();

        // Double check: pastikan tiket dibuat jika payment SUCCESS
        if ($payment->status === 'SUCCESS' && $booking->tickets()->count() === 0) {
            Log::warning('Payment is SUCCESS but no tickets generated, trying again', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id
            ]);
            $this->generateTicketsWithRetry($booking);
        }

        return [
            'success' => true,
            'message' => 'Notification processed successfully',
            'status' => $payment->status,
            'booking_status' => $booking->status,
            'booking_code' => $booking->booking_code, // Tambahkan ini untuk identifikasi
            'booking_id' => $booking->id // Tambahkan ini untuk identifikasi
        ];
    }

    /**
     * Get payment URL for redirection.
     *
     * @param Payment $payment
     * @return string|null
     */
    public function getPaymentUrl(Payment $payment)
    {
        // Ambil data dari payload
        $payloadData = json_decode($payment->payload, true) ?: [];

        // Check if payment has a payment URL in payload
        if (!empty($payloadData['payment_url'])) {
            return $payloadData['payment_url'];
        }

        // Check if payment data contains actions for redirection
        if (!empty($payloadData['payment_data'])) {
            $paymentData = $payloadData['payment_data'];

            // For e-wallets, find the redirect URL
            if (isset($paymentData['actions'])) {
                foreach ($paymentData['actions'] as $action) {
                    if ($action['name'] == 'deeplink-redirect' || $action['name'] == 'generate-qr-code') {
                        return $action['url'];
                    }
                }
            }
        }

        // If no direct URL, return the payment status page URL
        return url("/payment/status/{$payment->id}");
    }

    /**
     * Check payment status with Midtrans.
     *
     * @param Payment $payment
     * @return string
     */
    public function checkPaymentStatus(Payment $payment)
    {
        // Jika payment sudah success atau refunded, return current status
        if ($payment->status === 'SUCCESS') {
            $booking = $payment->booking;
            // Pastikan tiket dibuat jika payment success tapi tidak ada tiket
            if ($booking && $booking->tickets()->count() === 0) {
                Log::info('Payment successful but no tickets found, generating tickets now', [
                    'booking_id' => $booking->id,
                    'payment_id' => $payment->id
                ]);
                $this->generateTickets($booking);
            }
            return $payment->status;
        }

        // Jika payment memiliki transaction_id, cek dengan Midtrans
        if ($payment->transaction_id) {
            try {
                $response = $this->callMidtrans('/v2/' . $payment->transaction_id . '/status', 'GET');

                Log::info('Midtrans status check response', ['response' => $response]);

                // Update payment berdasarkan respons
                $newStatus = null;

                switch ($response['transaction_status'] ?? null) {
                    case 'capture':
                    case 'settlement':
                        $newStatus = 'SUCCESS';
                        $payment->status = $newStatus;
                        $payment->payment_date = now();

                        // Update booking
                        $booking = $payment->booking;
                        if ($booking && $booking->status === 'PENDING') {
                            $booking->status = 'CONFIRMED';
                            $booking->save();

                            // Generate tickets dengan retry mechanism
                            $this->generateTicketsWithRetry($booking);
                        }
                        break;

                    case 'pending':
                        $newStatus = 'PENDING';
                        $payment->status = $newStatus;
                        break;

                    case 'deny':
                    case 'cancel':
                    case 'expire':
                        $newStatus = 'FAILED';
                        $payment->status = $newStatus;
                        break;

                    case 'refund':
                        $newStatus = 'REFUNDED';
                        $payment->status = $newStatus;
                        break;
                }

                // Update payload
                if ($newStatus) {
                    $payloadData = json_decode($payment->payload, true) ?: [];
                    $payloadData['payment_data'] = $response;
                    $payment->payload = json_encode($payloadData);
                    $payment->save();

                    Log::info("Payment status updated from Midtrans", [
                        'payment_id' => $payment->id,
                        'old_status' => $payment->getOriginal('status'),
                        'new_status' => $newStatus
                    ]);
                }

                return $payment->status;
            } catch (\Exception $e) {
                Log::error('Midtrans status check error', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'error' => $e->getMessage()
                ]);

                return $payment->status;
            }
        }

        // Cek jika payment expired
        if ($payment->expiry_date && now()->gt($payment->expiry_date) && $payment->status === 'PENDING') {
            $payment->status = 'EXPIRED';
            $payment->save();
        }

        return $payment->status;
    }

    /**
     * Generate tickets with retry mechanism
     *
     * @param Booking $booking
     * @param int $maxRetries
     * @return bool
     */
    protected function generateTicketsWithRetry(Booking $booking, $maxRetries = 3)
    {
        $retry = 0;
        $success = false;

        while ($retry < $maxRetries && !$success) {
            try {
                // Check if tickets already exist
                if ($booking->tickets()->count() > 0) {
                    Log::info('Tickets already exist for booking', ['booking_id' => $booking->id]);
                    return true;
                }

                Log::info('Attempting to generate tickets for booking (attempt ' . ($retry + 1) . ')', ['booking_id' => $booking->id]);

                // Generate tickets
                $ticketService = app(TicketService::class);
                $result = $ticketService->generateTicketsForBooking($booking);

                if ($result['success']) {
                    Log::info('Tickets generated successfully', [
                        'booking_id' => $booking->id,
                        'tickets_count' => count($result['tickets'] ?? [])
                    ]);
                    $success = true;
                } else {
                    throw new \Exception($result['message'] ?? 'Unknown error generating tickets');
                }
            } catch (\Exception $e) {
                $retry++;
                Log::error('Failed to generate tickets (attempt ' . $retry . ')', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);

                // Wait before retrying (exponential backoff)
                if ($retry < $maxRetries) {
                    sleep(2 * $retry);
                }
            }
        }

        return $success;
    }

    /**
     * Generate a unique payment code.
     *
     * @return string
     */
    protected function generatePaymentCode()
    {
        return 'PAY-' . strtoupper(Str::random(8));
    }

    /**
     * Process refund for a payment.
     *
     * @param Booking $booking
     * @param float $amount
     * @param string $reason
     * @return array
     */
    public function processRefund(Booking $booking, float $amount, string $reason)
    {
        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', 'SUCCESS')
            ->latest()
            ->first();

        if (!$payment) {
            throw new \Exception('No successful payment found for this booking');
        }

        try {
            // Prepare refund data
            $refundData = [
                'refund_key' => 'REFUND-' . $payment->transaction_id . '-' . time(),
                'amount' => $amount,
                'reason' => $reason
            ];

            // Call Midtrans refund API
            $response = $this->callMidtrans("/v2/{$payment->transaction_id}/refund", 'POST', $refundData);
            Log::info('Midtrans refund response', ['response' => $response]);

            // Update payment status
            $payment->status = 'REFUNDED';
            $payment->refund_amount = $amount;
            $payment->refund_date = Carbon::now();
            $payment->notes = "Refunded: {$amount} - {$reason}";
            $payment->save();

            // Update booking status
            $booking->status = 'REFUNDED';
            $booking->save();

            return [
                'success' => true,
                'message' => 'Refund processed successfully',
                'refund_data' => $response
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans refund error', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Generate tickets for a confirmed booking.
     *
     * @param Booking $booking
     * @return void
     */
    protected function generateTickets(Booking $booking)
    {
        try {
            Log::info('Attempting to generate tickets for booking', ['booking_id' => $booking->id]);

            // Check if tickets already exist for this booking
            if ($booking->tickets()->count() > 0) {
                Log::info('Tickets already exist for booking', ['booking_id' => $booking->id]);
                return;
            }

            // Call the TicketService to generate tickets
            $ticketService = app(TicketService::class);
            $result = $ticketService->generateTicketsForBooking($booking);

            Log::info('Tickets generated for booking', [
                'booking_id' => $booking->id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate tickets', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
