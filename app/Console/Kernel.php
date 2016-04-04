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
        // Commands\Inspire::class,
        Commands\SendReminderEmails::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('reminder:email --due=7')               // 만료일 7일 이내인 태스트를
              ->daily()                                            // 매일 자정에
              ->appendOutputTo('storage/logs/reminder-email.log') // 알림 메일을 전송하고 로깅 기록
              ->withoutOverlapping();                              // 기존 전송 작업이 안 끝났을 경우 중복 작업 안 함

    }
}
