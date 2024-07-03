<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\MultiInOutShiftController;
use Illuminate\Console\Command;

class SyncMultiLogsByLogType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_multi_logs_by_log_type {id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Multi Logs By Log Type';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo (new MultiInOutShiftController)->renderByLogType($this->argument("id"), $this->argument("date"));
    }
}
