<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WhatsappNotificationsLogController;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Employee;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class SharjahUniversityAPI extends Controller
{


    public function readAttendanceAfterRender($attendanceArray)
    {

        $token = "---"; //$this->getToken();
        $logFile = "sharjah_attendance_api_logs/"   . now()->format('d-m-Y') . ".log";

        Storage::append($logFile, date("Y-m-d H:i:s") . ' token :' . $token . PHP_EOL);

        $postData = [];
        if ($token != '') {
            foreach ($attendanceArray as $key => $attendance) {
                //Storage::append($logFile, date("Y-m-d H:i:s") . ' company_id :' . $attendance['company_id'] . PHP_EOL);
                try {
                    // if ($attendance['company_id'] == 13 || $attendance['company_id'] == 2 || $attendance['company_id'] == 22) 
                    // {
                    //     try {
                    //         (new WhatsappNotificationsLogController())->addAttendanceMessageEmployeeId($attendance);
                    //     } catch (\Throwable $e) {
                    //     }
                    // }

                    if ($attendance['company_id'] == 1 &&  (env('APP_ENV') == 'desktop')) {



                        $data = collect($attendance)->only([
                            'employee_id',
                            'logDate',
                            'in',
                            'out',
                            'device_id_in',
                            'device_id_out',
                            'date'
                        ])->toArray();

                        if ($data["out"] == '---') {
                            $dateString = $data["date"] . ' ' . $data["in"];
                            $dateTime = new DateTime($dateString);
                            $isoDate = $dateTime->format('Y-m-d\TH:i:s.v\Z');

                            $postData[]  = [
                                "employeeID" => $data["employee_id"],
                                "logDate" => $isoDate,
                                "terminalID" => $data["device_id_in"],
                                "createdDate" => $isoDate,
                                "functionNo" => "in",
                                "depNo" => null,

                            ];
                        } else {

                            $dateString = $data["date"] . ' ' . $data["out"];
                            $dateTime = new DateTime($dateString);
                            $isoDate = $dateTime->format('Y-m-d\TH:i:s.v\Z');

                            $postData[] = [
                                "employeeID" => $data["employee_id"],
                                "logDate" => $isoDate,
                                "terminalID" => $data["device_id_out"],
                                "createdDate" => $isoDate,
                                "functionNo" => "out",
                                "depNo" => null,

                            ];
                        }





                        //////$response =  $this->pushToAPI($token, $postData);

                        //Storage::append($logFile, date("Y-m-d H:i:s") . ':' . $response . PHP_EOL);
                    }
                } catch (\Throwable $e) {

                    return $e;
                }
            }
            if (count($postData)) {
                $token = $this->getToken();
                try {
                    Storage::append($logFile, date("Y-m-d H:i:s") . ' Data :' . json_encode($postData) . PHP_EOL);
                    //$response =  $this->pushToHydersparkAPI($postData);
                    $response =  $this->pushToAPI($token, $postData);

                    //Storage::append($logFile, date("Y-m-d H:i:s") . ' Response :' . $response . PHP_EOL);
                    Storage::append($logFile, "----------------------------------------------------------" . PHP_EOL);
                } catch (\Throwable $e) {

                    return $e;
                }
            }
        } else {
            Storage::append($logFile, date("Y-m-d H:i:s") . ':' . "Token is empty" . PHP_EOL);

            return "Token Empty";
        }
    }
    public function pushToHydersparkAPI($postData)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://hyderspark.com/sharjah_university_api.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,  // Fixed timeout at 10 seconds
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>   json_encode($postData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],

        ));

        return     $response = curl_exec($curl);

        curl_close($curl);
        return  $response;
    }
    public function pushToAPI($token, $postData)
    {

        $logFile = "sharjah_attendance_api_logs/"   . now()->format('d-m-Y') . ".log";
        Storage::append($logFile, date("Y-m-d H:i:s") . ' Token API :' . $token . PHP_EOL);
        Storage::append($logFile, date("Y-m-d H:i:s") . ' Data API :' . json_encode($postData) . PHP_EOL);
        $response = Http::timeout(300)
            ->withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->post("https://aquhrsys.alqasimia.ac.ae/hrendpoint/api/InsertAccessLog",   $postData);
        Storage::append($logFile, date("Y-m-d H:i:s") . ' Response API 1:' . $response . PHP_EOL);

        //https://aquhrsys.alqasimia.ac.ae/HRENDPointAtt/api/InsertAccessLog
        return  $response;
    }
    public function getToken()
    {
        $logFile = "sharjah_attendance_api_logs/"   . now()->format('d-m-Y') . ".log";



        $response = Http::timeout(300)
            ->withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("https: //aquhrsys.alqasimia.ac.ae/hrendpoint/api/login", [
                "userName" => "attendanceuser",
                "password" => "AQU@Password123",
                "key" => "7112484a-e08b-11ea-87d0-0242ac130003"
            ]);

        //https://aquhrsys.alqasimia.ac.ae/HRENDPointAtt/api/login

        // Storage::append($logFile, date("Y-m-d H:i:s") . ' Response :' . $response . PHP_EOL);

        /*

        $curl = curl_init();

        curl_setopt_array($curl, array(

            CURLOPT_URL => 'https://aquhrsys.alqasimia.ac.ae/HRENDPointAtt/api/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,  // Fixed timeout at 10 seconds
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                "userName" => "attendanceuser",
                "password" => "AQU@Password123",
                "key" => "7112484a-e08b-11ea-87d0-0242ac130003"
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ));

        $response = curl_exec($curl);

        curl_close($curl);
		
		*/

        // Storage::append($logFile, date("Y-m-d H:i:s") . ' Response :' . $response . PHP_EOL);

        $data = json_decode($response, true);

        if (isset($data["token"])) return $data["token"];
        else return '';
    }
}
