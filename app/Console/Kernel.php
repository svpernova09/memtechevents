<?php

namespace MemtechEvents\Console;

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
        \MemtechEvents\Console\Commands\Inspire::class,
        \MemtechEvents\Console\Commands\UpdateEvents::class,
        \MemtechEvents\Console\Commands\ScheduleTweets::class,
        \MemtechEvents\Console\Commands\ProcessTweets::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }
}
