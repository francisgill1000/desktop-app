<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Shift\MultiInOutShiftController;
use App\Models\AttendanceLog;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log as Logger;

class AttendanceController extends Controller
{
    public function seedDefaultData($company_id, $UserIds = [], $branch_id = '')
    {


        $params = ["company_id" => $company_id, "date" => date("Y-m-d"), "branch_id" => $branch_id, "UserIds" => $UserIds];

        $employees = Employee::query();

        $employees->where("company_id", $params["company_id"]);

        $employees->withOut(["department", "sub_department", "designation"]);

        $employees->with(["schedule" => function ($q) use ($params) {
            $q->where("company_id", $params["company_id"]);
            $q->where("to_date", ">=", $params["date"]);
            // $q->where("shift_type_id", $params["shift_type_id"]);
            $q->withOut("shift_type");
            $q->select("shift_id", "isOverTime", "employee_id", "shift_type_id");
            $q->orderBy("to_date", "asc");
        }]);
        $employees->when($branch_id != '', function ($q) use ($params) {
            $q->where("branch_id", $params["branch_id"]);
        });

        $employees->when(count($params["UserIds"] ?? []) > 0, function ($q) use ($params) {
            $q->where("company_id", $params["company_id"]);
            $q->whereIn("system_user_id", $params["UserIds"]);
        });


        if (!$employees->count()) {
            info("No record found");
            return;
        }
        // $attendance = Attendance::query();
        // $attendance->where("company_id", $company_id);
        // $attendance->whereMonth("date", date("m"));
        // $attendance->delete();
        $daysInMonth = Carbon::now()->month(date('m'))->daysInMonth;

        $employees = $employees->get(["system_user_id"]);

        $data = [];

        foreach ($employees as $employee) {



            foreach (range(1, $daysInMonth) as $day) {
                $data[] = [
                    "date" => date("Y-m-") . sprintf("%02d", date($day)),
                    "employee_id" => $employee->system_user_id,
                    "shift_id" => $employee->schedule ? $employee->schedule->shift_id : null,
                    "shift_type_id" => $employee->schedule ? $employee->schedule->shift_type_id : null,
                    "status" => "A",
                    "in" => "---",
                    "out" => "---",
                    "total_hrs" => "---",
                    "ot" => "---",
                    "late_coming" => "---",
                    "early_going" => "---",
                    "device_id_in" => "---",
                    "device_id_out" => "---",
                    "company_id" => $company_id,
                    "created_at"    => date('Y-m-d H:i:s'),
                    "updated_at"    => date('Y-m-d H:i:s'),
                    "updated_func" => "seedDefaultData"
                ];
            }
        }

        $chunks = array_chunk($data, 100);

        $insertedCount = 0;


        $attendance = Attendance::query();
        $attendance->where("company_id", $company_id);
        if (count($UserIds) > 0) {
            $attendance->where("employee_id", $UserIds[0]);
        }
        $attendance->whereMonth("date", date("m"));
        $attendance->delete();


        foreach ($chunks as $chunk) {
            Attendance::insert($chunk);
            //$attendance->updateOrCreate($chunk);
            $insertedCount += count($chunk);
        }

        $message = "Cron AttendanceSeeder: " . $insertedCount . " record has been inserted.";

        Logger::channel("defaulSeeder")->info('Cron: Creating Default seeder. seedDefaultData: ' .  $message);
        return $message;
    }

