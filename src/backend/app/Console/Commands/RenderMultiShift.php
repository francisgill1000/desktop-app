<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RenderMultiShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'render:multi_shift {company_id} {date}';

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

        $id = $this->argument("company_id");

        $date = $this->argument("date");

        $nextDate = date("Y-m-d", strtotime($date . "+1 day"));

        $employee_ids = Employee::whereHas("attendance_logs", function ($q) use ($id, $date, $nextDate) {
            $q->where("company_id", $id);
            // $q->where("system_user_id", "673");
            $q->where("LogTime", ">=", $date); // Check for logs on or after the current date
            $q->where("LogTime", "<=", $nextDate); // Check for logs on or before the next date
        })->pluck("system_user_id")->toArray();

        $payload = [
            'date' => '',
            'UserID' => '',
            'updated_by' => "26",
            'company_ids' => [$id],
            'manual_entry' => true,
            'reason' => '',
            'employee_ids' => $employee_ids,
            'dates' => [$date, $nextDate],
            'shift_type_id' => 2
        ];

        $url = 'https://backend.mytime2cloud.com/api/render_logs';

        $response = Http::withoutVerifying()->get($url, $payload);

        if ($response->successful()) {
            $this->info("render:multi_shift executed with " . $id);
        } else {
            $error_message = 'Cron: ' . env('APP_NAME') . ': Exception in render:multi_shift  : Company Id :' . $id . ', : Date :' . $date . ', ' . $response->json();
            $this->info(json_encode($error_message));
        }
    }
}
