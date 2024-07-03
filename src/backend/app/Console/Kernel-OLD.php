<?php

namespace App\Console;

use App\Http\Controllers\DeviceController;
use App\Models\Company;
use App\Models\PayrollSetting;
use App\Models\ReportNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        if (env('APP_ENV') == 'desktop') {

            $schedule->command('task:sync_attendance_logs')->everyMinute();

            $schedule->command('task:sync_attendance_camera_logs')->everyMinute();

            $schedule->command('task:update_company_ids')->everyMinute();

            $companyIds = Company::pluck("id");
            //step 1 ;

            foreach ($companyIds as $companyId) {


                $schedule->command("task:sync_attendance_missing_shift_ids {$companyId} " . date("Y-m-d") . "  ")->everyThirtyMinutes();

                $schedule->command("task:sync_auto_shift {$companyId} " . date("Y-m-d") . " false")->everyFourMinutes();
                $schedule->command("task:sync_auto_shift {$companyId} " . date("Y-m-d") . " true")->everyThirtyMinutes();

                $schedule->command("send_notificatin_for_offline_devices {$companyId}")->everySixHours();

                $schedule
                    ->command("task:sync_multi_shift_night {$companyId} " . date("Y-m-d", strtotime("yesterday")))->hourly()
                    ->between('00:00', '05:59')
                    ->runInBackground();

                $schedule
                    ->command("task:sync_visitor_attendance {$companyId} " . date("Y-m-d"))
                    ->everyFiveMinutes()
                    // ->dailyAt('09:00')
                    //->withoutOverlapping()
                    ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



                $schedule
                    ->command("default_attendance_seeder {$companyId}")
                    ->monthlyOn(1, "00:00")
                    ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                //whatsapp reports 
                $array = ['All', "P", "A", "M", "ME"];
                foreach ($array as $status) {

                    $schedule->command("task:generate_daily_report {$companyId}  {$status}")->dailyAt('03:45'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                    $schedule->command("task:generate_weekly_report {$companyId} {$status}")->dailyAt('04:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                    $schedule->command("task:generate_monthly_report {$companyId} {$status}")->monthlyOn(1, "04:30"); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
                }

                $schedule->command("task:send_whatsapp_notification {$companyId}")

                    ->dailyAt('09:00')
                    ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule
                    ->command("task:sync_leaves $companyId")
                    ->dailyAt('01:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule
                    ->command("task:sync_holidays $companyId")
                    ->dailyAt('01:30'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule
                    ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")
                    ->dailyAt('02:00')
                    ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


                $schedule
                    ->command("task:sync_off $companyId")

                    ->dailyAt('02:00')
                    //->withoutOverlapping()
                    ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


                $schedule
                    ->command("task:sync_visitor_set_expire_dates $companyId")
                    ->everyFiveMinutes()
                    //->withoutOverlapping()
                    ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            }


            $schedule->call(function () {
                $count = Company::where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
                info($count . "companies has been updated");
            })->dailyAt('00:00');
            //->withoutOverlapping();




            $schedule
                ->command('task:check_device_health')
                ->hourly()
                ->between('7:00', '23:59'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $payroll_settings = PayrollSetting::get(["id", "date", "company_id"]);

            foreach ($payroll_settings as $payroll_setting) {

                $payroll_date = (int) (new \DateTime($payroll_setting->date))->modify('-24 hours')->format('d');

                $schedule
                    ->command("task:payslip_generation $payroll_setting->company_id")
                    ->monthlyOn((int) $payroll_date, "00:00"); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }
            //whatsapp and email notifications
            $models = ReportNotification::get();

            foreach ($models as $model) {
                $command_name = "task:report_notification_crons";

                if ($model->type == "alert") {
                    $command_name = "alert:absents";
                }

                $scheduleCommand = $schedule->command("$command_name " . $model->id . ' ' . $model->company_id)
                    ->runInBackground();

                if ($model->frequency == "Daily") {
                    $scheduleCommand->dailyAt($model->time);
                } elseif ($model->frequency == "Weekly") {
                    $scheduleCommand->weeklyOn($model->day, $model->time);
                } elseif ($model->frequency == "Monthly") {
                    $scheduleCommand->monthlyOn($model->day, $model->time);
                }
            }

         
            return;
        }
        // $file_name_raw = "test.txt";
        // Storage::append($file_name_raw,  date("d-m-Y H:i:s") . ' - Devices test listed');

        // $schedule->call(function () {
        //     $file_name_raw = "test.txt";
        //     Storage::append($file_name_raw,  date("d-m-Y H:i:s") . ' - Devices listed');
        // })->everyMinute()->appendOutputTo(storage_path("test.txt"));
        //-------------------------------------------------------------------------------------------------------------------------
        //Schedule Device Access Control 
        $schedule->call(function () {
            exec('pm2 reload 3');
            info("Camera Log listener restart");
        })->everyMinute();

        (new DeviceController())->deviceAccessControllAllwaysOpen($schedule);



        $schedule->call(function () {
            exec('pm2 reload 3');
            info("Camera Log listener restart");
        })->dailyAt('00:00');


        // $schedule->call(function () {
        //     exec('pm2 reload 11');
        //     info("Log listener backup restart");
        // })->monthlyOn(1, "00:00");

        $schedule->call(function () {
            exec('pm2 reload 4');
            info("MyTime2Cloud SDK Production");
        })->dailyAt('05:15');


        $monthYear = date("M-Y");

        $schedule->command('task:sync_attendance_logs')->everyMinute();

        $schedule->command('task:update_company_ids')->everyMinute();


        $companyIds = Company::pluck("id");
        //step 1 ;

        foreach ($companyIds as $companyId) {


            $schedule->command("task:sync_attendance_missing_shift_ids {$companyId} " . date("Y-m-d") . "  ")->everyThirtyMinutes();

            $schedule->command("task:sync_auto_shift {$companyId} " . date("Y-m-d") . " false")->everyFourMinutes();
            $schedule->command("task:sync_auto_shift {$companyId} " . date("Y-m-d") . " true")->everyThirtyMinutes();

            $schedule->command("send_notificatin_for_offline_devices {$companyId}")->everySixHours();

            $schedule
                ->command("task:sync_multi_shift_night {$companyId} " . date("Y-m-d", strtotime("yesterday")))->hourly()
                ->between('00:00', '05:59')
                ->runInBackground();

            $schedule
                ->command("task:sync_visitor_attendance {$companyId} " . date("Y-m-d"))
                ->everyFiveMinutes()
                // ->dailyAt('09:00')
                //->withoutOverlapping()
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $schedule
                ->command("default_attendance_seeder {$companyId}")
                ->monthlyOn(1, "00:00")
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            //whatsapp reports 
            $array = ['All', "P", "A", "M", "ME"];
            foreach ($array as $status) {

                $schedule->command("task:generate_daily_report {$companyId}  {$status}")->dailyAt('03:45'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule->command("task:generate_weekly_report {$companyId} {$status}")->dailyAt('04:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule->command("task:generate_monthly_report {$companyId} {$status}")->monthlyOn(1, "04:30"); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }

            $schedule->command("task:send_whatsapp_notification {$companyId}")

                ->dailyAt('09:00')
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_leaves $companyId")
                ->dailyAt('01:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_holidays $companyId")
                ->dailyAt('01:30'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")
                ->dailyAt('02:00')
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_off $companyId")

                ->dailyAt('02:00')
                //->withoutOverlapping()
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_visitor_set_expire_dates $companyId")
                ->everyFiveMinutes()
                //->withoutOverlapping()
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        }


        $schedule->call(function () {
            $count = Company::where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
            info($count . "companies has been updated");
        })->dailyAt('00:00');
        //->withoutOverlapping();




        $schedule
            ->command('task:check_device_health')
            ->hourly()
            ->between('7:00', '23:59'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



        $payroll_settings = PayrollSetting::get(["id", "date", "company_id"]);

        foreach ($payroll_settings as $payroll_setting) {

            $payroll_date = (int) (new \DateTime($payroll_setting->date))->modify('-24 hours')->format('d');

            $schedule
                ->command("task:payslip_generation $payroll_setting->company_id")
                ->monthlyOn((int) $payroll_date, "00:00"); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }
        //whatsapp and email notifications
        $models = ReportNotification::get();

        foreach ($models as $model) {
            $command_name = "task:report_notification_crons";

            if ($model->type == "alert") {
                $command_name = "alert:absents";
            }

            $scheduleCommand = $schedule->command("$command_name " . $model->id . ' ' . $model->company_id)
                ->runInBackground();

            if ($model->frequency == "Daily") {
                $scheduleCommand->dailyAt($model->time);
            } elseif ($model->frequency == "Weekly") {
                $scheduleCommand->weeklyOn($model->day, $model->time);
            } elseif ($model->frequency == "Monthly") {
                $scheduleCommand->monthlyOn($model->day, $model->time);
            }
        }

        $schedule
            ->command('task:render_missing')
            ->dailyAt('02:15'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        if (env("APP_ENV") == "production") {
            $schedule
                ->command('task:db_backup')
                ->dailyAt('6:00');

            $schedule
                ->command('restart_sdk')
                ->dailyAt('4:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }
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
