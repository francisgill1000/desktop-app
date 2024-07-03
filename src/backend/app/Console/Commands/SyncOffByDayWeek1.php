<?php

namespace App\Console\Commands;

use App\Http\Controllers\AbsentController;
use App\Http\Controllers\FlexibleOffController;
use App\Http\Controllers\OffByDayController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncOffByDayWeek1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_off_by_day_week1 {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Off By Day Week1';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');

        try {
            echo (new OffByDayController)->renderOffByDayWeek1Cron($id);
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncOffByDayWeek1. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: SyncOffByDayWeek1. Error occurred while inserting logs.\n";
        }
    }
}
