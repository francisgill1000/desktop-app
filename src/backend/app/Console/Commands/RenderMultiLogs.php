<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\RenderController;
use Illuminate\Console\Command;

class RenderMultiLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'render_multi_logs {id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Render logs for multi in and out shift';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo (new RenderController)->renderMulti($this->argument("id"), $this->argument("date"));
    }
}
