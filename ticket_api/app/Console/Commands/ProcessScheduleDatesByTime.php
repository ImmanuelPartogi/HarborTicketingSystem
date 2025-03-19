<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\RouteController;

class ProcessScheduleDatesByTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:process-by-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process schedule dates based on departure time (changes FULL to DEPARTED)';

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
        $this->info('Processing schedule dates based on departure time...');

        try {
            $routeController = new RouteController();
            $result = $routeController->processScheduleDatesByTime();

            if ($result['success']) {
                $this->info("Successfully processed {$result['processed']} schedule dates.");
                return 0;
            } else {
                $this->error("Failed to process schedule dates: {$result['error']}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            return 1;
        }
    }
}
