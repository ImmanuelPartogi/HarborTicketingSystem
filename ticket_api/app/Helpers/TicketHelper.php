<?php

namespace App\Helpers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;

class TicketHelper
{
    /**
     * Generate a seat number for a ticket.
     *
     * @return string
     */
    public function generateSeatNumber()
    {
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $columns = range(1, 20);

        $row = $rows[array_rand($rows)];
        $column = $columns[array_rand($columns)];

        return $row . $column;
    }

    /**
     * Generate a PDF for a ticket.
     *
     * @param Ticket $ticket
     * @return string
     */
    public function generateTicketPdf(Ticket $ticket)
    {
        $booking = $ticket->booking;
        $passenger = $ticket->passenger;
        $vehicle = $ticket->vehicle;
        $schedule = $booking->schedule;
        $route = $schedule->route;
        $ferry = $schedule->ferry;

        $departureTime = $schedule->departure_time->format('H:i');
        $departureDate = $booking->booking_date->format('d M Y');

        $watermarkData = json_decode($ticket->watermark_data, true);

        // Load the ticket template view
        $data = [
            'ticket' => $ticket,
            'booking' => $booking,
            'passenger' => $passenger,
            'vehicle' => $vehicle,
            'schedule' => $schedule,
            'route' => $route,
            'ferry' => $ferry,
            'departureTime' => $departureTime,
            'departureDate' => $departureDate,
            'watermarkData' => $watermarkData,
        ];

        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('emails.ticket', $data);

        // Save the PDF file
        $fileName = 'ticket_' . $ticket->ticket_code . '.pdf';
        $path = 'tickets/pdf/' . $fileName;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Validate a QR code content.
     *
     * @param string $qrContent
     * @return array
     */
    public function validateQrContent(string $qrContent)
    {
        try {
            $data = json_decode($qrContent, true);

            if (!isset($data['ticket_code']) || !isset($data['booking_code'])) {
                return [
                    'valid' => false,
                    'message' => 'QR code tidak valid',
                ];
            }

            $ticket = Ticket::where('ticket_code', $data['ticket_code'])->first();

            if (!$ticket) {
                return [
                    'valid' => false,
                    'message' => 'Tiket tidak ditemukan',
                ];
            }

            if ($ticket->booking->booking_code !== $data['booking_code']) {
                return [
                    'valid' => false,
                    'message' => 'Kode booking tidak sesuai',
                ];
            }

            return [
                'valid' => true,
                'ticket' => $ticket,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Format QR code tidak valid',
            ];
        }
    }

    /**
     * Check if a ticket is valid for boarding.
     *
     * @param Ticket $ticket
     * @return array
     */
    public function checkTicketValidity(Ticket $ticket)
    {
        $booking = $ticket->booking;

        if ($ticket->status !== 'ACTIVE') {
            return [
                'valid' => false,
                'message' => 'Tiket tidak aktif',
            ];
        }

        if ($ticket->boarding_status === 'BOARDED') {
            return [
                'valid' => false,
                'message' => 'Tiket sudah digunakan untuk boarding',
            ];
        }

        if ($booking->status !== 'CONFIRMED' && $booking->status !== 'RESCHEDULED') {
            return [
                'valid' => false,
                'message' => 'Status pemesanan tidak valid',
            ];
        }

        return [
            'valid' => true,
            'ticket' => $ticket,
        ];
    }
}
