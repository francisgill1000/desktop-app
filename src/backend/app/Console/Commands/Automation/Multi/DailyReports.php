<?php

namespace App\Console\Commands\Automation\Multi;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multi:daily_report {company_id} {branch_id}';

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

        $company_id = $this->argument("company_id") ?? 22;
        $branch_id = $this->argument("branch_id") ?? 38;

        $shift_type_id = 2;

        $apiUrl = env('BASE_URL') . '/api/multi_in_out_monthly_generate';
        // $apiUrl = 'https://mytime2cloud-backend.test' . '/api/multi_in_out_monthly_generate';


        $params = [
            'report_template' => 'Template1',
            'main_shift_type' => $shift_type_id,
            'branch_id' => $branch_id,
            'shift_type_id' => $shift_type_id,
            'company_id' => $company_id,
            'report_type' => 'Monthly',
            'from_date' => date("Y-m-d", strtotime("yesterday")),
            'to_date' => date("Y-m-d", strtotime("yesterday")),
        ];

        $array = [
            -1      => "daily_summary.pdf",
            "P"     => "daily_present.pdf",
            "A"     => "daily_absent.pdf",
            "M"     => "daily_missing.pdf",
        ];

        foreach ($array as $key => $single) {

            $params["status"] = $key;
            $params["file_name"] = $single;

            $response = Http::timeout(300)->withoutVerifying()->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->body(); // or $response->body() for the raw response
                echo json_encode($data);
            } else {
                $error = $response->status();
                echo json_encode($error);
            }
        }
    }
}