    public function getAttendanceTabsDisplay(Request $request)
    {

        $model = Shift::where("company_id", $request->company_id);


        $singleShiftEmployeeCount = $model->clone()->whereIn("shift_type_id", [1,    4, 5, 6])->get()->count();
        $DualShiftEmployeeCount  =  $model->clone()->where("shift_type_id", 7)->get()->count();
        $multiShiftEmployeeCount  =  $model->clone()->where("shift_type_id", 2)->get()->count();
        $date = date("Y-m-d");
        if ($request->filled("date")) {
            $date = $request->to_date;
        }


        if ($singleShiftEmployeeCount == 0) {
            $singleShiftEmployeeCount = Attendance::where("date", '>=', $date . ' 00:00:00')
                ->where("date", '<=',  $date . ' 23:59:00')
                ->where("company_id", $request->company_id)
                ->whereIn("shift_type_id", [1,    4, 5, 6])

                ->get()->count();
        }
        if ($DualShiftEmployeeCount == 0) {
            $DualShiftEmployeeCount = Attendance::where("date", '>=', $date . ' 00:00:00')
                ->where("date", '<=',  $date . ' 23:59:00')
                ->where("company_id", $request->company_id)
                ->where("shift_type_id", 7)->get()->count();
        }
        if ($multiShiftEmployeeCount == 0) {
            $multiShiftEmployeeCount = Attendance::where("date", '>=', $date . ' 00:00:00')
                ->where("date", '<=',  $date . ' 23:59:00')
                ->where("company_id", $request->company_id)
                ->where("shift_type_id", 2)->get()->count();
        }


        return [
            "single" => $singleShiftEmployeeCount > 0 ? true : false,
            "dual" => $DualShiftEmployeeCount > 0 ? true : false,
            "multi" => $multiShiftEmployeeCount > 0 ? true : false
        ];
    }
    public function attendance_avg_clock(Request $request)
    {
        //     $attendanceCounts =   AttendanceLog::selectRaw('DATE("LogTime") as date, MIN("LogTime") as first_entry')
        //     ->groupBy('date')
        //     ->orderBy('date', 'asc')->get();;


        // return $attendanceCounts;
        // Assuming your timestamps are stored in a 'timestamp' column in your model's database table
        //$timestamps = AttendanceLog::pluck('LogTime');


        $avg_clock_in = $this->getAvgClockIn($request);
        $avg_clock_out = $this->getAvgClockOut($request);
        $avg_working_hours = $this->getAvgWorkingHours($request);
        $leavesArray = $this->getEmployeeLeavecount($request);

        return ["avg_clock_in" => $avg_clock_in, "avg_clock_out" => $avg_clock_out, "avg_working_hours" => $avg_working_hours, "leaves" => $leavesArray];
    }

    public function getEmployeeLeavecount($request)
    {

        $model = Attendance::where("employee_id", $request->system_user_id)
            ->where("date", '>=', $request->start_date)
            ->where("date", '<=', $request->end_date)
            ->where("company_id", $request->company_id);




        return  $info = (object) [
            'total_absent' => $model->clone()->where('status', 'A')->count(),
            'total_present' => $model->clone()->where('status', 'P')->count(),
            'total_off' => $model->clone()->where('status', 'O')->count(),
            'total_missing' => $model->clone()->where('status', 'M')->count(),
            'total_leaves' => $model->clone()->where('status', 'L')->count(),
            'total_early' => $model->clone()->where('early_going', '!=', '---')->count(),


        ];
    }
    public function getAvgClockIn($request)
    {

        $timestamps = AttendanceLog::where("UserID", $request->system_user_id)
            ->where("LogTime", '>=', $request->start_date)
            ->where("LogTime", '<=', $request->end_date)
            ->where("company_id", $request->company_id)->orderBy('LogTime', 'asc')->pluck('LogTime');

        $timeDifferences = [];
        $date_prev = '';
        foreach ($timestamps as $timestamp) {
            $timeComponents = explode(' ', $timestamp);
            $date = $timeComponents[0];
            if ($date != $date_prev) {
                $time = $timeComponents[1];
                list($hours, $minutes) = explode(':', $time);
                $totalSeconds = $hours * 3600 + $minutes * 60;
                $timeDifferences[] = $totalSeconds;

                $date_prev = $date;
            }
        }

        if (count($timeDifferences) > 0) {
            $averageTimeInSeconds = array_sum($timeDifferences) / count($timeDifferences);
        } else {
            $averageTimeInSeconds = 0;
        }
        $averageTimeFormatted = gmdate("H:i", $averageTimeInSeconds);

        return $averageTimeFormatted;
    }
    public function getAvgClockOut($request)
    {


        $timestamps = AttendanceLog::where("UserID", $request->system_user_id)
            ->where("LogTime", '>=', $request->start_date)
            ->where("LogTime", '<=', $request->end_date)
            ->where("company_id", $request->company_id)->orderBy('LogTime', 'desc')->pluck('LogTime');


        $dateCount = array();

        foreach ($timestamps as $timestamp) {
            $date = date("Y-m-d", strtotime($timestamp));

            if (isset($dateCount[$date])) {
                $dateCount[$date]++;
            } else {
                $dateCount[$date] = 1;
            }
        };

        $timeDifferences = [];
        $date_prev = '';
        foreach ($timestamps as $timestamp) {
            $timeComponents = explode(' ', $timestamp);
            $date = $timeComponents[0];
            if ($date != $date_prev &&  $dateCount[$date] > 1) {
                $time = $timeComponents[1];
                list($hours, $minutes) = explode(':', $time);
                $totalSeconds = $hours * 3600 + $minutes * 60;
                $timeDifferences[] = $totalSeconds;

                $date_prev = $date;
            }
        }

        if (count($timeDifferences) > 0) {
            $averageTimeInSeconds = array_sum($timeDifferences) / count($timeDifferences);
        } else {
            $averageTimeInSeconds = 0;
        }
        $averageTimeFormatted = gmdate("H:i", $averageTimeInSeconds);

        return $averageTimeFormatted;
    }

