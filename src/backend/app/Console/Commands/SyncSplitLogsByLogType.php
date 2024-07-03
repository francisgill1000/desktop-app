<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\SplitShiftController;
use Illuminate\Console\Command;

class SyncSplitLogsByLogType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_split_logs_by_log_type {id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Split Logs By Log Type';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo (new SplitShiftController)->renderByLogType($this->argument("id"), $this->argument("date")) . "\n";
    }
}
