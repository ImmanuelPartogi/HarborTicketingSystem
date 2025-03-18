<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoResetWeatherStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:reset-weather-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reset routes with expired weather issues to active status';

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
        $this->info('Starting automatic reset of routes with expired weather issues...');

        try {
            DB::beginTransaction();

            // Find routes with weather issues where the expiry date has passed
            $expiredRoutes = Route::where('status', 'WEATHER_ISSUE')
                ->whereNotNull('status_expiry_date')
                ->where('status_expiry_date', '<=', Carbon::now())
                ->get();

            $routeCount = $expiredRoutes->count();

            if ($routeCount === 0) {
                $this->info('No routes found with expired weather status.');
                return 0;
            }

            $this->info("Found {$routeCount} routes with expired weather status.");

            // Process each expired route
            foreach ($expiredRoutes as $route) {
                $this->info("Processing route ID: {$route->id} ({$route->origin} to {$route->destination})");

                // Update route status to active
                $route->status = 'ACTIVE';
                $route->status_reason = 'Status otomatis dipulihkan setelah periode masalah cuaca berakhir';
                $route->status_expiry_date = null;
                $route->save();

                // Find affected schedules
                $schedules = Schedule::where('route_id', $route->id)
                    ->where('status', 'DELAYED')
                    ->get();

                $scheduleCount = $schedules->count();
                $this->info("Found {$scheduleCount} affected schedules for this route.");

                // Update each schedule
                foreach ($schedules as $schedule) {
                    $schedule->status = 'ACTIVE';
                    $schedule->save();

                    // Find and update affected schedule dates
                    $affectedDates = ScheduleDate::where('schedule_id', $schedule->id)
                        ->where('status', 'WEATHER_ISSUE')
                        ->whereDate('date', '>=', Carbon::today())
                        ->get();

                    $dateCount = $affectedDates->count();
                    $this->info("Updating {$dateCount} affected dates for schedule ID: {$schedule->id}");

                    foreach ($affectedDates as $date) {
                        $date->status = 'AVAILABLE';
                        $date->status_expiry_date = null;
                        $date->save();
                    }
                }
            }

            DB::commit();
            $this->info('Successfully reset expired weather statuses.');

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Failed to auto-reset weather status: ' . $e->getMessage());

            return 1;
        }
    }
}
