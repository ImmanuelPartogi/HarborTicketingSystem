<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
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
     * Get user's tickets.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Ticket::whereHas('booking', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
            ->with([
                'booking.schedule.route',
                'booking.schedule.ferry',
                'passenger',
                'vehicle'
            ]);

        if ($status) {
            $query->where('status', $status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => $tickets
            ]
        ]);
    }

    /**
     * Get ticket by ticket code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ticketCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $ticketCode)
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->with(['booking', 'vehicle', 'schedule.route', 'schedule.ferry'])
            ->firstOrFail();

        // Tambahkan informasi yang diperlukan tanpa mengandalkan data penumpang
        $ticketInfo = [
            'ticket_code' => $ticket->ticket_code,
            'status' => $ticket->status,
            'boarding_status' => $ticket->boarding_status,
            'ticket_type' => $ticket->vehicle_id ? 'Vehicle' : 'Passenger',
            'ferry' => $ticket->schedule->ferry->name,
            'route' => $ticket->schedule->route->name,
            'departure_date' => $ticket->booking->booking_date,
            'departure_time' => $ticket->schedule->departure_time,
            'arrival_time' => $ticket->schedule->arrival_time,
        ];

        // Tambahkan info kendaraan jika tiket untuk kendaraan
        if ($ticket->vehicle) {
            $ticketInfo['vehicle'] = [
                'type' => $ticket->vehicle->type,
                'license_plate' => $ticket->vehicle->license_plate,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $ticketInfo
        ]);
    }

    /**
     * Download a ticket as PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ticketCode
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request, $ticketCode)
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        try {
            $pdfPath = $this->ticketService->generateTicketPdf($ticket);
            return response()->download(storage_path('app/public/' . $pdfPath));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get QR code for a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ticketCode
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function qrCode(Request $request, $ticketCode)
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        if (!$ticket->qr_code) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code_url' => url('storage/' . $ticket->qr_code)
            ]
        ]);
    }

    /**
     * Check in for a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ticketCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIn(Request $request, $ticketCode)
    {
        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        if ($ticket->status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is not active'
            ], 400);
        }

        if ($ticket->checked_in) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is already checked in'
            ], 400);
        }

        try {
            $ticket = $this->ticketService->checkIn($ticket);

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'data' => [
                    'ticket' => $ticket
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate a ticket (for staff use).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticketService = app(TicketService::class);
        $result = $ticketService->validateTicket($request->ticket_code);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function autoGenerateTickets(Request $request, $bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            // Periksa status booking
            if ($booking->status !== 'CONFIRMED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tickets can only be generated for confirmed bookings'
                ], 400);
            }

            // Gunakan ticket service
            $ticketService = app(TicketService::class);
            $result = $ticketService->generateTicketsForBooking($booking);

            return response()->json([
                'success' => true,
                'message' => 'Tickets generated successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-generate tickets', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a ticket as boarded (for staff use).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsBoarded(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_code' => 'required|exists:tickets,ticket_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticketCode = $request->ticket_code;

        try {
            $ticketService = app(TicketService::class);
            $validationResult = $ticketService->validateTicket($ticketCode);

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message'],
                    'data' => $validationResult
                ], 400);
            }

            $ticket = Ticket::where('ticket_code', $ticketCode)->firstOrFail();
            $ticket = $ticketService->markAsBoarded($ticket);

            return response()->json([
                'success' => true,
                'message' => 'Ticket marked as boarded successfully',
                'data' => [
                    'ticket' => $ticket,
                    'entity_type' => $ticket->vehicle_id ? 'vehicle' : 'passenger',
                    'entity_info' => $ticket->vehicle_id
                        ? ['type' => $ticket->vehicle->type, 'license_plate' => $ticket->vehicle->license_plate]
                        : ['ticket_code' => $ticket->ticket_code]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark ticket as boarded: ' . $e->getMessage()
            ], 500);
        }
    }
}
