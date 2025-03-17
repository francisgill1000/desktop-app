<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use Illuminate\Console\Command;

class UpdateLogDateColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_log_date_column {company_id?}';

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

        $rows = AttendanceLog::where("company_id", $id ?? 1)->get(["id", "LogTime", "log_date"])->toArray();

        foreach ($rows as $key => $value) {
            $result =  AttendanceLog::where("id", $value["id"])
                ->update([
                    "log_date" => date("Y-m-d", strtotime($value["LogTime"]))
                ]);
        }

        $this->info(AttendanceLog::where("company_id", $id ?? 1)->count());
    }
}
