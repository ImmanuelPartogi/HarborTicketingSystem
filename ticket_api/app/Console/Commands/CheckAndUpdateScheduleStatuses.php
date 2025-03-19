<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleDate;
use Carbon\Carbon;

class CheckAndUpdateScheduleStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates FULL schedule dates to DEPARTED when arrival time has passed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("Starting schedule status updates at {$now->format('Y-m-d H:i:s')}");

        $updatedCount = 0;

        // Find all FULL schedule dates for today
        $scheduleDates = ScheduleDate::with('schedule')
            ->where('status', 'FULL')
            ->whereDate('date', $now->format('Y-m-d'))
            ->get();

        $this->info("Found {$scheduleDates->count()} FULL schedule dates for today");

        foreach ($scheduleDates as $scheduleDate) {
            if ($scheduleDate->schedule) {
                // Get arrival time and combine with date
                $arrivalTime = $scheduleDate->schedule->arrival_time;
                $arrivalDateTime = Carbon::parse(
                    $scheduleDate->date->format('Y-m-d') . ' ' . $arrivalTime->format('H:i:s')
                );

                $this->line("Checking schedule date #{$scheduleDate->id}: arrival time is {$arrivalDateTime->format('Y-m-d H:i:s')}");

                // If arrival time has passed, mark as DEPARTED
                if ($now->gt($arrivalDateTime)) {
                    $this->info("Updating schedule date #{$scheduleDate->id} from FULL to DEPARTED");
                    $scheduleDate->status = 'DEPARTED';
                    $scheduleDate->save();
                    $updatedCount++;
                } else {
                    $minutesRemaining = $now->diffInMinutes($arrivalDateTime, false);
                    $this->line("Schedule date #{$scheduleDate->id} has not reached arrival time yet ({$minutesRemaining} minutes remaining)");
                }
            }
        }

        $this->info("Completed with {$updatedCount} updates");

        return Command::SUCCESS;
    }
}
