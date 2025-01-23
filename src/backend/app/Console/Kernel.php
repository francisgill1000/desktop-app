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
        $schedule->command('task:sync_attendance_logs')->everyMinute();

        $schedule->command('task:sync_attendance_camera_logs')->everyMinute();

        $schedule->command('task:sync_alarm_logs')->everyMinute();

        (new DeviceController())->deviceAccessControllAllwaysOpen($schedule);

        $schedule->command('task:update_company_ids')->everyMinute();

        $companyId = 1;

        $schedule->command("pdf:generate $companyId")->dailyAt('03:35')->runInBackground();

        $schedule->command("pdf:access-control-report-generate {$companyId} " . date("Y-m-d", strtotime("yesterday")))
            ->dailyAt('04:35')->runInBackground();

        $schedule
            ->command("task:sync_attendance_missing_shift_ids {$companyId} " . date("Y-m-d") . "  ")
            ->everyThirtyMinutes();


        //-------------Shift Related Commands-------------------//
        $schedule
            ->command("task:sync_auto_shift $companyId " . date("Y-m-d"))
            ->everyFifteenMinutes()
            ->runInBackground();

        $schedule
            ->command("task:sync_except_auto_shift $companyId " . date("Y-m-d"))
            ->everyFifteenMinutes()
            ->runInBackground();

        //-------------Shift Related Commands End -------------------//

        $schedule->command("send_notificatin_for_offline_devices {$companyId}")->everySixHours();
        $schedule
            ->command("send_notificatin_for_offline_devices {$companyId}")
            ->everySixHours();

        $schedule
            ->command("render:night_shift {$companyId} " . date("Y-m-d", strtotime("yesterday")))
            ->everyTenMinutes();

        $schedule
            ->command("render:multi_shift {$companyId} " . date("Y-m-d", strtotime("yesterday")))
            ->everyTenMinutes();

        $schedule
            ->command("default_attendance_seeder {$companyId}")
            ->monthlyOn(1, "00:00")
            ->runInBackground();

        //whatsapp reports 
        $array = ['All', "P", "A", "M", "ME"];
        foreach ($array as $status) {

            $schedule->command("task:generate_daily_report {$companyId}  {$status}")->dailyAt('03:45'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule->command("task:generate_weekly_report {$companyId} {$status}")->dailyAt('04:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        }

        $schedule->command("task:send_whatsapp_notification {$companyId}")
            ->dailyAt('09:00')
            ->runInBackground();

        $schedule
            ->command("task:sync_leaves $companyId")
            ->dailyAt('01:00');

        $schedule
            ->command("task:sync_holidays $companyId")
            ->dailyAt('01:30');

        $schedule
            ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")
            ->dailyAt('02:00')
            ->runInBackground();


        $schedule
            ->command("task:sync_off $companyId")
            ->dailyAt('02:00')
            ->runInBackground();


        $schedule
            ->command("task:sync_visitor_set_expire_dates $companyId")
            ->everyFiveMinutes()
            ->runInBackground();

        $schedule->call(function () {
            $count = Company::where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
            info($count . "companies has been updated");
        })->dailyAt('00:00');

        $schedule
            ->command('task:check_device_health')
            ->hourly()
            ->between('7:00', '23:59');

        $payroll_settings = PayrollSetting::get(["id", "date", "company_id"]);

        foreach ($payroll_settings as $payroll_setting) {

            $payroll_date = (int) (new \DateTime($payroll_setting->date))->modify('-24 hours')->format('d');

            $schedule
                ->command("task:payslip_generation $payroll_setting->company_id")
                ->monthlyOn((int) $payroll_date, "00:00");
        }

        //whatsapp and email notifications
        $models = ReportNotification::get();


        foreach ($models as $model) {

            $schedule
                ->command("multi:daily_report " . $model->company_id . " " . $model->branch_id)
                ->dailyAt('3:45');

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

        // $schedule
        //     ->command('restart_sdk')
        //     ->dailyAt('4:00'); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
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
