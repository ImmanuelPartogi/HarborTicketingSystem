<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\TicketService;
use Illuminate\Support\Facades\Log;

class TicketGenerationController extends Controller
{
    protected $ticketService;

    /**
     * Create a new controller instance.
     *
     * @param TicketService $ticketService
     */
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Auto generate tickets for successful payment
     *
     * @param int $paymentId
     * @return array
     */
    public function autoGenerateTickets($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            $booking = $payment->booking;

            // Pastikan booking sudah CONFIRMED dan belum memiliki tiket
            if ($booking->status !== 'CONFIRMED') {
                // Update status booking menjadi CONFIRMED jika payment SUCCESS
                if ($payment->status === 'SUCCESS') {
                    $booking->update(['status' => 'CONFIRMED']);
                } else {
                    return [
                        'success' => false,
                        'message' => 'Booking belum confirmed, tidak bisa generate tiket'
                    ];
                }
            }

            // Cek apakah sudah ada tiket
            if ($booking->tickets()->count() > 0) {
                return [
                    'success' => true,
                    'message' => 'Tiket sudah dibuat sebelumnya',
                    'tickets' => $booking->tickets
                ];
            }

            // Generate tiket menggunakan TicketService
            $result = $this->ticketService->generateTicketsForBooking($booking);

            Log::info('Tickets auto-generated successfully', [
                'payment_id' => $paymentId,
                'booking_id' => $booking->id,
                'ticket_count' => count($result['tickets'] ?? [])
            ]);

            return [
                'success' => true,
                'message' => 'Tiket berhasil dibuat otomatis',
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Gagal membuat tiket otomatis', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat tiket: ' . $e->getMessage()
            ];
        }
    }
}
