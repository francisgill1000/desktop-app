<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\AutoShiftController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncAutoShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_auto  {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Auto Shift';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $id = $this->argument("company_id");

        $date = $this->argument("date");

        try {
            echo (new AutoShiftController)->render($id, $date, [], false) . "\n";
        } catch (\Throwable $th) {
            //throw $th;
            $error_message = 'Cron: ' . env('APP_NAME') . ': Exception in task:sync_auto  : Company Id :' . $id . ', : Date :' . $date . ', ' . $th;
            Logger::channel("custom")->error($error_message);

            echo $error_message;
        }
    }
}
