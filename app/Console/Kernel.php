<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
{
    $schedule->command('emails:send-new-posts')->hourly();
    $schedule->command('db:backup')->daily();
    // $schedule->command('emails:send-new-posts')->everyMinute(); 
    // for testing
}


    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}