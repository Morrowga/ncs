<?php

namespace App\Console;

use App\Console\Commands\BuilderguideCron;
use App\Console\Commands\EdgeCron;
use App\Console\Commands\FarmerCron;
use App\Console\Commands\Healthcare;
use App\Console\Commands\IctCron;
use App\Console\Commands\MystyleCron;
use App\Console\Commands\LifestyleCron;
use App\Console\Commands\ModaCron;
use App\Console\Commands\OndoctorCron;
use App\Console\Commands\SayarCron;
use App\Console\Commands\WeddingguideCron;
use App\Console\Commands\YatharCron;
use App\Console\Commands\YoyarlayEntCron;
use App\Console\Commands\YoyarlayHealthCron;
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
        //
        MystyleCron::class,
        LifestyleCron::class,
        Healthcare::class,
        OndoctorCron::class,
        IctCron::class,
        YoyarlayHealthCron::class,
        YoyarlayEntCron::class,
        EdgeCron::class,
        SayarCron::class,
        WeddingguideCron::class,
        YatharCron::class,
        ModaCron::class,
        BuilderguideCron::class,
        FarmerCron::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('mystyle:cron')->cron('*/3 * * * *');
        $schedule->command('lifestyle:cron')->cron('*/10 * * * *');
        $schedule->command('healthcare:cron')->cron('*/10 * * * *');
        $schedule->command('ondoctor:cron')->cron('*/10 * * * *');
        $schedule->command('ict:cron')->cron('*/30 * * * *');
        // $schedule->command('yyl-health:cron')->cron('*/2 * * * *');
        $schedule->command('yyl-ent:cron')->cron('*/10 * * * *');
        $schedule->command('edge:cron')->cron('*/10 * * * *');
        $schedule->command('sayar:cron')->cron('*/10 * * * *');
        $schedule->command('yathar:cron')->cron('*/10 * * * *');
        $schedule->command('weddingguide:cron')->cron('*/10 * * * *');
        $schedule->command('moda:cron')->cron('*/10 * * * *');
        $schedule->command('builderguide:cron')->cron('*/10 * * * *');
        $schedule->command('farmer:cron')->cron('*/10 * * * *');
        $schedule->command('platform:cron')->cron('*/10 * * * *');
        // $schedule->command('ballonestar:cron')->cron('*/10 * * * *');
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