    public function  getAvgWorkingHours($request)
    {
        $timestamps = Attendance::where("employee_id", $request->system_user_id)
            ->where("date", '>=', $request->start_date)
            ->where("date", '<=', $request->end_date)
            ->where("company_id", $request->company_id)
            ->where("total_hrs", '!=', "---")
            ->pluck('total_hrs');

        $timeDifferences = [];



        foreach ($timestamps as $time) {

            list($hours, $minutes) = explode(':', $time);
            $totalSeconds = $hours * 3600 + $minutes * 60;
            $timeDifferences[] = $totalSeconds;
        }



        if (count($timeDifferences) > 0) {
            $averageTimeInSeconds = array_sum($timeDifferences) / count($timeDifferences);
        } else {
            $averageTimeInSeconds = 0;
        }
        $averageTimeFormatted = gmdate("H:i", $averageTimeInSeconds);

        return $averageTimeFormatted;
    }
    public function seedDefaultDataManual(Request $request)
    {
        $scheduleEmployees = ScheduleEmployee::withOut("shift", "shift_type")
            ->where("company_id", $request->company_id)
            ->get([
                "shift_id",
                "employee_id",
                "shift_type_id",
                "company_id",
            ]);

        if ($scheduleEmployees->isEmpty()) {
            $message = "Cron AttendanceSeeder: No record found.";
            info($message);
            return $message;
        }

        $daysInMonth = Carbon::now()->month(date('m'))->daysInMonth;

        $startDate = $request->startDate ?? date('j');

        $endDate = $request->endDate ?? $daysInMonth;


        $arr = [];


        foreach ($scheduleEmployees as $scheduleEmployee) {
            foreach (range($startDate, $endDate) as $day) {
                $arr[] = [
                    "date" => date("Y-m-") . ($day < 10 ? '0' . $day : $day),
                    "employee_id" => $scheduleEmployee->employee_id,
                    "shift_id" => $scheduleEmployee->shift_id,
                    "shift_type_id" => $scheduleEmployee->shift_type_id,
                    "status" => "---",
                    "in" => "---",
                    "out" => "---",
                    "total_hrs" => "---",
                    "ot" => "---",
                    "late_coming" => "---",
                    "early_going" => "---",
                    "device_id_in" => "---",
                    "device_id_out" => "---",
                    "company_id" => $request->company_id,
                ];
            }
        }

        $attendance = Attendance::query();
        $attendance->whereIn("date", array_column($arr, "date"));
        $attendance->whereIn("employee_id", array_column($arr, "employee_id"));
        $attendance->where("company_id", $request->company_id);
        $attendance->delete();
        $attendance->insert($arr);
        // return $attendance->get();
        $message = "Cron AttendanceSeeder: " . count($arr) . " record has been inserted.";

        info($message);

        return $message;
    }

    public function ProcessAttendance()
    {

        // $night = new NightShiftController;
        // $night->processNightShift();

        // $single = new SingleShiftController;
        // $single->processSingleShift();

        $multiInOut = new MultiInOutShiftController;
        return $multiInOut->processShift();
    }



    public function SyncAttendance()
    {
        $items = [];
        $model = AttendanceLog::query();
        $model->where("checked", false);
        $model->take(1000);
        if ($model->count() == 0) {
            return false;
        }
        return $logs = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);

        $i = 0;

