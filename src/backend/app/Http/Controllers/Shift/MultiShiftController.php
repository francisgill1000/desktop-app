<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\API\SharjahUniversityAPI;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Jobs\SyncMultiShiftDualDayJob;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MultiShiftController extends Controller
{
    public $logFilePath = 'logs/shifts/multi_shift/controller';

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
        $channel = $request->channel ?? "browser";

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);

        $response = [];

        // while ($startDate <= $currentDate && $startDate <= $endDate) {
        while ($startDate <= $endDate) {
            // $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 2, $employee_ids, true);
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 2, $employee_ids, $request->filled("auto_render") ? false : true, $channel);

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        // return $departmentIds = Department::where("company_id",$request->company_id)->pluck("id");
        // $employee_ids = Employee::where("department_id", 31)->pluck("system_user_id");
        $channel = $request->channel ?? "browser";
        return $this->render($request->company_id, $request->date, $request->shift_type_id, $request->UserIds, $request->custom_render ?? true, $channel);
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel)
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
        $logsUpdated = 0;


        foreach ($employees as $row) {

            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"] = $row->schedule->shift ?? false;

            $logs = (new AttendanceLog)->getLogsWithInRangeNew($params);

            $data = $logs[$row->system_user_id] ?? [];
            if (!count($data)) {
                if ($row->schedule->shift && $row->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id" => $row->schedule->shift["id"],
                        "shift_type_id" => $row->schedule->shift["shift_type_id"],
                        "status" => "A",
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

            $totalMinutes = 0;
            $logsJson = [];
            $i = 0;

            $totalMinutes = 0;
            $logsJson = [];

            $totalMinutes = 0;
            $logsJson = [];
            $previousOut = null;

            for ($i = 0; $i < count($data); $i += 2) {
                $currentLog = $data[$i];
                $nextLog = $data[$i + 1] ?? null;

                $currentTime = $currentLog['time'] ?? '---';
                $nextTime = $nextLog['time'] ?? '---';

                $validIn = $currentTime !== '---' && $currentTime !== $previousOut;
                $validOut = $nextTime !== '---' && $nextTime !== $currentTime;

                $minutes = 0;

                if ($validIn && $validOut) {
                    $parsedIn = strtotime($currentTime);
                    $parsedOut = strtotime($nextTime);

                    if ($parsedIn > $parsedOut) {
                        $parsedOut += 86400; // handle midnight crossover
                    }

                    $diff = $parsedOut - $parsedIn;
                    $minutes = $diff / 60;
                    $totalMinutes += $minutes;
                }

                $logsJson[] = [
                    "in" => $validIn
                        ? $this->getLogTime(
                            $currentLog,
                            ["In", "Auto", "Option", "in", "auto", "option", "Mobile", "mobile"],
                            ["Manual", "manual", "MANUAL"]
                        )
                        : "---",
                    "out" => $validOut
                        ? $this->getLogTime(
                            $nextLog,
                            ["Out", "Auto", "Option", "out", "auto", "option", "Mobile", "mobile"],
                            ["Manual", "manual", "MANUAL"]
                        )
                        : "---",
                    "device_in" => $this->getDeviceName($currentLog),
                    "device_out" => $this->getDeviceName($nextLog ?? []),
                    "total_minutes" => $this->minutesToHours($minutes),
                ];

                $item["employee_id"] = $row->system_user_id;
                $item["total_hrs"] = $this->minutesToHours($totalMinutes);

                if ($params["isOverTime"]) {
                    $item["ot"] = $this->calculatedOT(
                        $item["total_hrs"],
                        $params["shift"]->working_hours,
                        $params["shift"]->overtime_interval
                    );
                }

                // Save current out time for next loop
                if ($validOut) {
                    $previousOut = $nextTime;
                }
            }

            $item["logs"] = json_encode($logsJson, JSON_PRETTY_PRINT);

            $items[] = $item;
        }

        try {

            if (count($items) > 0) {
                $model = Attendance::query();
                $model->whereIn("employee_id", array_column($items, "employee_id"));
                $model->where("date", $date);
                $model->where("company_id", $id);
                $model->delete();

                $chunks = array_chunk($items, 100);

                foreach ($chunks as $chunk) {
                    $model->insert($chunk);
                }

                $message = "[" . $date . " " . date("H:i:s") .  "] Multi Shift.   Affected Ids: " . json_encode($UserIds) . " " . $message;

                $logsUpdated = AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", $UserIds ?? [])
                    ->where("LogTime", ">=", $date)
                    ->where("LogTime", "<=", date("Y-m-d", strtotime($date . "+1 day")))
                    // ->where("checked", false)
                    ->update([
                        "checked" => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel" => $channel,
                        "log_message" => substr($message, 0, 200)
                    ]);
            }
        } catch (\Throwable $e) {
            $this->logOutPut($this->logFilePath, $e->getMessage());
        }

        $this->logOutPut($this->logFilePath, [
            "UserIds" => $UserIds,
            "params" => $params,
            "items" => $items,
        ]);

        $this->logOutPut($this->logFilePath, "[" . $date . " " . date("H:i:s") .  "] " . "$logsUpdated " . " updated logs");
        $this->logOutPut($this->logFilePath, $message);
        return "[" . $date . " " . date("H:i:s") .  "] " . $message;
    }

    public function sync(Request $request)
    {
        $request->validate([
            'company_id' => 'required|numeric',
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
            'UserID' => 'nullable',
        ]);

        $id = $request->input('company_id');
        $startDate = Carbon::parse($request->input('from_date'));
        $endDate = Carbon::parse($request->input('to_date'));
        $flag = 'true';
        $UserID = $request->input('UserID');

        // Check if the date range exceeds 5 days
        if ($startDate->diffInDays($endDate) > 5) {
            return response()->json(['error' => 'You cannot select more than 5 dates.'], 400);
        }


        if ($startDate->greaterThan($endDate)) {
            return response()->json(['error' => 'Start date must be before end date.'], 400);
        }

        while ($startDate->lte($endDate)) {
            SyncMultiShiftDualDayJob::dispatch($id, $startDate->toDateString(), $flag, $UserID);
            $startDate->addDay();
        }

        return response()->json([
            'message' => 'Report has been regerated!',
        ]);
    }


    private function getLogTime($log, $validFunctions, $manualDeviceID)
    {
        return $log && $log['time'] ? $log['time'] : "---";
        // return isset($log["device"]["function"]) && in_array($log["device"]["function"], $validFunctions)
        //     || (isset($log["DeviceID"]) && $log["DeviceID"] == $manualDeviceID)
        //     ? $log['time']
        //     : "---";
    }

    private function getDeviceName($log)
    {
        return $log['device']['short_name'] ?? $log['device']['name'] ?? "---";
    }
}
