<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan setiap awal bulan pukul 00:00
        $schedule->command('iuran:generate')->monthlyOn(1, '00:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
