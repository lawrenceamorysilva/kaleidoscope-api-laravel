<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the Artisan commands for the application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ImportShippingCosts::class,
        \App\Console\Commands\SyncNetoProducts::class, // ✅ Register your custom command
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // You can uncomment this when needed:
        // $schedule->command('neto:sync-products')->dailyAt('02:00');

        // Runs every 30 minutes
        $schedule->command('sync:neto-products')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
