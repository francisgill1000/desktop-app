<?php

namespace App\Console\Commands;

use App\Http\Controllers\DeviceController;
use App\Models\Company;
use App\Models\Device;
use Illuminate\Console\Command;

// use Illuminate\Support\Facades\Log as Logger;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\NotifyIfLogsDoesNotGenerate;

class CheckDeviceHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:check_device_health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Device Health';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $return = (new DeviceController())->checkDevicesHealthCompanyId();
        echo $return;
        info($return);


        // $devices = Device::where("device_type", "!=", "Mobile")
        //     ->where("name", "!=", "Manual")
        //     ->get(["company_id", "device_id"]);



        // $total_iterations = 0;
        // $online_devices_count = 0;
        // $offline_devices_count = 0;

        // $sdk_url = '';

        // if ($sdk_url == '') {
        //     $sdk_url = env("SDK_URL"); // "http://139.59.69.241:5000";
        // }
        // // if (env("APP_ENV") != "production") {
        // //     $sdk_url = env("SDK_STAGING_COMM_URL");
        // // }

        // if (checkSDKServerStatus($sdk_url) === 0) {
        //     $date = date("Y-m-d H:i:s");
        //     echo "[$date] Cron: CheckDeviceHealth. SDK Server is down.\n";
        //     return;
        // }

        // $companiesIds = [];

        // foreach ($devices as $device_id) {

        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => "$sdk_url/CheckDeviceHealth/" . $device_id->device_id,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'POST',
        //     ));

        //     return   $response = curl_exec($curl);

        //     curl_close($curl);
        //     if (json_decode($response)) {
        //         $status = json_decode($response);

        //         if ($status && $status->status == 200) {
        //             $online_devices_count++;
        //         } else {
        //             $offline_devices_count++;

        //             $companiesIds[$device_id->company_id] =  $device_id->company_id;
        //         }

        //         Device::where("device_id", $device_id->device_id)->update(["status_id" => $status->status == 200 ? 1 : 2]);

        //         $total_iterations++;
        //     } else {
        //         echo "Error\n";
        //     }
        // }

        // $count = Company::whereIn("id", array_values($companiesIds))->where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
        // info($count . "companies has been updated");

        // $date = date("Y-m-d H:i:s");
        // $script_name = "CheckDeviceHealth";

        // $meta = "[$date] Cron: $script_name.";

        // $result = "$offline_devices_count Devices offline. $online_devices_count Devices online. $total_iterations records found";

        // $message = $meta . " " . $result . ".\n";
        // echo $message;
    }

    public function checkSDKServerStatus($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $httpCode;
    }
}
