<?php

namespace App\Console\Commands;

use App\Http\Controllers\OffByDayController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncOffByDayWeek2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_off_by_day_week2 {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Off By Day Week2';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');

        try {
            echo (new OffByDayController)->renderOffByDayWeek2Cron($id);
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncOffByDayWeek2. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: SyncOffByDayWeek2. Error occurred while inserting logs.\n";
        }
    }
}
