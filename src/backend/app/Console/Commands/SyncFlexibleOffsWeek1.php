<?php

namespace App\Console\Commands;

use App\Http\Controllers\AbsentController;
use App\Http\Controllers\FlexibleOffController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncFlexibleOffsWeek1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_flexible_offs_week1 {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Flexible Offs Week1';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');

        try {
            echo (new FlexibleOffController)->renderFlexibleOffWeek1Cron($id);
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncFlexibleOffsWeek1. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: SyncFlexibleOffsWeek1. Error occurred while inserting logs.\n";
        }
    }
}
