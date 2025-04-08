<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Vehicle;
use App\Models\ScheduleDate;
use App\Models\BookingLog;
use App\Models\Route;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use App\Services\TicketService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingService
{
    protected $ticketService;
    protected $paymentService;
    protected $notificationService;

    /**
     * Create a new service instance.
     *
     * @param TicketService $ticketService
     * @param PaymentService $paymentService
     * @param NotificationService $notificationService
     */
    public function __construct(
        TicketService $ticketService,
        PaymentService $paymentService,
        NotificationService $notificationService
    ) {
        $this->ticketService = $ticketService;
        $this->paymentService = $paymentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new booking.
     *
     * @param array $data
     * @param int $userId
     * @return Booking
     */
    public function createBooking(array $data, int $userId = null)
    {
        try {
            DB::beginTransaction();

            // Buat booking baru hanya dengan jumlah penumpang
            $booking = Booking::create([
                'user_id' => $userId,
                'schedule_id' => $data['schedule_id'],
                'booking_date' => Carbon::parse($data['booking_date']),
                'passenger_count' => $data['passenger_count'], // Hanya menyimpan jumlah
                'total_amount' => $data['total_amount'],
                'status' => 'PENDING',
                'booking_code' => $this->generateBookingCode(),
            ]);

            // Buat data kendaraan jika ada (tanpa owner_passenger_id)
            if (isset($data['vehicles']) && !empty($data['vehicles'])) {
                foreach ($data['vehicles'] as $vehicleData) {
                    $vehicle = Vehicle::create([
                        'booking_id' => $booking->id,
                        'type' => $vehicleData['type'],
                        'plate_number' => $vehicleData['plate_number'],
                        'brand' => $vehicleData['brand'] ?? null,
                        'model' => $vehicleData['model'] ?? null,
                    ]);
                }
            }

            // Update kapasitas jadwal
            $scheduleDate = ScheduleDate::firstOrCreate(
                [
                    'schedule_id' => $booking->schedule_id,
                    'date' => $booking->booking_date->format('Y-m-d')
                ],
                [
                    'status' => 'AVAILABLE',
                    'passenger_count' => 0,
                    'motorcycle_count' => 0,
                    'car_count' => 0,
                    'bus_count' => 0,
                    'truck_count' => 0,
                ]
            );

            $this->updateScheduleDateCapacity($scheduleDate, $booking);

            // Catat log booking
            BookingLog::create([
                'booking_id' => $booking->id,
                'previous_status' => 'NEW',
                'new_status' => 'PENDING',
                'changed_by_type' => $userId ? 'USER' : 'SYSTEM',
                'changed_by_id' => $userId,
                'notes' => 'Pemesanan baru dibuat',
            ]);

            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating booking: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate unique booking code
     *
     * @return string
     */
    private function generateBookingCode()
    {
        $prefix = 'BK';
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return $prefix . $date . $random;
    }

    /**
     * Confirm a booking after payment.
     *
     * @param Booking $booking
     * @param array $paymentData
     * @return Booking
     */
    public function confirmBooking(Booking $booking, array $paymentData)
    {
        try {
            DB::beginTransaction();

            // Pemrosesan pembayaran tetap sama
            $payment = $this->paymentService->createPayment($booking, $paymentData);

            // Update status booking
            $booking->update(['status' => 'CONFIRMED']);

            // Buat tiket penumpang sejumlah passenger_count
            for ($i = 0; $i < $booking->passenger_count; $i++) {
                $ticketData = ['passenger_id' => null, 'vehicle_id' => null];
                $this->ticketService->createTicket($booking, $ticketData);
            }

            // Buat tiket untuk kendaraan tanpa owner_passenger_id
            foreach ($booking->vehicles as $vehicle) {
                $ticketData = [
                    'passenger_id' => null,
                    'vehicle_id' => $vehicle->id,
                ];
                $this->ticketService->createVehicleTicket($booking, $ticketData);
            }

            // Log dan notifikasi tetap sama
            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel a booking.
     *
     * @param Booking $booking
     * @param string $reason
     * @param string $cancelledBy
     * @param int|null $cancelledById
     * @return Booking
     */
    public function cancelBooking(Booking $booking, string $reason, string $cancelledBy = 'USER', ?int $cancelledById = null)
    {
        try {
            DB::beginTransaction();

            $previousStatus = $booking->status;

            // Update booking status
            $booking->update([
                'status' => 'CANCELLED',
                'cancellation_reason' => $reason,
            ]);

            // Cancel all associated tickets
            foreach ($booking->tickets as $ticket) {
                $ticket->update([
                    'status' => 'CANCELLED',
                ]);
            }

            // Release capacity on schedule date
            $scheduleDate = ScheduleDate::where('schedule_id', $booking->schedule_id)
                ->where('date', $booking->booking_date->format('Y-m-d'))
                ->first();

            if ($scheduleDate) {
                $this->releaseScheduleDateCapacity($scheduleDate, $booking);
            }

            // Create booking log
            BookingLog::create([
                'booking_id' => $booking->id,
                'previous_status' => $previousStatus,
                'new_status' => 'CANCELLED',
                'changed_by_type' => $cancelledBy,
                'changed_by_id' => $cancelledById,
                'notes' => 'Pemesanan dibatalkan: ' . $reason,
            ]);

            // Send notification to user
            $this->notificationService->sendBookingCancellation($booking);

            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reschedule a booking.
     *
     * @param Booking $booking
     * @param array $rescheduleData
     * @param string $rescheduledBy
     * @param int|null $rescheduledById
     * @return Booking
     */
    public function rescheduleBooking(Booking $booking, array $rescheduleData, string $rescheduledBy = 'USER', ?int $rescheduledById = null)
    {
        try {
            DB::beginTransaction();

            $previousStatus = $booking->status;
            $oldScheduleId = $booking->schedule_id;
            $oldBookingDate = $booking->booking_date;

            // Check if the new schedule is available for the date
            $newSchedule = Schedule::findOrFail($rescheduleData['schedule_id']);
            $newBookingDate = Carbon::parse($rescheduleData['booking_date']);

            $newScheduleDate = ScheduleDate::where('schedule_id', $newSchedule->id)
                ->where('date', $newBookingDate->format('Y-m-d'))
                ->first();

            if (!$newScheduleDate) {
                $newScheduleDate = ScheduleDate::create([
                    'schedule_id' => $newSchedule->id,
                    'date' => $newBookingDate->format('Y-m-d'),
                    'status' => 'AVAILABLE',
                ]);
            }

            if ($newScheduleDate->status !== 'AVAILABLE') {
                throw new \Exception('Jadwal baru tidak tersedia untuk tanggal ini.');
            }

            // Release capacity on old schedule date
            $oldScheduleDate = ScheduleDate::where('schedule_id', $oldScheduleId)
                ->where('date', $oldBookingDate->format('Y-m-d'))
                ->first();

            if ($oldScheduleDate) {
                $this->releaseScheduleDateCapacity($oldScheduleDate, $booking);
            }

            // Update booking
            $booking->update([
                'schedule_id' => $rescheduleData['schedule_id'],
                'booking_date' => $rescheduleData['booking_date'],
                'status' => 'RESCHEDULED',
            ]);

            // Update schedule date capacities for new date
            $this->updateScheduleDateCapacity($newScheduleDate, $booking);

            // Update tickets with new information
            foreach ($booking->tickets as $ticket) {
                $this->ticketService->updateTicketForReschedule($ticket);
            }

            // Create booking log
            BookingLog::create([
                'booking_id' => $booking->id,
                'previous_status' => $previousStatus,
                'new_status' => 'RESCHEDULED',
                'changed_by_type' => $rescheduledBy,
                'changed_by_id' => $rescheduledById,
                'notes' => 'Pemesanan dijadwalkan ulang ke ' . $newBookingDate->format('d M Y'),
            ]);

            // Send notification to user
            $this->notificationService->sendRescheduleNotification($booking);

            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Complete a booking after journey.
     *
     * @param Booking $booking
     * @return Booking
     */
    public function completeBooking(Booking $booking)
    {
        try {
            DB::beginTransaction();

            $previousStatus = $booking->status;

            // Update booking status
            $booking->update([
                'status' => 'COMPLETED',
            ]);

            // Mark all tickets as used
            foreach ($booking->tickets as $ticket) {
                if ($ticket->status == 'ACTIVE') {
                    $ticket->update([
                        'status' => 'USED',
                    ]);
                }
            }

            // Create booking log
            BookingLog::create([
                'booking_id' => $booking->id,
                'previous_status' => $previousStatus,
                'new_status' => 'COMPLETED',
                'changed_by_type' => 'SYSTEM',
                'notes' => 'Perjalanan selesai, pemesanan diselesaikan',
            ]);

            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update schedule date capacity based on booking.
     *
     * @param ScheduleDate $scheduleDate
     * @param Booking $booking
     * @return void
     */
    private function updateScheduleDateCapacity(ScheduleDate $scheduleDate, Booking $booking)
    {
        // Update passenger count
        $scheduleDate->passenger_count += $booking->passenger_count;

        // Update vehicle counts
        $motorcycleCount = 0;
        $carCount = 0;
        $busCount = 0;
        $truckCount = 0;

        foreach ($booking->vehicles as $vehicle) {
            switch ($vehicle->type) {
                case 'MOTORCYCLE':
                    $motorcycleCount++;
                    break;
                case 'CAR':
                    $carCount++;
                    break;
                case 'BUS':
                    $busCount++;
                    break;
                case 'TRUCK':
                    $truckCount++;
                    break;
            }
        }

        $scheduleDate->motorcycle_count += $motorcycleCount;
        $scheduleDate->car_count += $carCount;
        $scheduleDate->bus_count += $busCount;
        $scheduleDate->truck_count += $truckCount;

        // Check if schedule is now full
        $ferry = $booking->schedule->ferry;

        if (
            $scheduleDate->passenger_count >= $ferry->capacity_passenger ||
            $scheduleDate->motorcycle_count >= $ferry->capacity_vehicle_motorcycle ||
            $scheduleDate->car_count >= $ferry->capacity_vehicle_car ||
            $scheduleDate->bus_count >= $ferry->capacity_vehicle_bus ||
            $scheduleDate->truck_count >= $ferry->capacity_vehicle_truck
        ) {
            $scheduleDate->status = 'FULL';
        }

        $scheduleDate->save();
    }

    /**
     * Release schedule date capacity based on booking.
     *
     * @param ScheduleDate $scheduleDate
     * @param Booking $booking
     * @return void
     */
    private function releaseScheduleDateCapacity(ScheduleDate $scheduleDate, Booking $booking)
    {
        // Update passenger count
        $scheduleDate->passenger_count -= $booking->passenger_count;
        if ($scheduleDate->passenger_count < 0) {
            $scheduleDate->passenger_count = 0;
        }

        // Update vehicle counts
        $motorcycleCount = 0;
        $carCount = 0;
        $busCount = 0;
        $truckCount = 0;

        foreach ($booking->vehicles as $vehicle) {
            switch ($vehicle->type) {
                case 'MOTORCYCLE':
                    $motorcycleCount++;
                    break;
                case 'CAR':
                    $carCount++;
                    break;
                case 'BUS':
                    $busCount++;
                    break;
                case 'TRUCK':
                    $truckCount++;
                    break;
            }
        }

        $scheduleDate->motorcycle_count -= $motorcycleCount;
        if ($scheduleDate->motorcycle_count < 0) {
            $scheduleDate->motorcycle_count = 0;
        }

        $scheduleDate->car_count -= $carCount;
        if ($scheduleDate->car_count < 0) {
            $scheduleDate->car_count = 0;
        }

        $scheduleDate->bus_count -= $busCount;
        if ($scheduleDate->bus_count < 0) {
            $scheduleDate->bus_count = 0;
        }

        $scheduleDate->truck_count -= $truckCount;
        if ($scheduleDate->truck_count < 0) {
            $scheduleDate->truck_count = 0;
        }

        // Reset status to available if it was full
        if ($scheduleDate->status === 'FULL') {
            $scheduleDate->status = 'AVAILABLE';
        }

        $scheduleDate->save();
    }
}
