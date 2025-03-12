<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Send a booking confirmation notification.
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendBookingConfirmation(Booking $booking)
    {
        $user = $booking->user;

        // Create in-app notification
        $this->createNotification(
            $user->id,
            'Pemesanan Dikonfirmasi',
            'Pemesanan tiket ferry #' . $booking->booking_code . ' telah dikonfirmasi. Silakan cek detail tiket Anda.',
            'BOOKING'
        );

        // Send email
        try {
            Mail::send('emails.booking-confirmation', ['booking' => $booking], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Konfirmasi Pemesanan Tiket Ferry');
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Send a booking cancellation notification.
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendBookingCancellation(Booking $booking)
    {
        $user = $booking->user;

        // Create in-app notification
        $this->createNotification(
            $user->id,
            'Pemesanan Dibatalkan',
            'Pemesanan tiket ferry #' . $booking->booking_code . ' telah dibatalkan.',
            'BOOKING'
        );

        // Send email would be implemented here

        return true;
    }

    /**
     * Send a reschedule notification.
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendRescheduleNotification(Booking $booking)
    {
        $user = $booking->user;

        // Create in-app notification
        $this->createNotification(
            $user->id,
            'Jadwal Pemesanan Diubah',
            'Pemesanan tiket ferry #' . $booking->booking_code . ' telah dijadwalkan ulang ke tanggal ' .
            $booking->booking_date->format('d M Y') . '.',
            'SCHEDULE_CHANGE'
        );

        // Send email would be implemented here

        return true;
    }

    /**
     * Send a boarding reminder.
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendBoardingReminder(Booking $booking)
    {
        $user = $booking->user;
        $schedule = $booking->schedule;
        $departureTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $schedule->departure_time->format('H:i:s'));

        // Create in-app notification
        $this->createNotification(
            $user->id,
            'Pengingat Boarding',
            'Perjalanan ferry Anda dari ' . $schedule->route->origin . ' ke ' . $schedule->route->destination .
            ' akan berangkat dalam 1 jam. Silakan segera menuju ke pelabuhan.',
            'BOARDING'
        );

        // Send email would be implemented here

        return true;
    }

    /**
     * Send a payment reminder.
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendPaymentReminder(Booking $booking)
    {
        $user = $booking->user;
        $payment = $booking->payments()->where('status', 'PENDING')->latest()->first();

        if (!$payment) {
            return false;
        }

        // Create in-app notification
        $this->createNotification(
            $user->id,
            'Pengingat Pembayaran',
            'Pemesanan tiket ferry #' . $booking->booking_code . ' belum dibayar. Silakan selesaikan pembayaran sebelum ' .
            $payment->expiry_date->format('d M Y H:i') . '.',
            'PAYMENT'
        );

        // Send email would be implemented here

        return true;
    }

    /**
     * Send a schedule change notification.
     *
     * @param Booking $booking
     * @param string $message
     * @return bool
     */
    public function sendScheduleChangeNotification(Booking $booking, string $message)
    {
        $user = $booking->user;

        // Create in-app notification
        $this->createNotification(
            $user->id,
            'Perubahan Jadwal',
            $message,
            'SCHEDULE_CHANGE'
        );

        // Send email would be implemented here

        return true;
    }

    /**
     * Create an in-app notification.
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param array|null $data
     * @return Notification
     */
    private function createNotification(int $userId, string $title, string $message, string $type, ?array $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'data' => $data ? json_encode($data) : null,
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param Notification $notification
     * @return Notification
     */
    public function markAsRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
        ]);

        return $notification;
    }

    /**
     * Mark all notifications for a user as read.
     *
     * @param User $user
     * @return bool
     */
    public function markAllAsRead(User $user)
    {
        $user->notifications()->update([
            'is_read' => true,
        ]);

        return true;
    }

    /**
     * Get unread notifications for a user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications(User $user)
    {
        return $user->notifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Schedule upcoming notifications for bookings (to be called by a scheduler).
     *
     * @return void
     */
    public function scheduleUpcomingNotifications()
    {
        // Find bookings with departure within the next hour
        $bookingsForBoardingReminder = Booking::whereIn('status', ['CONFIRMED', 'RESCHEDULED'])
            ->whereHas('schedule', function ($query) {
                $now = Carbon::now();
                $oneHourLater = Carbon::now()->addHour();

                // This is a simplification and would need to be adjusted for actual time comparison
                $query->whereRaw("CONCAT(bookings.booking_date, ' ', schedules.departure_time) BETWEEN ? AND ?", [
                    $now->format('Y-m-d H:i:s'),
                    $oneHourLater->format('Y-m-d H:i:s'),
                ]);
            })
            ->get();

        foreach ($bookingsForBoardingReminder as $booking) {
            $this->sendBoardingReminder($booking);
        }

        // Find bookings with pending payments that expire soon
        $bookingsForPaymentReminder = Booking::where('status', 'PENDING')
            ->whereHas('payments', function ($query) {
                $now = Carbon::now();
                $twoHoursLater = Carbon::now()->addHours(2);

                $query->where('status', 'PENDING')
                    ->whereBetween('expiry_date', [$now, $twoHoursLater]);
            })
            ->get();

        foreach ($bookingsForPaymentReminder as $booking) {
            $this->sendPaymentReminder($booking);
        }
    }
}