        foreach ($logs as $log) {

            $date = date("Y-m-d", strtotime($log->LogTime));

            $AttendanceLog = new AttendanceLog;

            $orderByAsc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);
            $orderByDesc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);

            $first_log = $orderByAsc->orderBy("LogTime")->first() ?? false;
            $last_log =  $orderByDesc->orderByDesc('LogTime')->first() ?? false;

            $logs = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date)->count();

            $item = [];
            $item["company_id"] = $log->company_id;
            $item["employee_id"] = $log->UserID;
            $item["date"] = $date;

            if ($first_log) {
                $item["in"] = $first_log->time;
                $item["status"] = "---";
                $item["device_id_in"] = $first_log->DeviceID ?? "---";
            }
            if ($logs > 1 && $last_log) {
                $item["out"] = $last_log->time;
                $item["device_id_out"] = $last_log->DeviceID ?? "---";
                $item["status"] = "P";
                $diff = abs(($last_log->show_log_time - $first_log->show_log_time));
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $item["total_hrs"] = (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
            }

            $attendance = Attendance::whereDate("date", $date)->where("employee_id", $log->UserID);

            $attendance->first() ? $attendance->update($item) : Attendance::create($item);

            AttendanceLog::where("id", $log->id)->update(["checked" => true]);

            $i++;

            // $items[$date][$log->UserID] = $item;
        }

        return $i;
    }

    public function SyncAbsent()
    {
        $previousDate = date('Y-m-d', strtotime('-1 days'));

        $employeesThatDoesNotExist = ScheduleEmployee::with('roster')->whereDoesntHave('attendances', function ($q) use ($previousDate) {
            $q->whereDate('date', $previousDate);
        })
            ->get(["employee_id", "company_id", "roster_id"])
            ->groupBy("company_id");

        // Debug
        // $employeesThatDoesNotExist = ScheduleEmployee::whereIn("company_id", [1, 8])->whereIn("employee_id", [1001])
        //     ->whereDoesntHave('attendances', function ($q) use ($previousDate) {
        //         $q->whereDate('date', $previousDate);
        //     })
        //     ->get(["employee_id", "company_id"]);

        return $this->runFunc($employeesThatDoesNotExist, $previousDate);
    }


    public function SyncAbsentByManual(Request $request)
    {
        // return $this->SyncAbsent();

        $date = $request->input('date', date('Y-m-d'));
        $previousDate = date('Y-m-d', strtotime($date . '-1 days'));
        // return [$date, $previousDate];
        $model = ScheduleEmployee::whereIn("company_id", $request->company_ids);

        $model->when(count($request->UserIDs ?? []) > 0, function ($q) use ($request) {
            $q->whereIn("employee_id", $request->UserIDs);
        });

        $model->whereDoesntHave('attendances', function ($q) use ($previousDate) {
            $q->whereDate('date', $previousDate);
        });

        return $employeesThatDoesNotExist =  $model->with('roster')
            ->get(["employee_id", "company_id", "shift_type_id", "roster_id"])
            ->groupBy("company_id");
        return $this->runFunc($employeesThatDoesNotExist, $previousDate);
    }


    public function SyncAbsentForMultipleDays()
    {
        $first = AttendanceLog::orderBy("id")->first();
        $today = date('Y-m-d');
        $startDate = $first->edit_date;
        $difference = strtotime($startDate) - strtotime($today);
        $days = abs($difference / (60 * 60) / 24);
        $arr = [];

        for ($i = $days; $i > 0; $i--) {
            $arr[] = $this->SyncAbsent($i);
        }

        return json_encode($arr);
    }

    public function ResetAttendance(Request $request)
    {
        $items = [];
        $model = AttendanceLog::query();
        $model->whereBetween("LogTime", [$request->from_date ?? date("Y-m-d"), $request->to_date ?? date("Y-m-d")]);
        $model->where("DeviceID", $request->DeviceID);

        if ($model->count() == 0) {
            return false;
        }
        $logs = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);


        $i = 0;

        foreach ($logs as $log) {

            $date = date("Y-m-d", strtotime($log->LogTime));

            $AttendanceLog = new AttendanceLog;

            $orderByAsc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);
            $orderByDesc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);

            $first_log = $orderByAsc->orderBy("LogTime")->first() ?? false;
            $last_log =  $orderByDesc->orderByDesc('LogTime')->first() ?? false;

            $logs = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date)->count();

            $item = [];
            $item["company_id"] = $log->company_id;
            $item["employee_id"] = $log->UserID;
            $item["date"] = $date;

            if ($first_log) {
                $item["in"] = $first_log->time;
                $item["status"] = "---";
                $item["device_id_in"] = Device::where("device_id", $first_log->DeviceID)->pluck("id")[0] ?? "---";
            }
            if ($logs > 1 && $last_log) {
                $item["out"] = $last_log->time;
                $item["device_id_out"] = Device::where("device_id", $last_log->DeviceID)->pluck("id")[0] ?? "---";
                $item["status"] = "P";
                $diff = abs(($last_log->show_log_time - $first_log->show_log_time));
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $item["total_hrs"] = (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
            }


            $attendance = Attendance::whereDate("date", $date)->where("employee_id", $log->UserID);

            $attendance->first() ? $attendance->update($item) : Attendance::create($item);

            AttendanceLog::where("id", $log->id)->update(["checked" => true]);

            $i++;

            $items[$date][$log->UserID] = $item;
        }

        Storage::disk('local')->put($request->DeviceID . '-' . date("d-M-y") . '-reset_attendance.txt', json_encode($items));

        return $i;
    }

    public function runFunc($companyIDs, $previousDate)
    {
        $result = null;
        $record = [];
        foreach ($companyIDs as $companyID => $employeesThatDoesNotExist) {
            $NumberOfEmployee = count($employeesThatDoesNotExist);

            if (!$NumberOfEmployee) {
                $result .= $this->getMeta("SyncAbsent", "No employee(s) found against company id $companyID .\n");
                continue;
            }


            $employee_ids = [];
            foreach ($employeesThatDoesNotExist as $employee) {
                $arr = [
                    "employee_id"   => $employee->employee_id,
                    "date"          => $previousDate,
                    "status"        => $this->getDynamicStatus($employee, $previousDate),
                    "company_id"    => $employee->company_id,
                    "shift_type_id"    => $employee->shift_type_id,

                    "created_at"    => date('Y-m-d H:i:s'),
                    "updated_at"    => date('Y-m-d H:i:s'),
                    "updated_func" => "runFunc"
                ];
                $record[] = $arr;

                $employee_ids[] = $employee->employee_id;
            }

            $result .= $this->getMeta("SyncAbsent", "$NumberOfEmployee employee(s) absent against company id $companyID.\n Employee IDs: " . json_encode($employee_ids));
        }


        Attendance::insert($record);
        // return $record[0];
        return $result;
    }

    public function getDynamicStatus($employee, $date)
    {
        $shift = array_filter($employee->roster->json, function ($shift) use ($date) {
            return $shift['day'] ==  date('D', strtotime($date));
        });

        $obj = reset($shift);

        if ($obj['shift_id'] == -1) {
            return "OFF";
        }
        return "A";
    }

    public function seedFakeDataForTesting($company_id, $employee_id)
    {
        $params = ["company_id" => $company_id, "date" => date("Y-m-d"), "employee_id" => $employee_id];

        $employees = Employee::query();

        $employees->where("company_id", $params["company_id"]);
        $employees->where("system_user_id", $params["employee_id"]);


        $employees->withOut(["department", "sub_department", "designation"]);

        $employees->with(["schedule" => function ($q) use ($params) {
            $q->where("company_id", $params["company_id"]);
            $q->where("employee_id", $params["employee_id"]);
            $q->where("to_date", ">=", $params["date"]);
            // $q->where("shift_type_id", $params["shift_type_id"]);
            $q->withOut("shift_type");
            $q->select("shift_id", "isOverTime", "employee_id", "shift_type_id");
            $q->orderBy("to_date", "asc");
        }]);

        $daysInMonth = Carbon::now()->month(date('m'))->daysInMonth;

        $employee = $employees->first();

        if (!$employee) {
            info("No record found");
            return;
        }

        $data = [];

        foreach (range(1, $daysInMonth) as $day) {
            $data[] = [
                "date" => date("Y-m-") . sprintf("%02d", date($day)),
                "employee_id" => $params["employee_id"],
                "shift_id" => $employee->schedule->shift_id,
                "shift_type_id" => $employee->schedule->shift_type_id,
                "status" => Arr::random(["P", "A", "M", "O", "ME"]),
                "in" => "---",
                "out" => "---",
                "total_hrs" => "---",
                "ot" => "---",
                "late_coming" => "---",
                "early_going" => "---",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "company_id" => $company_id,
            ];
        }

        $chunks = array_chunk($data, 100);

        $insertedCount = 0;

        $attendance = Attendance::query();
        $attendance->where("company_id", $company_id);
        $attendance->whereMonth("date", date("m"));
        $attendance->delete();

        foreach ($chunks as $chunk) {
            $attendance->insert($chunk);
            $insertedCount += count($chunk);
        }

        $message = "Cron AttendanceSeeder: " . $insertedCount . " record has been inserted.";
        return $message;
    }
}
