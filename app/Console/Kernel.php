<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reports:dispatch-due')->everyMinute();
        $schedule->command('reports:cleanup')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
