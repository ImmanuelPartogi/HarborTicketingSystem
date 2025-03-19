<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessScheduleDepartedStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:process-departed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process FULL schedule dates to DEPARTED status when departure time has passed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting schedule dates status processing...');

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $today = $now->format('Y-m-d');

            // Get all FULL schedule dates for today
            $scheduleDates = ScheduleDate::with(['schedule'])
                ->where('status', 'FULL')
                ->whereDate('date', $today)
                ->get();

            $this->info("Found " . count($scheduleDates) . " FULL schedule dates for today.");

            $processedCount = 0;

            foreach ($scheduleDates as $scheduleDate) {
                if (!$scheduleDate->schedule) {
                    $this->warn("Schedule not found for date ID: " . $scheduleDate->id);
                    continue;
                }

                // Combine date with departure time to get the exact departure datetime
                $departureTime = $scheduleDate->schedule->departure_time->format('H:i:s');
                $departureDateTime = Carbon::parse($today . ' ' . $departureTime);

                // If departure time has passed, change status to DEPARTED
                if ($now->gt($departureDateTime)) {
                    $this->info("Changing status to DEPARTED for schedule date ID: " . $scheduleDate->id);

                    $scheduleDate->status = 'DEPARTED';
                    $scheduleDate->save();

                    $processedCount++;
                }
            }

            DB::commit();

            $this->info("Successfully processed {$processedCount} schedule dates to DEPARTED status.");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Failed to process schedule dates: ' . $e->getMessage());

            return 1;
        }
    }
}
