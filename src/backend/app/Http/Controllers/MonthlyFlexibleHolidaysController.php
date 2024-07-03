<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class MonthlyFlexibleHolidaysController extends Controller
{
    public function renderMonthlyFlexibleHolidaysCron($company_id = 0, $date)
    {
        $yesterdayDate = date("Y-m-d", strtotime($date) - 86400);
        $UserIds = $this->renderMonthlyFlexibleHolidaysScipt($company_id, $yesterdayDate);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->getMeta("Sync Monthly Flexible Holidays", "$result Employee has been marked as Off" . ".\n");
    }

    public function renderMonthlyFlexibleHolidays(Request $request)
    {
        $UserIds = $this->renderMonthlyFlexibleHolidaysScipt($request->company_id, $request->date, $request->UserID);
        $result = !count($UserIds) ? "0" : json_encode($UserIds);
        return $this->response("$result Employee has been marked as Off", null, true);
    }

    public function renderMonthlyFlexibleHolidaysScipt($company_id, $date, $user_id = 0)
    {

        return '';
        $model = Employee::query();

        $model->withOut(["department", "designation", "sub_department"]);

        $model->where("company_id", $company_id);

        $model->when($user_id, fn ($q) => $q->where("system_user_id", $user_id));

        $model->with(["schedule" =>  function ($q) {
            $q->orderBy('from_date', 'asc');
            $q->orderBy('to_date', 'asc');
            $q->whereHas("shift");
            $q->withOut(["shift_type"]);
            $q->select("shift_id", "shift_type_id", "employee_id", "from_date", "to_date");
            $q->with(["shift" => function ($q) {
                $q->select("id", "name", "monthly_flexi_holidays", "from_date", "to_date");
            }]);
        }]);

        $model->withCount(["attendances as off_count" =>  function ($q) use ($company_id) {
            $q->whereMonth("date", date("m"));
            $q->where("status", "O");
            $q->where("company_id", $company_id);
        }]);

        $missingEmployees = $model->get("employee_id", "system_user_id");

        $records = [];

        foreach ($missingEmployees as $missingEmployee) {

            $schedule = $missingEmployee->schedule;
            $monthly_flexi_holidays = $schedule->shift->monthly_flexi_holidays ?? 0;

            if ($schedule && $missingEmployee->off_count < $monthly_flexi_holidays) {
                $records[] = [
                    "company_id" => $company_id,
                    "date" => $date,
                    "status" => "O",
                    "employee_id" => $missingEmployee->system_user_id,
                    "shift_id" => $schedule->shift_id,
                    "shift_type_id" => $schedule->shift_type_id,
                    "updated_func" => "renderMonthlyFlexibleHolidaysScipt",
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
