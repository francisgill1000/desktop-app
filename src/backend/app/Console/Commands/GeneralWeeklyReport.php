<?php

namespace App\Console\Commands;

use App\Http\Controllers\Reports\WeeklyController;
use Illuminate\Console\Command;

class GeneralWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate_weekly_report {id} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Weekly Report';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("id");
        $status = $this->argument("status");
        if ($status == "All") $status = "-1";
        echo (new WeeklyController)->custom_request_general($id, $status, 1);
    }
}
