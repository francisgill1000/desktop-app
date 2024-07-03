<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class DeviceCameraController extends Controller
{
    public  $camera_sdk_url = '';

    public function __construct($camera_sdk_url)
    {
        $this->camera_sdk_url = $camera_sdk_url;
    }
    public function updateCameraDeviceLiveStatus($company_id)
    {
        return  $online_devices_count = 0;
        $devices = Device::where('company_id', $company_id)->where('model_number', "CAMERA1");

        $devices->clone()->update(["status_id" => 2]);

        foreach ($devices->get() as $device) {


            $this->camera_sdk_url = $device->camera_sdk_url;

            $sessionResponse = $this->getActiveSessionId();
            if ($sessionResponse['status']) {
                $sessionId = $sessionResponse['message'];

                $devicesInfo = $this->curlPost('/ISAPI/DeviceInfo?ID=' . $sessionId, ' ');

                $xml = simplexml_load_string($devicesInfo);
                $DeviceID = (string) $xml->DeviceID;
                if ($DeviceID != '') {

                    Device::where("device_id", $DeviceID)->where('device_category_name', "CAMERA")->update(["status_id" => 1, "last_live_datetime" => date("Y-m-d H:i:s")]);

                    $online_devices_count++;
                    Log::channel("camerasdk")->info($DeviceID . " - Live status updated");
                    //return $MACAddress . " - Live status updated";
                }
            } else {
                //return $sessionResponse['message'];
            }
        }

        return  $online_devices_count;
    }

    public function pushUserToCameraDevice($name,  $system_user_id, $base65Image)
    {




        $gender  = 'Male';
        $sessionResponse = $this->getActiveSessionId();
        if ($sessionResponse['status']) {
            $sessionId = $sessionResponse['message'];


            $postData = '<RegisterImage>
            <FaceItem>
            <Name>' . $name . '</Name> 
            <CardType>0</CardType>
            <CardNum>' . $system_user_id . '</CardNum> 
            <Gender>Male</Gender>
            <Overwrite>0</Overwrite>
            <ImageContent>' . $base65Image . '</ImageContent>
            </FaceItem>
            </RegisterImage>';
            $response = $this->curlPost('/ISAPI/FaceDetection/RegisterImage?ID=' . $sessionId, $postData);

            $xml = simplexml_load_string($response);

            if ($xml->StatusCode == 200) {

                $FeatureCode = $xml->FeatureCode;
                Log::channel("camerasdk")->info($name . ' - ' . $system_user_id . ' - ' . $xml->StatusString . ' - Successfully Uploaded');
                return  $xml->StatusString;
            } else {
                Log::channel("camerasdk")->error($name . ' - ' . $system_user_id . ' - ' . $xml->StatusString . ' - Failed: ');
                return  $xml->StatusString;
            }
        } else {

            Log::channel("camerasdk")->info($name . ' - ' . $system_user_id . ' - ' . $sessionResponse['message']);
            return $sessionResponse['message'];
        }
    }
    public function getActiveSessionId()
    {



        $post_data = ' ';
        $response = $this->curlPost('/ISAPI/Security/Login', $post_data);
        $xml = simplexml_load_string($response);
        if ($xml == '') {
            return ["message" => "SessionID is not generated.", "status" => false];
        }
        $sessionId = (string) $xml->SessionId;


        //activate the sessionid

        if ($sessionId == '') {
            return ["message" => "SessionID is not geenrated.", "status" => false];
        } else if (env("CAMERA_SDK_LOGIN_USERNAME") == '') {
            return ["message" => "SDK Username is Empty ", "status" => false];
        } else if (env("CAMERA_SDK_LOGIN_PASSWORD")  == '') {
            return   ["message" => "SDK Password is Empty", "status" => false];
        }

        $md5string = md5($sessionId . ':' . env("CAMERA_SDK_LOGIN_USERNAME") . ':' . env("CAMERA_SDK_LOGIN_PASSWORD") . ':IPCAM');
        if ($md5string != '') {



            $post_data = '<UserCheck>
                <Username>' . env("CAMERA_SDK_LOGIN_USERNAME") . '</Username>
                <Password>' . $md5string . '</Password>
                <SessionId>' . $sessionId . '</SessionId>
            </UserCheck>';
            $response = $this->curlPost('/ISAPI/Security/Login', $post_data);

            $xml = simplexml_load_string($response);
            $StatusCode = (string) $xml->StatusCode;
            if ($StatusCode == 200) {

                return   ["message" => $sessionId, "status" => true];
            } else {
                return   ["message" => "SessionID activation is failed", "status" => false];

                Log::channel("camerasdk")->error("SessionID activation is failed");
            }
        } else {
            return   ["message" => "Invalid MD5 String", "status" => false];
        }
    }
    public function curlPost($url, $post_data)
    {



        // $url = env('CAMERA_SDK_URL') .   $url;
        if ($this->camera_sdk_url != '') {


            $url = $this->camera_sdk_url .   $url;


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,


                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/plain'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            Log::channel("camerasdk")->info('CURL ' . $url . '-');
            return $response;
        } else {
            Log::channel("camerasdk")->info('CURL ' .  $url . '- EMPTY SDK URL in DB Devices Table');
        }
    }

    // public function curlPostImage($url, $post_data)
    // {



    //     $url = env('CAMERA_SDK_URL') .   $url;
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,


    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $post_data,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: text/plain'
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     curl_close($curl);

    //     return $response;
    // }
    // public function curlPost_Old($url, $post_data)
    // {



    //     $url = env('CAMERA_SDK_URL') .   $url;
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $post_data,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: text/plain'
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     curl_close($curl);

    //     return $response;
    // }
}
