<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;

class MultiShiftController extends Controller
{
    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        // $endDateString = $request->dates[1];
        if (isset($request->dates[1])) {
            $endDateString = $request->dates[1];
        } else {
            $endDateString = $request->dates[0];
        }
        $company_id = $request->company_ids[0];
        $employee_ids = $request->employee_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $response = [];

        // while ($startDate <= $currentDate && $startDate <= $endDate) {
        while ($startDate <= $endDate) {
            // $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 2, $employee_ids, true);
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 2, $employee_ids, $request->filled("auto_render") ? false : true);

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        // return $departmentIds = Department::where("company_id",$request->company_id)->pluck("id");
        // $employee_ids = Employee::where("department_id", 31)->pluck("system_user_id");

        return $this->render($request->company_id, $request->date, $request->shift_type_id, $request->UserIds, $request->custom_render ?? true);
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false)
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {
            //$params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsToRender($params);
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        // return json_encode($params);

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);


        //update shift ID for No logs 
        if (count($employees) == 0) {
            $employees = (new Employee)->GetEmployeeWithShiftDetails($params);

            foreach ($employees as $key => $value) {


                if ($value->schedule->shift && $value->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id" => $value->schedule->shift["id"],
                        "shift_type_id" => $value->schedule->shift["shift_type_id"]
                    ];
                    $model1 = Attendance::query();
                    $model1->whereIn("employee_id", $UserIds);
                    $model1->where("date", $params["date"]);
                    $model1->where("company_id", $params["company_id"]);
                    $model1->update($data1);
                }
            }
        }

        $items = [];
        $message = "";

        foreach ($employees as $row) {

            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"] = $row->schedule->shift ?? false;

            $logs = (new AttendanceLog)->getLogsWithInRangeNew($params);



            $data = $logs[$row->system_user_id] ?? [];
            if (!count($data)) {


                if ($row->schedule->shift && $row->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id" => $row->schedule->shift["id"],
                        "shift_type_id" => $row->schedule->shift["shift_type_id"]
                    ];
                    $model1 = Attendance::query();
                    $model1->where("employee_id", $row->system_user_id);
                    $model1->where("date", $params["date"]);
                    $model1->where("company_id", $params["company_id"]);
                    $model1->update($data1);
                }
                $message .= "{$row->system_user_id}   has No Logs to render";
                continue;
            }
            if (!$params["shift"]["id"]) {
                $message .= "{$row->system_user_id} : No shift configured on date: $date";
                continue;
            }

            $item = [
                "total_hrs" => 0,
                "in" => "---",
                "out" => "---",
                "ot" => "---",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "company_id" => $params["company_id"],
                "shift_id" => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"]  ?? 0,
                "status" => count($data) % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,

            ];

            $logsJson = [];

            $totalMinutes = 0;

            for ($i = 0; $i < count($data); $i++) {
                $currentLog = $data[$i];
                $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;
                $item["employee_id"] = $row->system_user_id;

                if ($nextLog && $nextLog['device']['function'] == "In") {
                    $i++;
                    $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;
                } else if ($currentLog && $currentLog['device']['function'] == "Out") {
                    $i++;
                    $currentLog = isset($data[$i + 1]) ? $data[$i + 1] : false;
                }

                $minutes = 0;


                if ((isset($currentLog['time']) && $currentLog['time'] != '---') and (isset($nextLog['time']) && $nextLog['time'] != '---')) {


                    $parsed_out = strtotime($nextLog['time'] ?? 0);
                    $parsed_in = strtotime($currentLog['time'] ?? 0);

                    if ($parsed_in > $parsed_out) {
                        //$item["extra"] = $nextLog['time'];
                        $parsed_out += 86400;
                    }

                    $diff = $parsed_out - $parsed_in;

                    $minutes =  ($diff / 60);

                    //$totalMinutes += $minutes > 0 ? $minutes : 0;

                    $totalMinutes += $minutes;
                }

                $logsJson[] = [
                    "in" => (isset($currentLog["device"]["function"]) && ($currentLog["device"]["function"] == "In" || $currentLog["device"]["function"] == "auto")) || (isset($currentLog["DeviceID"]) && $currentLog["DeviceID"] == "Manual") ? $currentLog['time'] : "---",
                    "out" => ($nextLog && isset($nextLog["device"]["function"]) && ($nextLog["device"]["function"] == "Out" || $nextLog["device"]["function"] == "auto")) || ($nextLog && isset($nextLog["DeviceID"]) && $nextLog["DeviceID"] == "Manual") ? $nextLog['time'] : "---",
                    "device_in" => isset($currentLog['device']) ? ($currentLog['device']['short_name'] ?? $currentLog['device']['name'] ?? "---") : "---",
                    "device_out" => isset($nextLog['device']) ? ($nextLog['device']['short_name'] ?? $nextLog['device']['name'] ?? "---") : "---",
                    "total_minutes" => $this->minutesToHours($minutes),
                ];

                $item["total_hrs"] = $this->minutesToHours($totalMinutes);

                if ($params["isOverTime"]) {
                    $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
                }

                $i++;
            }

            $item["logs"] = json_encode($logsJson);

            $items[] = $item;
        }


        $UserIds = array_column($items, "employee_id");

        try {

            $model = Attendance::query();
            $model->whereIn("employee_id", $UserIds);
            $model->where("date", $date);
            $model->where("company_id", $id);
            $model->delete();

            $chunks = array_chunk($items, 100);

            foreach ($chunks as $chunk) {
                $model->insert($chunk);
            }

            //if (!$custom_render)
            {
                AttendanceLog::where("company_id", $id)->whereIn("UserID", $UserIds)
                    ->where("LogTime", ">=", $date . ' 00:00:00')
                    ->where("LogTime", "<=", $date . ' 23:59:00')
                    ->update(["checked" => true, "checked_datetime" => date('Y-m-d H:i:s')]);
            }
            $message = "[" . $date . " " . date("H:i:s") .  "] Multi Shift.   Affected Ids: " . json_encode($UserIds) . " " . $message;
        } catch (\Throwable $e) {
            $message = $this->getMeta("Multi Shift ", $e->getMessage());
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }
}
