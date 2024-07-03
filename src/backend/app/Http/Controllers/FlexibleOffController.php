<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class FlexibleOffController extends Controller
{
    public function renderFlexibleOffWeek1Cron($company_id = 0)
    {
        $UserIds = $this->renderFlexibleOffScript($company_id, date('Y-m-d'), 6);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->getMeta("Sync Flexible Off Week 1", "$result Employee has been marked as Off (Flexible)" . ".\n");
    }

    public function renderFlexibleOffWeek2Cron($company_id = 0)
    {
        $UserIds = $this->renderFlexibleOffScript($company_id, date('Y-m-d'), 13);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->getMeta("Sync Flexible Off Week 2", "$result Employee has been marked as Off (Flexible)" . ".\n");
    }

    public function renderFlexibleOffWeek1(Request $request)
    {
        $UserIds = $this->renderFlexibleOffScript($request->company_id, $request->date, 6, $request->UserID);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->response("$result Employee has been marked as Flexible Off", null, true);
    }

    public function renderFlexibleOffWeek2(Request $request)
    {
        $UserIds = $this->renderFlexibleOffScript($request->company_id, $request->date, 13, $request->UserID);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->response("$result Employee has been marked as Flexible Off", null, true);
    }

    public function renderFlexibleOffScript($company_id, $date, $backDays = 6, $user_id = 0)
    {
        $sixDaysAgo = date('Y-m-d', strtotime($date . ' -' . $backDays . ' days'));

        $model = Employee::query();
        $model->withOut(["department", "designation", "sub_department"]);
        $model->where("company_id", $company_id);

        $model->when($user_id, fn ($q) => $q->where("system_user_id", $user_id));
        $model->with(["schedule" =>  function ($q) use ($date) {
            $q->where('to_date', '>=', $date);
            $q->orderBy('to_date', 'asc');
            $q->whereHas("shift");
        }]);

        $model->withCount(["attendances" =>  function ($q) use ($company_id, $date, $sixDaysAgo) {
            $q->where('date', '>', $sixDaysAgo);
            $q->where('date', '<=', $date);
            $q->where('status', 'A');
            $q->where("company_id", $company_id);
        }]);


        $missingEmployees = $model->get(["employee_id", "system_user_id"]);

        $records = [];

        foreach ($missingEmployees as $missingEmployee) {

            $schedule = $missingEmployee->schedule;
            $shift = $schedule->shift;


            $week = false;


            if ($backDays == 6) {
                $week = $shift->weekend1 == "Flexi";
            } else if ($backDays == 13) {
                $week = $shift->weekend2 == "Flexi";
            }

            if ($missingEmployee->schedule->shift_id && $missingEmployee->attendances_count == 0 && $week) {
                $records[] = [
                    "company_id" => $company_id,
                    "date" => $date,
                    "status" => "O",
                    "employee_id" => $missingEmployee->system_user_id,
                    "shift_id" => $missingEmployee->schedule->shift_id,
                    "shift_type_id" => $missingEmployee->schedule->shift_type_id,
                    "updated_func" => "renderFlexibleOffScript",
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ];
            }
        }

        $UserIds = array_column($records, "employee_id");
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
