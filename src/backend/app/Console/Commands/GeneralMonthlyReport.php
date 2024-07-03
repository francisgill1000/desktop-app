<?php

namespace App\Console\Commands;

use App\Http\Controllers\Reports\MonthlyController;
use Illuminate\Console\Command;

class GeneralMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate_monthly_report {id} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Monthly Report';

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
        echo (new MonthlyController)->custom_request_general($id, $status, 1);
    }
}
