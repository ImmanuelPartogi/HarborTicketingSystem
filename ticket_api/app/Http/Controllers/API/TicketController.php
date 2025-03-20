<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        try {
            $status = $request->query('status');
            $page = $request->query('page', 1);
            $perPage = min($request->query('per_page', 10), 50); // Maximum 50 per page

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

            $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'tickets' => $tickets->items(),
                    'pagination' => [
                        'current_page' => $tickets->currentPage(),
                        'total' => $tickets->total(),
                        'per_page' => $tickets->perPage(),
                        'last_page' => $tickets->lastPage()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving tickets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tickets',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
        try {
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

            // Check if ticket is still valid
            $isValid = $this->ticketService->isTicketValid($ticket);

            return response()->json([
                'success' => true,
                'data' => [
                    'ticket' => $ticket,
                    'watermark_data' => json_decode($ticket->watermark_data),
                    'is_valid' => $isValid,
                    'boarding_status' => $ticket->boarding_status,
                    'qr_code_url' => $ticket->qr_code ? url('storage/' . $ticket->qr_code) : null
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'error' => 'The requested ticket could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ticket',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
        try {
            $ticket = Ticket::where('ticket_code', $ticketCode)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->firstOrFail();

            $pdfPath = $this->ticketService->generateTicketPdf($ticket);

            // Record the download for analytics
            $this->ticketService->recordTicketDownload($ticket, $request->ip());

            // Verify the file exists
            if (!Storage::disk('public')->exists($pdfPath)) {
                throw new \Exception('PDF file not found');
            }

            return response()->download(storage_path('app/public/' . $pdfPath), 'ticket-' . $ticket->ticket_code . '.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'error' => 'The requested ticket could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating ticket PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get QR code for a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ticketCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function qrCode(Request $request, $ticketCode)
    {
        try {
            $ticket = Ticket::where('ticket_code', $ticketCode)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->firstOrFail();

            // Generate QR code if it doesn't exist
            if (!$ticket->qr_code) {
                $qrCode = $this->ticketService->generateQrCode($ticket);
                if (!$qrCode) {
                    throw new \Exception('Failed to generate QR code');
                }
                $ticket->qr_code = $qrCode;
                $ticket->save();
            }

            // Verify the QR code file exists
            if (!Storage::disk('public')->exists($ticket->qr_code)) {
                throw new \Exception('QR code file not found');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_code_url' => url('storage/' . $ticket->qr_code)
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'error' => 'The requested ticket could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving QR code: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve QR code',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
        try {
            $ticket = Ticket::where('ticket_code', $ticketCode)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->firstOrFail();

            // Validate ticket status
            if ($ticket->status !== 'ACTIVE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is not active',
                    'error' => 'This ticket cannot be checked in due to its status: ' . $ticket->status
                ], 400);
            }

            if ($ticket->checked_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is already checked in',
                    'error' => 'This ticket was already checked in at ' . $ticket->checked_in_at
                ], 400);
            }

            // Check if check-in is allowed (usually within 24 hours before departure)
            $checkInAllowed = $this->ticketService->isCheckInAllowed($ticket);
            if (!$checkInAllowed['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in is not allowed',
                    'error' => $checkInAllowed['reason']
                ], 400);
            }

            DB::beginTransaction();
            try {
                $ticket = $this->ticketService->checkIn($ticket, $request->ip());
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Check-in successful',
                    'data' => [
                        'ticket' => $ticket,
                        'boarding_pass_url' => $ticket->boarding_pass ? url('storage/' . $ticket->boarding_pass) : null,
                        'qr_code_url' => $ticket->qr_code ? url('storage/' . $ticket->qr_code) : null
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'error' => 'The requested ticket could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Check-in error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Verify a ticket (for staff use).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyTicket(Request $request)
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

        try {
            $result = $this->ticketService->verifyTicket($request->ticket_code);

            return response()->json([
                'success' => $result['valid'],
                'message' => $result['message'],
                'data' => $result
            ], $result['valid'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Ticket verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => 'An unexpected error occurred'
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
            'ticket_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ticket = Ticket::where('ticket_code', $request->ticket_code)
                ->firstOrFail();

            if ($ticket->status !== 'ACTIVE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is not active',
                    'error' => 'This ticket cannot be boarded due to its status: ' . $ticket->status
                ], 400);
            }

            if ($ticket->boarding_status === 'BOARDED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is already boarded',
                    'error' => 'This ticket was already marked as boarded at ' . $ticket->boarded_at
                ], 400);
            }

            // Check if the ticket is valid for boarding
            $verificationResult = $this->ticketService->verifyTicket($ticket->ticket_code);
            if (!$verificationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is not valid for boarding',
                    'error' => $verificationResult['message']
                ], 400);
            }

            DB::beginTransaction();
            try {
                $ticket = $this->ticketService->markAsBoarded($ticket, $request->user()->id);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Ticket marked as boarded',
                    'data' => [
                        'ticket' => $ticket,
                        'boarding_time' => $ticket->boarded_at
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'error' => 'The requested ticket could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error marking ticket as boarded: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark ticket as boarded',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get boarding pass for a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ticketCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBoardingPass(Request $request, $ticketCode)
    {
        try {
            $ticket = Ticket::where('ticket_code', $ticketCode)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->firstOrFail();

            if (!$ticket->checked_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is not checked in',
                    'error' => 'Please check in first to get a boarding pass'
                ], 400);
            }

            // Generate boarding pass if it doesn't exist
            if (!$ticket->boarding_pass) {
                $boardingPass = $this->ticketService->generateBoardingPass($ticket);
                if (!$boardingPass) {
                    throw new \Exception('Failed to generate boarding pass');
                }
                $ticket->boarding_pass = $boardingPass;
                $ticket->save();
            }

            // Verify the boarding pass file exists
            if (!Storage::disk('public')->exists($ticket->boarding_pass)) {
                throw new \Exception('Boarding pass file not found');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'boarding_pass_url' => url('storage/' . $ticket->boarding_pass)
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'error' => 'The requested ticket could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving boarding pass: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve boarding pass',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
