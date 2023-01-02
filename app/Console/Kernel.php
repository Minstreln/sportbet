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
        'App\Console\Commands\LiveHoje',
        'App\Console\Commands\LiveAmanha',
        'App\Console\Commands\LiveAfterTomorow',
        'App\Console\Commands\Live',
        'App\Console\Commands\Ligas',
        'App\Console\Commands\LigasMain',
        'App\Console\Commands\LoadDay',
        'App\Console\Commands\LiveScore',
        'App\Console\Commands\RefreshOdds',
        'App\Console\Commands\PopulateDbCommand',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('command:liveHoje')->everyFiveMinutes();
         $schedule->command('games:populate')->everyFifteenMinutes();
         $schedule->command('command:liveAmanha')->everyFiveMinutes();
         $schedule->command('command:liveAfter')->everyFiveMinutes();
         $schedule->command('command:live')->everyMinute();
         $schedule->command('command:refreshOdds')->everyMinute();
         $schedule->command('command:liveScore')->everyMinute();
         $schedule->command('command:loadLigas')->everyTenMinutes();
         $schedule->command('command:loadLigasMain')->everyTenMinutes();
         $schedule->command('command:loadDay')->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
