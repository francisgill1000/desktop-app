<?php

namespace App\Console\Commands;

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Shift\RenderController;
use Illuminate\Console\Command;

class SendNotificationForOfflineDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_notificatin_for_offline_devices {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SendNotificationForOfflineDevices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo (new DeviceController)->handleNotification($this->argument("id")) . "\n";
    }
}
