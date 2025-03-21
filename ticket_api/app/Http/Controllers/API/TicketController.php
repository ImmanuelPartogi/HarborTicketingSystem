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
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with([
                'booking.schedule.route',
                'booking.schedule.ferry',
                'passenger',
                'vehicle'
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'watermark_data' => json_decode($ticket->watermark_data)
            ]
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
        $request->validate([
            'ticket_code' => 'required|string'
        ]);

        try {
            $result = $this->ticketService->validateTicket($request->ticket_code);

            return response()->json([
                'success' => $result['valid'],
                'message' => $result['message'],
                'data' => $result
            ], $result['valid'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
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
        $request->validate([
            'ticket_code' => 'required|string'
        ]);

        $ticket = Ticket::where('ticket_code', $request->ticket_code)
            ->firstOrFail();

        if ($ticket->status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is not active'
            ], 400);
        }

        if ($ticket->boarding_status === 'BOARDED') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is already boarded'
            ], 400);
        }

        try {
            $ticket = $this->ticketService->markAsBoarded($ticket);

            return response()->json([
                'success' => true,
                'message' => 'Ticket marked as boarded',
                'data' => [
                    'ticket' => $ticket
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark ticket as boarded',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
