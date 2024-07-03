<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class OffByDayController extends Controller
{

    public function renderOffByDayWeek1Cron($company_id = 0)
    {
        // return date('l', strtotime('-1 day'));
        $UserIds = $this->renderOffByDayScript($company_id, date('Y-m-d', strtotime('-1 day')), 1);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->getMeta("Sync Off By Day Week 1", "$result Employee has been marked as Off" . ".\n");
    }

    public function renderOffByDayWeek2Cron($company_id = 0)
    {
        $UserIds = $this->renderOffByDayScript($company_id, date('Y-m-d', strtotime('-1 day')), 2);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->getMeta("Sync Off By Day Week 2", "$result Employee has been marked as Off" . ".\n");
    }

    public function renderOffByDayWeek1(Request $request)
    {
        $UserIds = $this->renderOffByDayScript($request->company_id, $request->date, 1, $request->UserID);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->response("$result Employee has been marked as Off", null, true);
    }

    public function renderOffByDayWeek2(Request $request)
    {
        $UserIds = $this->renderOffByDayScript($request->company_id, $request->date, 2, $request->UserID);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->response("$result Employee has been marked as Off", null, true);
    }

    public function renderOffByDayScript($company_id, $date, $weekNumber, $user_id = 0)
    {
        $model = Employee::query();
        $model->withOut(["department", "designation", "sub_department"]);
        $model->where("company_id", $company_id);

        $model->when($user_id, fn ($q) => $q->where("system_user_id", $user_id));

        $model->with(["schedule" =>  function ($q) use ($date, $company_id) {
            $q->where('to_date', '>=', $date);
            $q->orderBy('to_date', 'asc');
            $q->whereHas("shift");
            $q->whereDoesntHave("attendance_logs", function ($q) use ($company_id, $date) {
                $q->whereDate('LogTime', $date);
                $q->where("company_id", $company_id);
            });
        }]);

        $daysBack = $weekNumber == 1 ? 6 : 13;


        $daysBack = date('Y-m-d', strtotime($date . ' -' . $daysBack . ' days'));


        // $model->withCount(["attendances" =>  function ($q) use ($company_id, $date, $daysBack) {
        //     $q->where('date', '>', $daysBack);
        //     $q->where('date', '<=', $date);
        //     $q->where('status', 'A');
        //     $q->where("company_id", $company_id);
        // }]);

        $missingEmployees = $model->get(["employee_id", "system_user_id"]);

        $records = [];

        foreach ($missingEmployees as $missingEmployee) {

            $schedule = $missingEmployee->schedule;
            $shift = $schedule->shift;

            $week = false;

            if ($weekNumber == 1) {
                $week = $shift->weekend1 == date('l', strtotime($date));
            } else if ($weekNumber == 2) {
                $week = $shift->weekend2 == date('l', strtotime($date));
            }


            if ($schedule->shift_id && $week) {
                $records[] = [
                    "company_id" => $company_id,
                    "date" => $date,
                    "status" => "O",
                    "employee_id" => $missingEmployee->system_user_id,
                    "shift_id" => $schedule->shift_id,
                    "shift_type_id" => $schedule->shift_type_id,
                    "updated_func" => "renderOffByDayScript",
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ];
            }
        }

        $UserIds = array_column($records, "employee_id");
        // return $records;
        try {
            if (count($records)) {
                $model = Attendance::query();
                $model->where("company_id", $company_id);
                $model->where("date", $date);
                $model->whereIn("employee_id", $UserIds);
                $model->delete();
                $model->insert($records);
            }
            return $UserIds;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
