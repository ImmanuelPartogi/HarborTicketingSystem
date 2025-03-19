<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AutoResetWeatherStatus::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Reset routes with expired weather issues
        $schedule->command('routes:reset-weather-status')
            ->hourly()
            ->appendOutputTo(storage_path('logs/weather-status-reset.log'));

        // Process schedule dates (FULL to DEPARTED)
        $schedule->command('schedules:process-departed')
            ->everyFifteenMinutes()
            ->between('5:00', '23:00') // Only run during operational hours
            ->appendOutputTo(storage_path('logs/schedule-status-updates.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
