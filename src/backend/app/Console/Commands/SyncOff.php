<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\RenderController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncOff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_off {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Off';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');

        try {
            echo (new RenderController)->renderOffCron($id);
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncOff. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: SyncOff. Error occurred while inserting logs.\n";
        }
    }
}
