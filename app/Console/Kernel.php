<?php

namespace App\Console;

use App\Console\Commands\SendNewMessageNotification;
use App\Jobs\ExpiredSubscription;
use App\Jobs\RenewSubscriptionJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ExpiredSubscription())->everyMinute();
        $schedule->job(new SendNewMessageNotification())->everyMinute();
        
        // Update service stats cache every 15 minutes for fast homepage loading
        $schedule->command('services:update-stats')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
