<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Payment;
use App\Models\User;
use App\Models\Schedule;
use App\Services\BookingService;
use App\Services\TicketService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @method \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */
class BookingController extends Controller
{
    protected $bookingService;
    protected $ticketService;
    protected $paymentService;
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param BookingService $bookingService
     * @param TicketService $ticketService
     * @param PaymentService $paymentService
     * @param NotificationService $notificationService
     */
    public function __construct(
        BookingService $bookingService,
        TicketService $ticketService,
        PaymentService $paymentService,
        NotificationService $notificationService
    ) {
        $this->middleware('auth:admin');
        $this->bookingService = $bookingService;
        $this->ticketService = $ticketService;
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of bookings.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = Booking::with(['user', 'schedule.route', 'schedule.ferry']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('booking_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('booking_date', '<=', $dateTo);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bookings.index', compact('bookings', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    /**
     * Display the specified booking.
     *
     * @param Booking $booking
     * @return \Illuminate\View\View
     */
    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'schedule.route',
            'schedule.ferry',
            'passengers',
            'vehicles',
            'tickets',
            'payments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'logs' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        // Get available schedules for rescheduling
        $schedules = Schedule::with('route', 'ferry')
            ->where('route_id', $booking->schedule->route_id)
            ->where('status', 'ACTIVE')
            ->orderBy('departure_time')
            ->get();

        return view('admin.bookings.show', compact('booking', 'schedules'));
    }

    /**
     * Show the tickets for a booking.
     *
     * @param Booking $booking
     * @return \Illuminate\View\View
     */
    public function tickets(Booking $booking)
    {
        $booking->load([
            'user',
            'schedule.route',
            'schedule.ferry',
            'tickets.passenger',
            'tickets.vehicle'
        ]);

        return view('admin.bookings.tickets', compact('booking'));
    }

    /**
     * Manually confirm a booking.
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request, Booking $booking)
    {
        if ($booking->status !== 'PENDING') {
            return back()->with('error', 'Booking is not in pending status');
        }

        try {
            // Create payment record if not exists
            $payment = $booking->payments()->where('status', 'SUCCESS')->first();

            if (!$payment) {
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_amount,
                    'payment_method' => 'BANK_TRANSFER',
                    'payment_channel' => 'MANUAL',
                    'status' => 'SUCCESS',
                    'payment_date' => now(),
                ]);
            }

            // Confirm booking
            $this->bookingService->confirmBooking($booking, []);

            // Log action
            BookingLog::create([
                'booking_id' => $booking->id,
                'previous_status' => 'PENDING',
                'new_status' => 'CONFIRMED',
                'changed_by_type' => 'ADMIN',
                'changed_by_id' => auth()->guard('admin')->id(),
                'notes' => 'Booking manually confirmed by admin',
                'created_at' => now(),
            ]);

            return back()->with('success', 'Booking confirmed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm booking: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a booking.
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (!in_array($booking->status, ['PENDING', 'CONFIRMED', 'RESCHEDULED'])) {
            return back()->with('error', 'Booking cannot be cancelled');
        }

        try {
            $this->bookingService->cancelBooking(
                $booking,
                $request->reason,
                'ADMIN',
                auth()->guard('admin')->id()
            );

            return back()->with('success', 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    /**
     * Complete a booking.
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Booking $booking)
    {
        if (!in_array($booking->status, ['CONFIRMED', 'RESCHEDULED'])) {
            return back()->with('error', 'Booking cannot be completed');
        }

        try {
            $this->bookingService->completeBooking($booking);

            // Log action
            BookingLog::create([
                'booking_id' => $booking->id,
                'previous_status' => $booking->status,
                'new_status' => 'COMPLETED',
                'changed_by_type' => 'ADMIN',
                'changed_by_id' => auth()->guard('admin')->id(),
                'notes' => 'Booking manually completed by admin',
                'created_at' => now(),
            ]);

            return back()->with('success', 'Booking completed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to complete booking: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule a booking.
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reschedule(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (!in_array($booking->status, ['CONFIRMED', 'RESCHEDULED'])) {
            return back()->with('error', 'Booking cannot be rescheduled');
        }

        try {
            $this->bookingService->rescheduleBooking(
                $booking,
                $request->all(),
                'ADMIN',
                auth()->guard('admin')->id()
            );

            return back()->with('success', 'Booking rescheduled successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reschedule booking: ' . $e->getMessage());
        }
    }

    /**
     * Process refund for a booking.
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (!in_array($booking->status, ['CONFIRMED', 'CANCELLED', 'RESCHEDULED'])) {
            return back()->with('error', 'Booking cannot be refunded');
        }

        try {
            // Check if payment exists
            $payment = $booking->payments()->where('status', 'SUCCESS')->first();

            if (!$payment) {
                return back()->with('error', 'No successful payment found for this booking');
            }

            // Validate refund amount
            if ($request->amount > $payment->amount) {
                return back()->with('error', 'Refund amount cannot be greater than payment amount');
            }

            // Process refund
            $refund = $this->paymentService->processRefund(
                $booking,
                $request->amount,
                $request->reason,
                auth()->guard('admin')->id()
            );

            // Update booking status if full refund
            if ($refund->amount == $payment->amount) {
                $booking->status = 'REFUNDED';
                $booking->save();

                // Log status change
                BookingLog::create([
                    'booking_id' => $booking->id,
                    'previous_status' => 'CANCELLED',
                    'new_status' => 'REFUNDED',
                    'changed_by_type' => 'ADMIN',
                    'changed_by_id' => auth()->guard('admin')->id(),
                    'notes' => 'Booking refunded: ' . $request->reason,
                    'created_at' => now(),
                ]);
            }

            return back()->with('success', 'Refund processed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Print tickets for a booking.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     */
    public function printTickets(Booking $booking)
    {
        $booking->load([
            'user',
            'schedule.route',
            'schedule.ferry',
            'tickets.passenger',
            'tickets.vehicle'
        ]);

        return view('admin.bookings.print-tickets', compact('booking'));
    }
}
