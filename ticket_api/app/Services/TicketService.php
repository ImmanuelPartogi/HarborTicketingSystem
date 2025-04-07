<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Passenger; // Tambahkan ini
use App\Models\Vehicle;   // Tambahkan ini
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TicketService
{
    /**
     * Generate tickets for a booking.
     *
     * @param Booking $booking
     * @return array
     */
    public function generateTicketsForBooking(Booking $booking)
    {
        try {
            Log::info('Generating tickets for booking', ['booking_id' => $booking->id]);

            // Check if booking is confirmed
            if ($booking->status !== 'CONFIRMED') {
                Log::warning('Cannot generate tickets for non-confirmed booking', [
                    'booking_id' => $booking->id,
                    'status' => $booking->status
                ]);
                return ['success' => false, 'message' => 'Booking is not confirmed'];
            }

            // Check if tickets already exist
            if ($booking->tickets()->count() > 0) {
                Log::info('Tickets already exist for booking', ['booking_id' => $booking->id]);
                return ['success' => true, 'message' => 'Tickets already generated'];
            }

            $tickets = [];

            // Generate passenger tickets
            foreach ($booking->passengers as $passenger) {
                $ticketData = [
                    'passenger_id' => $passenger->id,
                    'vehicle_id' => null,
                ];

                $ticket = $this->createTicket($booking, $ticketData);
                $tickets[] = $ticket;
            }

            // Generate vehicle tickets
            foreach ($booking->vehicles as $vehicle) {
                if ($vehicle->owner_passenger_id) {
                    $this->createVehicleTicket($booking, [
                        'vehicle_id' => $vehicle->id,
                        'passenger_id' => $vehicle->owner_passenger_id
                    ]);
                }
            }

            Log::info('Tickets generated successfully', [
                'booking_id' => $booking->id,
                'ticket_count' => count($tickets)
            ]);

            return [
                'success' => true,
                'message' => 'Tickets generated successfully',
                'tickets' => $tickets
            ];
        } catch (\Exception $e) {
            Log::error('Error generating tickets', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error generating tickets: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a ticket for a passenger.
     *
     * @param Booking $booking
     * @param array $ticketData
     * @return Ticket
     */
    public function createTicket(Booking $booking, array $ticketData)
    {
        // Generate ticket code
        $ticketCode = $this->generateTicketCode();

        // Get passenger
        $passenger = Passenger::findOrFail($ticketData['passenger_id']);

        // Create ticket record
        $ticket = new Ticket();
        $ticket->booking_id = $booking->id;
        $ticket->passenger_id = $passenger->id;
        $ticket->vehicle_id = $ticketData['vehicle_id'] ?? null;
        $ticket->ticket_code = $ticketCode;
        $ticket->status = 'ACTIVE';
        $ticket->boarding_status = Ticket::BOARDING_NOT_BOARDED;
        $ticket->checked_in = false;

        // Generate watermark data
        $watermarkData = [
            'passenger_name' => $passenger->name,
            'departure_date' => $booking->booking_date,
            'route' => $booking->schedule->route->name,
            'ferry' => $booking->schedule->ferry->name,
            'departure_time' => $booking->schedule->departure_time,
            'arrival_time' => $booking->schedule->arrival_time,
            'timestamp' => Carbon::now()->timestamp
        ];
        $ticket->watermark_data = json_encode($watermarkData);

        // Generate QR code
        $qrData = [
            'ticket_code' => $ticketCode,
            'passenger_id' => $passenger->id,
            'passenger_name' => $passenger->name,
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'schedule_id' => $booking->schedule_id,
            'generated_at' => Carbon::now()->toIso8601String()
        ];

        $qrContent = json_encode($qrData);
        $qrFileName = 'tickets/' . $ticketCode . '.png';

        // Generate QR code and save to storage
        $qrImage = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($qrContent);

        Storage::disk('public')->put($qrFileName, $qrImage);
        $ticket->qr_code = $qrFileName;

        $ticket->save();

        return $ticket;
    }

    /**
     * Create a ticket for a vehicle.
     *
     * @param Booking $booking
     * @param array $ticketData
     * @return Ticket
     */
    public function createVehicleTicket(Booking $booking, array $ticketData)
    {
        // Generate ticket code
        $ticketCode = $this->generateTicketCode('VEH');

        // Get vehicle and passenger
        $vehicle = Vehicle::findOrFail($ticketData['vehicle_id']);
        $passenger = Passenger::findOrFail($ticketData['passenger_id']);

        // Create ticket record
        $ticket = new Ticket();
        $ticket->booking_id = $booking->id;
        $ticket->passenger_id = $passenger->id;  // Ini bagian krusial
        $ticket->vehicle_id = $vehicle->id;
        $ticket->ticket_code = $ticketCode;
        $ticket->status = 'ACTIVE';
        $ticket->boarding_status = Ticket::BOARDING_NOT_BOARDED;
        $ticket->checked_in = false;

        // Generate watermark data
        $watermarkData = [
            'vehicle_type' => $vehicle->type,
            'license_plate' => $vehicle->license_plate,
            'departure_date' => $booking->booking_date,
            'route' => $booking->schedule->route->name,
            'ferry' => $booking->schedule->ferry->name,
            'departure_time' => $booking->schedule->departure_time,
            'arrival_time' => $booking->schedule->arrival_time,
            'timestamp' => Carbon::now()->timestamp
        ];
        $ticket->watermark_data = json_encode($watermarkData);

        // Generate QR code
        $qrData = [
            'ticket_code' => $ticketCode,
            'vehicle_id' => $vehicle->id,
            'vehicle_type' => $vehicle->type,
            'license_plate' => $vehicle->license_plate,
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'schedule_id' => $booking->schedule_id,
            'generated_at' => Carbon::now()->toIso8601String()
        ];

        $qrContent = json_encode($qrData);
        $qrFileName = 'tickets/' . $ticketCode . '.png';

        // Generate QR code and save to storage
        $qrImage = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($qrContent);

        Storage::disk('public')->put($qrFileName, $qrImage);
        $ticket->qr_code = $qrFileName;

        $ticket->save();

        $watermarkData['passenger_name'] = $passenger->name;
        $qrData['passenger_name'] = $passenger->name;
        $qrData['passenger_id'] = $passenger->id;

        return $ticket;
    }

    /**
     * Update ticket information for rescheduled booking.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function updateTicketForReschedule(Ticket $ticket)
    {
        $booking = $ticket->booking;

        // Update watermark data
        $watermarkData = json_decode($ticket->watermark_data, true);

        if ($ticket->passenger_id) {
            // Update passenger ticket info
            $watermarkData['departure_date'] = $booking->booking_date;
            $watermarkData['route'] = $booking->schedule->route->name;
            $watermarkData['ferry'] = $booking->schedule->ferry->name;
            $watermarkData['departure_time'] = $booking->schedule->departure_time;
            $watermarkData['arrival_time'] = $booking->schedule->arrival_time;
        } else if ($ticket->vehicle_id) {
            // Update vehicle ticket info
            $watermarkData['departure_date'] = $booking->booking_date;
            $watermarkData['route'] = $booking->schedule->route->name;
            $watermarkData['ferry'] = $booking->schedule->ferry->name;
            $watermarkData['departure_time'] = $booking->schedule->departure_time;
            $watermarkData['arrival_time'] = $booking->schedule->arrival_time;
        }

        $ticket->watermark_data = json_encode($watermarkData);
        $ticket->save();

        return $ticket;
    }

    /**
     * Generate a unique ticket code.
     *
     * @param string $prefix
     * @return string
     */
    protected function generateTicketCode($prefix = 'TIX')
    {
        // Gunakan metode dari model Ticket yang sudah ada
        $code = Ticket::generateTicketCode();

        // Tambahkan prefix jika berbeda dari default
        if ($prefix !== 'TIX') {
            return $prefix . '-' . $code;
        }

        return $code;
    }

    /**
     * Generate a PDF for a ticket.
     *
     * @param Ticket $ticket
     * @return string Path to the generated PDF
     */
    public function generateTicketPdf(Ticket $ticket)
    {
        try {
            // Load booking and related information
            $booking = $ticket->booking;
            $schedule = $booking->schedule;
            $route = $schedule->route;
            $ferry = $schedule->ferry;
            $passenger = $ticket->passenger;
            $vehicle = $ticket->vehicle;

            // Create PDF using a PDF library like DOMPDF or TCPDF
            // Here's an example using DOMPDF (You'll need to include it in your project)
            $pdf = new \Dompdf\Dompdf();

            // Get PDF content
            $content = view('tickets.pdf', [
                'ticket' => $ticket,
                'booking' => $booking,
                'schedule' => $schedule,
                'route' => $route,
                'ferry' => $ferry,
                'passenger' => $passenger,
                'vehicle' => $vehicle,
                'qr_code' => asset('storage/' . $ticket->qr_code)
            ])->render();

            $pdf->loadHtml($content);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            // Save PDF to storage
            $pdfFileName = 'tickets/pdf/' . $ticket->ticket_code . '.pdf';
            Storage::disk('public')->put($pdfFileName, $pdf->output());

            return $pdfFileName;
        } catch (\Exception $e) {
            Log::error('Error generating ticket PDF', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validate a ticket.
     *
     * @param string $ticketCode
     * @return array
     */
    public function validateTicket($ticketCode)
    {
        try {
            $ticket = Ticket::where('ticket_code', $ticketCode)
                ->with(['booking', 'passenger', 'vehicle', 'schedule.route', 'schedule.ferry'])
                ->first();

            if (!$ticket) {
                return [
                    'valid' => false,
                    'message' => 'Ticket not found'
                ];
            }

            // Check ticket status
            if ($ticket->status !== 'ACTIVE') {
                return [
                    'valid' => false,
                    'message' => 'Ticket is not active',
                    'status' => $ticket->status
                ];
            }

            // Check if already boarded
            if ($ticket->boarding_status === 'BOARDED') {
                return [
                    'valid' => false,
                    'message' => 'Ticket has already been used for boarding',
                    'boarding_status' => $ticket->boarding_status
                ];
            }

            // Check if schedule date matches today
            $scheduleDate = Carbon::parse($ticket->booking->booking_date);
            $today = Carbon::today();

            if (!$scheduleDate->isSameDay($today)) {
                return [
                    'valid' => false,
                    'message' => 'Ticket is not valid for today',
                    'ticket_date' => $scheduleDate->toDateString(),
                    'today' => $today->toDateString()
                ];
            }

            // Get passenger or vehicle info
            $entityInfo = [];
            if ($ticket->passenger) {
                $entityInfo = [
                    'type' => 'passenger',
                    'name' => $ticket->passenger->name,
                    'identity_number' => $ticket->passenger->identity_number,
                    'identity_type' => $ticket->passenger->identity_type
                ];
            } elseif ($ticket->vehicle) {
                $entityInfo = [
                    'type' => 'vehicle',
                    'license_plate' => $ticket->vehicle->license_plate,
                    'vehicle_type' => $ticket->vehicle->type
                ];
            }

            return [
                'valid' => true,
                'message' => 'Ticket is valid',
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'boarding_status' => $ticket->boarding_status,
                    'checked_in' => $ticket->checked_in
                ],
                'booking' => [
                    'id' => $ticket->booking->id,
                    'booking_code' => $ticket->booking->booking_code,
                    'booking_date' => $ticket->booking->booking_date
                ],
                'schedule' => [
                    'route' => $ticket->schedule->route->name,
                    'ferry' => $ticket->schedule->ferry->name,
                    'departure_time' => $ticket->schedule->departure_time,
                    'arrival_time' => $ticket->schedule->arrival_time
                ],
                'entity' => $entityInfo
            ];
        } catch (\Exception $e) {
            Log::error('Error validating ticket', [
                'ticket_code' => $ticketCode,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Error validating ticket'
            ];
        }
    }

    /**
     * Mark a ticket as boarded.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function markAsBoarded(Ticket $ticket)
    {
        $ticket->boarding_status = 'BOARDED';
        $ticket->boarded_at = Carbon::now();
        $ticket->save();

        return $ticket;
    }

    /**
     * Check in a ticket.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function checkIn(Ticket $ticket)
    {
        $ticket->checked_in = true;
        $ticket->checked_in_at = Carbon::now();
        $ticket->save();

        return $ticket;
    }
}
