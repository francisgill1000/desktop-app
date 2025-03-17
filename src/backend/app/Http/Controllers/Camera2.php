<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\StoreRequest;
use App\Http\Requests\Announcement\UpdateRequest;
use App\Models\Announcement;
use App\Models\Device;
use App\Models\Employee;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Camera2 extends Controller
{

    public function camera2PushEvents(Request $request)
    {
        //Sample Data
        // {"json_flag":"pass_record","blur":0.8,"device_ip":"192.168.1.66","device_token":"846411f5a1ed419c9b920ebe0965bc9d","device_sn":"M014200892110002761","liveness_score":99,"liveness_type":1,"pass_type":1,"person_code":"4000","person_id":"15760676","person_name":"Venu Jakku","card_number":null,"qr_code":null,"recognition_score":92,"recognition_type":1,"timestamp":1718178317000,"verification_mode":0,"temperature":0,"temperature_type":0,"mask_type":0,"healthy_state":0,"idcard_number":null,"server_verify":0,"verification_type":0,"clock_status":"Clock On"}

        //try {
        $device_sn = $request->device_sn;
        //$card_number = $request->card_number;
        $card_number = $request->person_code;
        $timestamp = $request->timestamp;
        $recognition_score = $request->recognition_score;

        $clock_status = $request->clock_status;

        if ($request->clock_status == 'Clock On') $clock_status = "In";
        else if ($request->clock_status == 'Clock Off') $clock_status = "Out";



        //----Raw--------
        $file_name_raw = "camera/camera-logs-raw" . date("d-m-Y") . ".txt";
        $message = $card_number . "," . $device_sn . "," . date("Y-m-d H:i:s", (($timestamp) / 1000)  + (60 * 60 * 4)) . "," . $recognition_score . "," . $clock_status;
        Storage::append($file_name_raw, json_encode($message));

        //------Raw Data-------

        $deviceAttendancefunction = Device::where("device_id", $device_sn)->pluck("function")->first();
        if ($deviceAttendancefunction == 'option' && $clock_status == 'None') {
            return false;
        }

        $timeZone = 'Asia/Dubai';
        $deviceTimezone = Device::where("device_id", $device_sn)->pluck("utc_time_zone")->first();
        if ($deviceTimezone != '') {
            $timeZone = $deviceTimezone;
        }
        $timestamp = $timestamp / 1000; // Convert milliseconds to seconds
        $dateTime = new DateTime("@$timestamp");
        $dateTime->setTimezone(new DateTimeZone($timeZone));


        //`${UserCode},${DeviceID},${RecordDate},${RecordNumber}`;
        // /`../backend/storage/app/camera/camera-logs-${formattedDate}.csv`
        if ($card_number > 0 && $device_sn != '') {
            $file_name = "camera/camera-logs-" . date("d-m-Y") . ".csv";
            $message = $card_number . "," . $device_sn . "," . $dateTime->format('Y-m-d H:i:s') . "," . $recognition_score . "," . $clock_status;
            //chmod($file_name, 666);
            Storage::append($file_name, $message);
        } else {
            $file_name = "camera/camera2-error-logs-" . date("d-m-Y") . ".log";
            Logger::channel("custom")->error('Error occured while inserting Camera2 logs logs.' . $message);
            return $this->getMeta("Sync Attenance Camera2 Logs", " Error occured." . "\n");
        }
        // } catch (\Throwable $th) {

        //     Logger::channel("custom")->error('Error occured while inserting Camera2 logs logs.');
        //     Logger::channel("custom")->error('Error Details: ' . $th);
        //     return $this->getMeta("Sync Attenance Camera2 Logs", " Error occured." . "\n");
        // }
    }
}
