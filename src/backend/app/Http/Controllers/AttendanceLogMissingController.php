<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Storage;

class AttendanceLogMissingController  extends Controller
{
    public function GetMissingLogs(Request $request)
    {
        $verification_methods = array(
            1 => "Card",
            2 => "Fing",
            3 => "Face",
            4 => "Fing + Card",
            5 => "Face + Fing",
            6 => "Face + Card",
            7 => "Card + Pin",
            8 => "Face + Pin",
            9 => "Fing + Pin",
            10 => "Manual",
            11 => "Fing + Card + Pin",
            12 => "Face + Card + Pin",
            13 => "Face + Fing + Pin",
            14 => "Face + Fing + Card",
            15 => "Repeated"
        );

        // PHP array for reasons
        $reasons = array(
            16 => "Date Expire",
            17 => "Timezone Expire",
            18 => "Holiday",
            19 => "Unregistered",
            20 => "Detection lock",
            23 => "Loss Card",
            24 => "Blacklisted",
            25 => "Without Verification",
            26 => "No Card Verification",
            27 => "No Fingerprint"
        );

        try {
            $total_records = 0;
            //$deviceId = "FC-8300T20094123";
            //$company_id = 2;
            ///$date = "2022-09-20";

            $company_id = $request->company_id;
            $date = $request->date;

            $date = date('Y-m-d', strtotime($date . ' - 1 days'));


            $deviceId = $request->device_id;
            if ($company_id == 0) {
                $device = Device::where("device_id", $deviceId)->first();
                $company_id = $device["company_id"];
            }



            $indexSerialNumber = 0;

            //find serial number 
            $indexSerialNumberModel = AttendanceLog::where("company_id", $company_id)
                ->whereDate("LogTime", '<=', $date)
                ->where("SerialNumber", '>', 0)

                ->where("DeviceID",   $deviceId)->orderBy("LogTime", "DESC")->first();
            if ($indexSerialNumberModel) {
                $indexSerialNumber = $indexSerialNumberModel->SerialNumber;
            }


            // if ($indexSerialNumber > 0) {

            $url = env("SDK_URL") . "/"  . $deviceId . "/GetRecordByIndex";
            //$url =   "https://sdk.mytime2cloud.com/" . $deviceId . "/GetRecordByIndex";
            $data =  [
                "TransactionType" => 1,
                "Quantity" => 60,
                "ReadIndex" => $indexSerialNumber
            ];

            $data = json_encode($data);


            $records = $this->culrmethod($url, $data);
            $records = json_decode($records, true);

            if (isset($records['status']))
                if ($records['status'] != 200) {
                    $records['message'];

                    return [
                        "status" => 100,
                        "message" => "Timeout Error", // $records['message'],
                        "updated_records" => [],
                        "total_device_records" => [],
                        "indexSerialNumber" => $indexSerialNumber,
                    ];
                }
            $finalResult = [];
            foreach ($records['data'] as $record) {

                $logtime = substr(str_replace(" ", " ", $record['recordDate']), 0, -3);
                $data = [
                    "UserID" => $record['userCode'],
                    "DeviceID" => $deviceId,
                    "LogTime" =>  $logtime,
                    "SerialNumber" => $record['recordNumber'],
                    "status" => $record['recordCode'] > 15 ? "Access Denied" : "Allowed",
                    "mode" => $verification_methods[$record['recordCode']] ?? "---",
                    "reason" => $reasons[$record['recordCode']] ?? "---",
                    "company_id" => $company_id,
                ];

                $condition = ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $logtime];
                $exists = AttendanceLog::where('UserID', $record['userCode'])
                    ->where('DeviceID', $deviceId)
                    ->where('LogTime', $logtime)
                    ->exists();

                if (!$exists) {
                    AttendanceLog::create($data);

                    $finalResult[] =  ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $logtime, "SerialNumber" => $record['recordNumber']];
                }
                // $status = AttendanceLog::firstOrCreate(
                //     $condition,
                //     ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $record['recordDate']], // Search by email and username
                //     $data  // Optional attributes to set if the user doesn't exist
                // );


            }
            // } else {
            //     return [
            //         "status" => 120,
            //         "message" => "Device has no  records found on this date " . $date,
            //         "updated_records" => [],
            //         "total_device_records" => [],
            //         "indexSerialNumber" => $indexSerialNumber,

            //     ];
            // }

            return [
                "status" => 200,
                "message" => "success",
                "updated_records" => $finalResult,
                "total_device_records" => count($records['data']),
                "indexSerialNumber" => $indexSerialNumber,
            ];

            return $records;
        } catch (\Exception $e) {
            return [
                "status" => 102,
                "message" => $e->getMessage(),
            ];
            // You can log the error or perform any other necessary actions here
        }
    }

    public function culrmethod($url, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return  $response;
    }
}
