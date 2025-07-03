<?php

namespace App\Console\Commands\Shift;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shift\MultiShiftController;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;

class SyncMultiShiftForDualDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_multi_shift_dual_day {company_id} {date} {checked?} {UserID?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("company_id", 1);

        $date = $this->argument("date", date("Y-m-d"));

        $found = Shift::where("company_id", $id)->where("shift_type_id", 2)->count();

        if ($found == 0) {
            return;
        }

        $model = DB::table('schedule_employees as se')
            ->join('attendance_logs as al', 'se.employee_id', '=', 'al.UserID')
            ->join('shifts as sh', 'sh.id', '=', 'se.shift_id')
            ->select('al.UserID')
            ->where('sh.shift_type_id', "=", 2); // this condition not workin

        // if ($this->argument("checked")) {
        //     $model->where('al.checked', $this->argument("checked"));
        // }

        if ($this->argument("UserID")) {
            $model->where('al.UserID', $this->argument("UserID"));
        }

        $all_new_employee_ids = $model->where('se.company_id', $id)
            ->where('al.company_id', $id)
            ->whereDate('al.log_date', $date)
            ->orderBy("al.LogTime")
            // ->take(50)
            ->pluck("al.UserID")
            ->toArray();

        $this->info((new MultiShiftController)->render($id, $date, 2, $all_new_employee_ids, true, "kernel"));
    }
}
