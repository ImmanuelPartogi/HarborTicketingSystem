<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Passenger;
use App\Helpers\TicketHelper;
use App\Helpers\WatermarkGenerator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TicketService
{
    protected $ticketHelper;
    protected $watermarkGenerator;

    /**
     * Create a new service instance.
     *
     * @param TicketHelper $ticketHelper
     * @param WatermarkGenerator $watermarkGenerator
     */
    public function __construct(TicketHelper $ticketHelper, WatermarkGenerator $watermarkGenerator)
    {
        $this->ticketHelper = $ticketHelper;
        $this->watermarkGenerator = $watermarkGenerator;
    }

    /**
     * Create a new ticket.
     *
     * @param Booking $booking
     * @param array $data
     * @return Ticket
     */
    public function createTicket(Booking $booking, array $data)
    {
        // Generate ticket code
        $ticketCode = Ticket::generateTicketCode();

        // Generate QR code
        $qrCodeContent = json_encode([
            'ticket_code' => $ticketCode,
            'booking_code' => $booking->booking_code,
            'passenger_id' => $data['passenger_id'],
            'schedule_id' => $booking->schedule_id,
            'booking_date' => $booking->booking_date->format('Y-m-d'),
            'timestamp' => now()->timestamp,
        ]);

        $qrCodePath = 'tickets/qr/' . $ticketCode . '.png';
        $qrCode = QrCode::format('png')
                    ->size(300)
                    ->errorCorrection('H')
                    ->generate($qrCodeContent);

        Storage::disk('public')->put($qrCodePath, $qrCode);

        // Generate watermark data
        $passenger = Passenger::find($data['passenger_id']);
        $watermarkData = $this->watermarkGenerator->generateWatermarkData(
            $booking,
            $passenger,
            $ticketCode
        );

        // Create ticket record
        $ticket = Ticket::create([
            'ticket_code' => $ticketCode,
            'booking_id' => $booking->id,
            'passenger_id' => $data['passenger_id'],
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'qr_code' => $qrCodePath,
            'seat_number' => $this->ticketHelper->generateSeatNumber(),
            'boarding_status' => 'NOT_BOARDED',
            'status' => 'ACTIVE',
            'checked_in' => false,
            'watermark_data' => json_encode($watermarkData),
        ]);

        return $ticket;
    }

    /**
     * Validate a ticket for boarding.
     *
     * @param string $ticketCode
     * @return array
     */
    public function validateTicket(string $ticketCode)
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)->first();

        if (!$ticket) {
            return [
                'valid' => false,
                'message' => 'Tiket tidak ditemukan.',
            ];
        }

        if ($ticket->status !== 'ACTIVE') {
            return [
                'valid' => false,
                'message' => 'Tiket tidak aktif. Status: ' . $ticket->status,
            ];
        }

        if ($ticket->boarding_status === 'BOARDED') {
            return [
                'valid' => false,
                'message' => 'Tiket sudah digunakan untuk boarding.',
            ];
        }

        $booking = $ticket->booking;
        $scheduleDate = $booking->booking_date;
        $currentDate = Carbon::now()->startOfDay();

        if ($scheduleDate->lt($currentDate)) {
            $ticket->update([
                'status' => 'EXPIRED',
            ]);

            return [
                'valid' => false,
                'message' => 'Tiket telah kadaluarsa.',
            ];
        }

        if ($scheduleDate->gt($currentDate)) {
            return [
                'valid' => false,
                'message' => 'Tiket hanya dapat digunakan pada tanggal keberangkatan.',
            ];
        }

        // If all checks pass, the ticket is valid
        return [
            'valid' => true,
            'message' => 'Tiket valid.',
            'ticket' => $ticket,
            'passenger' => $ticket->passenger,
            'vehicle' => $ticket->vehicle,
            'booking' => $booking,
            'schedule' => $booking->schedule,
        ];
    }

    /**
     * Mark a ticket as boarded.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function markAsBoarded(Ticket $ticket)
    {
        $ticket->update([
            'boarding_status' => 'BOARDED',
            'boarding_time' => now(),
            'checked_in' => true,
        ]);

        return $ticket;
    }

    /**
     * Mark a ticket as checked in.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function checkIn(Ticket $ticket)
    {
        $ticket->update([
            'checked_in' => true,
        ]);

        return $ticket;
    }

    /**
     * Update ticket information for reschedule.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function updateTicketForReschedule(Ticket $ticket)
    {
        $booking = $ticket->booking;
        $passenger = $ticket->passenger;

        // Generate new watermark data
        $watermarkData = $this->watermarkGenerator->generateWatermarkData(
            $booking,
            $passenger,
            $ticket->ticket_code
        );

        // Update ticket
        $ticket->update([
            'boarding_status' => 'NOT_BOARDED',
            'boarding_time' => null,
            'status' => 'ACTIVE',
            'checked_in' => false,
            'watermark_data' => json_encode($watermarkData),
        ]);

        return $ticket;
    }

    /**
     * Cancel a ticket.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function cancelTicket(Ticket $ticket)
    {
        $ticket->update([
            'status' => 'CANCELLED',
        ]);

        return $ticket;
    }

    /**
     * Mark a ticket as expired.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function expireTicket(Ticket $ticket)
    {
        $ticket->update([
            'status' => 'EXPIRED',
        ]);

        return $ticket;
    }

    /**
     * Generate ticket PDF.
     *
     * @param Ticket $ticket
     * @return string Path to the generated PDF
     */
    public function generateTicketPdf(Ticket $ticket)
    {
        // This would use a PDF generation library like DOMPDF
        // For simplicity, we'll just return a path
        return $this->ticketHelper->generateTicketPdf($ticket);
    }
}
