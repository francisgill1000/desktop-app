<?php

namespace App\Console\Commands\AccessControl;

use App\Jobs\GenerateAccessControlReport;
use App\Models\AttendanceLog;
use App\Models\Company;
use Illuminate\Console\Command;

class PDFReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:access-control-report-generate {company_id} {date}';

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
        $company_id = $this->argument("company_id", 0);

        $date = $this->argument("date", date("Y-m-d"));

        $model = AttendanceLog::query();

        $model->where("company_id", $company_id);

        $model->where('LogTime', '>=', "$date 00:00:00");

        $model->where('LogTime', '<=', "$date 23:59:59");

        $model->whereHas('device', function ($q) use ($company_id) {
            $q->whereIn('device_type', ["all", "Access Control"]);
            $q->where('company_id', $company_id);
        });

        $model->with([
            'device'    => fn($q) => $q->where('company_id', $company_id),
            'employee'  => fn($q) => $q->where('company_id', $company_id),
            'visitor'   => fn($q) => $q->where('company_id', $company_id),
        ]);


        $data = $model->get()->toArray();

        $company = Company::whereId($company_id)->first();

        $chunks = array_chunk($data, 10);

        $params = ["report_type" => "Date Wise Report"];

        foreach ($chunks as $index => $chunk) {

            $batchKey = $index + 1;

            GenerateAccessControlReport::dispatch($chunk, $company_id, $date, $params, $company, $batchKey, count($chunks));
        };
    }
}
