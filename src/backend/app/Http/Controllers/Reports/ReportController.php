<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use NumberFormatter;


class ReportController extends Controller
{
    public function index(Request $request)
    {
        //    return $request->all();
        $model = (new Attendance)->processAttendanceModel($request);



        if ($request->shift_type_id == 1) {
            return $this->general($model, $request->per_page ?? 1000);
        }

        return $this->multiInOut($model->get(), $request->per_page ?? 1000);
    }

    public function fetchDataOLD(Request $request)
    {
        //    return $request->all();
        $model = (new Attendance)->processAttendanceModel($request);

        if ($request->shift_type_id == 1) {
            return $this->general($model, $request->per_page ?? 1000);
        }

        return $this->multiInOut($model->get(), $request->per_page ?? 1000);
    }

    public function fetchDataNEW(Request $request)
    {
        $perPage = $request->per_page ?? 100;

        $model = (new Attendance)->processAttendanceModel($request);

        $data = $model->paginate($perPage);

        $showTabs = json_decode($request->showTabs, true);

        // only for multi in/out
        if ($showTabs['multi'] == true || $showTabs['dual'] == true) {
            foreach ($data as $value) {

                $logs = $value->logs ?? [];

                $logs = array_pad($logs, 7, [
                    "in" => "---",
                    "out" => "---",
                    "device_in" => "---",
                    "device_out" => "---",
                ]);

                // Populate log details for each entry
                foreach ($logs as $index => $log) {
                    $value["in" . ($index + 1)] = $log["in"];
                    $value["out" . ($index + 1)] = $log["out"];
                    $value["device_in" . ($index + 1)] = $log["device_in"];
                    $value["device_out" . ($index + 1)] = $log["device_out"];
                }
            }
        }

        return $data;
    }

    public function general($model, $per_page = 100)
    {
        return $model->paginate($per_page);
    }

    public function multiInOut($model, $per_page = 100)
    {
        foreach ($model as $value) {
            $logs = $value->logs ?? [];
            $count = count($logs);
            $requiredLogs = max($count, 7); // Ensure at least 8 logs

            for ($a = 0; $a < $requiredLogs; $a++) {
                $log = $logs[$a] ?? [];
                $value["in" . ($a + 1)] = $log["in"] ?? "---";
                $value["out" . ($a + 1)] = $log["out"] ?? "---";
                $value["device_" . "in" . ($a + 1)]   = $log["device_in"] ?? "---";
                $value["device_" . "out" . ($a + 1)]  = $log["device_out"] ?? "---";
            }
        }

        return $this->paginate($model, $per_page);
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        $perPage == 0 ? 50 : $perPage;

        $resultArray = [];

        foreach ($items->forPage($page, $perPage) as $object) {
            $resultArray[] =   $object;
        }

        return new LengthAwarePaginator($resultArray, $items->count(), $perPage, $page, $options);
        //return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function general_download_csv(Request $request)
    {
        $data = (new Attendance)->processAttendanceModel($request)->get();

        $fileName = 'report.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            $i = 0;

            fputcsv($file, ["#", "Date", "E.ID", "Name", "Dept", "Shift Type", "Shift", "Status", "In", "Out", "Total Hrs", "OT", "Late coming", "Early Going", "D.In", "D.Out"]);
            foreach ($data as $col) {
                fputcsv($file, [
                    ++$i,
                    $col['date'],
                    $col['employee_id'] ?? "---",
                    $col['employee']["display_name"] ?? "---",
                    $col['employee']["department"]["name"] ?? "---",
                    $col["shift_type"]["name"] ?? "---",
                    $col["shift"]["name"] ?? "---",
                    $col["status"] ?? "---",
                    $col["in"] ?? "---",
                    $col["out"] ?? "---",
                    $col["total_hrs"] ?? "---",
                    $col["ot"] ?? "---",
                    $col["late_coming"] ?? "---",
                    $col["early_going"] ?? "---",
                    $col["device_in"]["short_name"] ?? "---",
                    $col["device_out"]["short_name"] ?? "---"
                ], ",");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function multi_in_out_daily_download_csv(Request $request)
    {
        $data = (new Attendance)->processAttendanceModel($request)->get();

        foreach ($data as $value) {
            $count = count($value->logs ?? []);
            if ($count > 0) {
                if ($count < 8) {
                    $diff = 7 - $count;
                    $count = $count + $diff;
                }
                $i = 1;
                for ($a = 0; $a < $count; $a++) {

                    $holder = $a;
                    $holder_key = ++$holder;

                    $value["in" . $holder_key] = $value->logs[$a]["in"] ?? "---";
                    $value["out" . $holder_key] = $value->logs[$a]["out"] ?? "---";
                }
            }
        }

        $fileName = 'report.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            $i = 0;
            fputcsv($file, [
                "#",
                "Date",
                "E.ID",
                "Name",
                "In1",
                "Out1",
                "In2",
                "Out2",
                "In3",
                "Out3",
                "In4",
                "Out4",
                "In5",
                "Out5",
                "In6",
                "Out6",
                "In7",
                "Out7",
                "Total Hrs",
                "OT",
                "Status",

            ]);
            foreach ($data as $col) {
                fputcsv($file, [
                    ++$i,
                    $col['date'],
                    $col['employee_id'] ?? "---",
                    $col['employee']["display_name"] ?? "---",
                    $col["in1"] ?? "---",
                    $col["out1"] ?? "---",
                    $col["in2"] ?? "---",
                    $col["out2"] ?? "---",
                    $col["in3"] ?? "---",
                    $col["out3"] ?? "---",
                    $col["in4"] ?? "---",
                    $col["out4"] ?? "---",
                    $col["in5"] ?? "---",
                    $col["out5"] ?? "---",
                    $col["in6"] ?? "---",
                    $col["out6"] ?? "---",
                    $col["in7"] ?? "---",
                    $col["out7"] ?? "---",
                    $col["total_hrs"] ?? "---",
                    $col["ot"] ?? "---",
                    $col["status"] ?? "---",

                ], ",");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function performanceReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $branch_id = $request->input('branch_id', 0);


        $fromDate = $request->input('from_date', date("Y-m-d"));
        $toDate = $request->input('to_date', date("Y-m-d"));


        $department_ids = $request->department_ids;

        if (gettype($department_ids) !== "array") {
            $department_ids = explode(",", $department_ids);
        }

        $employeeIds = [];

        if (!empty($request->employee_id)) {
            $employeeIds = is_array($request->employee_id) ? $request->employee_id : explode(",", $request->employee_id);
        }

        $model = Attendance::where('company_id', $companyId)
            ->when($branch_id, function ($q) use ($branch_id) {
                $q->whereHas('employee', fn($query) => $query->where('branch_id', $branch_id));
            })

            ->when(count($department_ids), function ($q) use ($department_ids) {
                $q->whereHas('employee', fn($query) => $query->whereIn('department_id',   $department_ids));
            })
            ->when(count($employeeIds), function ($q) use ($employeeIds) {
                $q->whereIn('employee_id', $employeeIds);
            })

            ->whereBetween('date', [$fromDate, $toDate]);

        $model->select(
            'employee_id',
            $this->getStatusCountWithSuffix('P'),
            $this->getStatusCountWithSuffix('LC'),
            $this->getStatusCountWithSuffix('EG'),
            $this->getStatusCountWithSuffix('A'),
            $this->getStatusCountWithSuffix('M'),
            $this->getStatusCountWithSuffix('O'),
            $this->getStatusCountWithSuffix('L'),
            $this->getStatusCountWithSuffix('V'),
            $this->getStatusCountWithSuffix('H'),
        );

        $model->whereHas("employee", fn($q) => $q->where("company_id", request("company_id")));

        $model->with(["employee" => function ($q) {
            $q->where("company_id", request("company_id"));
            $q->withOut("schedule", "user");
            $q->with("reporting_manager:id,reporting_manager_id,first_name");
            $q->with(["schedule_all" => function ($q) {
                $q->where("company_id", request("company_id"));
                $q->select([
                    "id",
                    "shift_id",
                    "employee_id",
                    "shift_type_id",
                    "company_id",
                ]);
                $q->with(["shift" => function ($q) {
                    $q->select([
                        "id",
                        "working_hours",
                        "days",
                    ]);
                }]);
            }]);
            $q->select(
                "first_name",
                "last_name",
                "profile_picture",
                "phone_number",
                "whatsapp_number",
                "employee_id",
                "joining_date",
                "designation_id",
                "department_id",
                "user_id",
                "sub_department_id",
                "overtime",
                "title",
                "status",
                "company_id",
                "branch_id",
                "system_user_id",
                "display_name",
                "full_name",
                "home_country",
                "reporting_manager_id",
                "local_email",
                "home_email",
                "leave_group_id"
            );
        }])
            ->groupBy('employee_id');


        return $model->paginate($request->per_page ?? 100);
    }

    public function summaryReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $branch_id = $request->input('branch_id', 0);


        $fromDate = $request->input('from_date', date("Y-m-d"));
        $toDate = $request->input('to_date', date("Y-m-d"));


        $department_ids = $request->department_ids;

        if (gettype($department_ids) !== "array") {
            $department_ids = explode(",", $department_ids);
        }

        $employeeIds = [];

        if (!empty($request->employee_id)) {
            $employeeIds = is_array($request->employee_id) ? $request->employee_id : explode(",", $request->employee_id);
        }

        $model = Attendance::where('company_id', $companyId)
            ->when($branch_id, function ($q) use ($branch_id) {
                $q->whereHas('employee', fn($query) => $query->where('branch_id', $branch_id));
            })

            ->when(count($department_ids), function ($q) use ($department_ids) {
                $q->whereHas('employee', fn($query) => $query->whereIn('department_id',   $department_ids));
            })
            ->when(count($employeeIds), function ($q) use ($employeeIds) {
                $q->whereIn('employee_id', $employeeIds);
            })

            ->whereBetween('date', [$fromDate, $toDate]);

        $model->select(
            'employee_id',
            $this->getTotalHours(),
            $this->getInHours(),
            $this->getOutHours(),
            $this->getStatusCountWithSuffix('P'),
            $this->getStatusCountWithSuffix('LC'),
            $this->getStatusCountWithSuffix('EG'),
            $this->getStatusCountWithSuffix('A'),
            $this->getStatusCountWithSuffix('M'),
            $this->getStatusCountWithSuffix('O'),
            $this->getStatusCountWithSuffix('L'),
            $this->getStatusCountWithSuffix('V'),
            $this->getStatusCountWithSuffix('H'),
        );

        $model->whereHas("employee", fn($q) => $q->where("company_id", request("company_id")));

        $model->with(["employee" => function ($q) {
            $q->where("company_id", request("company_id"));
            $q->withOut("schedule", "user");
            $q->with("reporting_manager:id,reporting_manager_id,first_name");
            $q->with(["schedule_all" => function ($q) {
                $q->where("company_id", request("company_id"));
                $q->select([
                    "id",
                    "shift_id",
                    "employee_id",
                    "shift_type_id",
                    "company_id",
                ]);
                $q->with(["shift" => function ($q) {
                    $q->select([
                        "id",
                        "working_hours",
                        "days",
                    ]);
                }]);
            }]);
            $q->select(
                "first_name",
                "last_name",
                "profile_picture",
                "phone_number",
                "whatsapp_number",
                "employee_id",
                "joining_date",
                "designation_id",
                "department_id",
                "user_id",
                "sub_department_id",
                "overtime",
                "title",
                "status",
                "company_id",
                "branch_id",
                "system_user_id",
                "display_name",
                "full_name",
                "home_country",
                "reporting_manager_id",
                "local_email",
                "home_email",
                "leave_group_id"
            );
        }])
            ->groupBy('employee_id');


        return $model->paginate($request->per_page ?? 100);
    }

    public function summaryReportDownload(Request $request)
    {



        $companyId = $request->input('company_id', 0);
        $branch_id = $request->input('branch_id', 0);


        $fromDate = $request->input('from_date', date("Y-m-d"));
        $toDate = $request->input('to_date', date("Y-m-d"));


        $department_ids = $request->department_ids;

        if (gettype($department_ids) !== "array") {
            $department_ids = explode(",", $department_ids);
        }

        $employeeIds = [];

        if (!empty($request->employee_id)) {
            $employeeIds = is_array($request->employee_id) ? $request->employee_id : explode(",", $request->employee_id);
        }

        $cacheKey = 'attendance_summary_' . md5(json_encode([
            'company_id' => $companyId,
            'branch_id' => $branch_id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'department_ids' => $department_ids,
            'employee_ids' => $employeeIds,
        ]));

        Cache::forget($cacheKey);

        $model = Attendance::where('company_id', $companyId)
            ->when($branch_id, function ($q) use ($branch_id) {
                $q->whereHas('employee', fn($query) => $query->where('branch_id', $branch_id));
            })

            ->when(count($department_ids), function ($q) use ($department_ids) {
                $q->whereHas('employee', fn($query) => $query->whereIn('department_id',   $department_ids));
            })
            ->when(count($employeeIds), function ($q) use ($employeeIds) {
                $q->whereIn('employee_id', $employeeIds);
            })

            ->whereBetween('date', [$fromDate, $toDate]);

        $model->select(
            'employee_id',
            $this->getTotalHours(),
            $this->getInHours(),
            $this->getOutHours(),
            $this->getStatusCountWithSuffix('P'),
            $this->getStatusCountWithSuffix('LC'),
            $this->getStatusCountWithSuffix('EG'),
            $this->getStatusCountWithSuffix('A'),
            $this->getStatusCountWithSuffix('M'),
            $this->getStatusCountWithSuffix('O'),
            $this->getStatusCountWithSuffix('L'),
            $this->getStatusCountWithSuffix('V'),
            $this->getStatusCountWithSuffix('H'),
        );

        $model->whereHas("employee_report_only", fn($q) => $q->where("company_id", request("company_id")));

        $model->with(["employee_report_only" => function ($q) {
            $q->where("company_id", request("company_id"));
            $q->withOut("schedule", "user");
            $q->with("reporting_manager:id,reporting_manager_id,first_name");
            $q->with(["schedule_all" => function ($q) {
                $q->where("company_id", request("company_id"));
                $q->select([
                    "id",
                    "shift_id",
                    "employee_id",
                    "shift_type_id",
                    "company_id",
                ]);
                $q->with(["shift" => function ($q) {
                    $q->select([
                        "id",
                        "working_hours",
                        "days",
                    ]);
                }]);
            }]);
            $q->select(
                "first_name",
                "last_name",
                "profile_picture",
                "phone_number",
                "whatsapp_number",
                "employee_id",
                "joining_date",
                "designation_id",
                "department_id",
                "user_id",
                "sub_department_id",
                "overtime",
                "title",
                "status",
                "company_id",
                "branch_id",
                "system_user_id",
                "display_name",
                "full_name",
                "home_country",
                "reporting_manager_id",
                "local_email",
                "home_email",
                "leave_group_id"
            );
        }])
            ->groupBy('employee_id');


        return $model->paginate(10);
    }

    function getStatusCountWithSuffix($dbDtatus)
    {
        $status = strtolower($dbDtatus);

        return DB::raw("COUNT(CASE WHEN status = '{$dbDtatus}' THEN 1 END) AS {$status}_count");
    }

    function getTotalHours()
    {

        $driver = DB::connection()->getDriverName(); // Get the database driver name

        if ($driver === 'sqlite') {
            return DB::raw("json_group_array(total_hrs) FILTER (WHERE total_hrs != '---') AS total_hrs_array");
        } else {
            return DB::raw("json_agg(\"total_hrs\"::TEXT) FILTER (WHERE \"total_hrs\" != '---') AS total_hrs_array");
        }
    }
    function getInHours()
    {
        $driver = DB::connection()->getDriverName(); // Get the database driver name
        if ($driver === 'sqlite') {
            return DB::raw("json_group_array(\"in\") FILTER (WHERE \"in\" != '---') AS average_in_time_array");
        } else {
            return  DB::raw("json_agg(\"in\"::TEXT) FILTER (WHERE \"in\" != '---') AS average_in_time_array");
        }
    }
    function getOutHours()
    {
        $driver = DB::connection()->getDriverName(); // Get the database driver name
        if ($driver === 'sqlite') {
            return DB::raw("json_group_array(\"out\") FILTER (WHERE \"out\" != '---') AS average_out_time_array");
        } else {
            return DB::raw("json_agg(\"out\"::TEXT) FILTER (WHERE \"out\" != '---') AS average_out_time_array");
        }
    }



    function getStatusCountValue($status)
    {
        return DB::raw("COUNT(CASE WHEN status = '$status' THEN 1 END) AS {$status}_count_value");
    }

    public function lastSixMonthsPerformanceReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $employeeId = $request->input('employee_id', 0);

        $startMonth = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();  // Removes time
        $endMonth = Carbon::now()->endOfMonth()->toDateString();  // Removes time
        // $endMonth = Carbon::now()->toDateString();  // Removes time


        $startMonthOnly = Carbon::now()->subMonths(5)->startOfMonth();
        $endMonthOnly = Carbon::now()->endOfMonth();

        $months = [];
        for ($month = $startMonthOnly; $month <= $endMonthOnly; $startMonthOnly->addMonth()) {
            $months[] = [
                'year' => $month->year,
                'month' => $month->month,
            ];
        }

        // Now, use these dates in your query
        $driver = DB::connection()->getDriverName(); // Get the database driver

        if ($driver === 'sqlite') {
            // SQLite uses strftime() for extracting Year and Month
            $query = DB::table('attendances')
                ->select(
                    DB::raw("strftime('%Y', date) AS year"),
                    DB::raw("strftime('%m', date) AS month"),
                    DB::raw("SUM(CASE WHEN status in ('P','LC','EG') THEN 1 ELSE 0 END) AS present_count"),
                    DB::raw("SUM(CASE WHEN status in ('A','M') THEN 1 ELSE 0 END) AS absent_count"),
                    DB::raw("SUM(CASE WHEN status in ('O') THEN 1 ELSE 0 END) AS week_off_count"),
                    DB::raw("SUM(CASE WHEN status in ('L','V','H',) THEN 1 ELSE 0 END) AS other_count")
                )
                ->where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$startMonth, $endMonth]) // Date-only comparison
                ->groupBy(DB::raw("strftime('%Y', date)"), DB::raw("strftime('%m', date)"))
                ->orderBy(DB::raw("strftime('%Y', date)"), 'desc')
                ->orderBy(DB::raw("strftime('%m', date)"), 'desc')
                ->get();
        } else {
            // Now, use these dates in your query
            $query = DB::table('attendances')
                ->select(
                    DB::raw('EXTRACT(YEAR FROM date) AS year'),
                    DB::raw('EXTRACT(MONTH FROM date) AS month'),
                    DB::raw('SUM(CASE WHEN status IN (\'P\',\'LC\',\'EG\') THEN 1 ELSE 0 END) AS present_count'),
                    DB::raw('SUM(CASE WHEN status IN (\'A\',\'M\') THEN 1 ELSE 0 END) AS absent_count'),
                    DB::raw('SUM(CASE WHEN status IN (\'O\') THEN 1 ELSE 0 END) AS week_off_count'),
                    DB::raw('SUM(CASE WHEN status IN (\'L\',\'V\',\'H\') THEN 1 ELSE 0 END) AS other_count')
                )
                ->where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$startMonth, $endMonth])
                ->groupBy(DB::raw('EXTRACT(YEAR FROM date)'), DB::raw('EXTRACT(MONTH FROM date)'))
                ->orderBy(DB::raw('EXTRACT(YEAR FROM date)'), 'desc')
                ->orderBy(DB::raw('EXTRACT(MONTH FROM date)'), 'desc')
                ->get();
        }


        $queryResults = [];

        // Get today's date
        $today = new DateTime();
        // Get the last day of the current month
        $endOfMonth = new DateTime('last day of this month');

        // Calculate remaining days (excluding today)
        $remainingDays = (int)$today->diff($endOfMonth)->format('%a');

        foreach ($months as $month) {
            $found = false;

            $monthFormatted = str_pad($month['month'], 2, '0', STR_PAD_LEFT);
            $month_year = date("M Y", strtotime("{$month['year']}-{$monthFormatted}-01"));

            foreach ($query as $result) {
                if ($result->year == $month['year'] && $result->month == $month['month']) {
                    $found = true;
                    $isCurrentMonth = ($month['year'] == date('Y') && $month['month'] == date('n'));
                    $queryResults[] = (object) [
                        'year' => $month['year'],
                        'month' => $month['month'],
                        'present_count' => $result->present_count,
                        'absent_count' => $isCurrentMonth ? ($result->absent_count - $remainingDays) : $result->absent_count,
                        'week_off_count' => $result->week_off_count,
                        'other_count' => $result->other_count,
                        'month_year' => date("M y", strtotime($month_year))
                    ];
                    break;
                }
            }

            if (!$found) {
                // If the month was not found in the results, add it with counts as 0
                $queryResults[] = (object) [
                    'year' => $month['year'],
                    'month' => $month['month'],
                    'present_count' => 0,
                    'absent_count' => 31,
                    'week_off_count' => 0,
                    'other_count' => 0,
                    'month_year' => date("M y", strtotime($month_year))
                ];
            }
        }

        return response()->json($queryResults);
    }

    public function lastSixMonthsSalaryReport(Request $request)
    {
        $startMonthOnly = Carbon::now()->subMonths(6)->startOfMonth();
        $endMonthOnly = Carbon::now()->subMonths(1)->endOfMonth();

        $result = [];

        for ($month_year = $startMonthOnly; $month_year <= $endMonthOnly; $startMonthOnly->addMonth()) {
            $year = $month_year->year;
            $month = str_pad($month_year->month, 2, "0", STR_PAD_LEFT);
            $result[] = $this->getRenderedSalary($request->company_id, $request->employee_id, $month, $year);
        }

        return array_reverse($result);
    }

    public function previousMonthSalaryReport(Request $request)
    {
        // Get the first day of the previous month
        $previousMonth = date("m", strtotime("first day of previous month"));
        $year = date("Y", strtotime("first day of previous month"));

        // Ensure the month is two digits (e.g., "01" for January)
        $month = str_pad($previousMonth, 2, "0", STR_PAD_LEFT);

        // Call the method to get the rendered salary report
        $response = $this->getRenderedSalary($request->company_id, $request->employee_id, $month, $year);

        return $response;
    }

    public function currentMonthHoursReport(Request $request)
    {
        // Validate input
        $request->validate([
            'company_id' => 'required|integer|min:1',
            'employee_id' => 'required|integer|min:1',
        ]);

        $companyId = $request->input('company_id');
        $employeeId = $request->input('employee_id');
        $previousMonth = date('m', strtotime('last month'));

        try {
            // Base query for the current month, company, and employee
            $baseQuery = DB::table('attendances')
                ->where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->whereMonth('date', $previousMonth);

            // Fetch total performed hours
            $totalHours = (clone $baseQuery)->where('status', 'P')->pluck('total_hrs')->toArray();
            $totalPerformedHours = $this->sumTimeValues($totalHours);

            // Fetch late coming hours
            $totalLateComings = (clone $baseQuery)->where('late_coming', '!=', '---')->pluck('late_coming')->toArray();
            $lateComingHours = $this->sumTimeValues($totalLateComings);

            // Fetch early going hours
            $totalEarlyGoings = (clone $baseQuery)->where('early_going', '!=', '---')->pluck('early_going')->toArray();
            $earlyGoingHours = $this->sumTimeValues($totalEarlyGoings);

            // Fetch overtime hours

            $totalOtHours = (clone $baseQuery)->where('ot', '!=', '---')->pluck('ot')->toArray();
            $otHours = $this->sumTimeValues($totalOtHours);

            return response()->json([
                'total_performed' => [
                    "hours" => $this->formatMinutesToTime($totalPerformedHours),
                    "days" => count($totalHours)
                ],
                'late_coming' => [
                    "hours" => $this->formatMinutesToTime($lateComingHours),
                    "days" => count($totalLateComings)
                ],
                'early_going' => [
                    "hours" => $this->formatMinutesToTime($earlyGoingHours),
                    "days" => count($totalEarlyGoings)
                ],
                'overtime' => [
                    "hours" => $this->formatMinutesToTime($otHours),
                    "days" => count($totalOtHours)
                ]
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([], 500);
        }
    }

    public function lastSixMonthsHoursReport(Request $request)
    {
        // Validate input
        $request->validate([
            'company_id' => 'required|integer|min:1',
            'employee_id' => 'required|integer|min:1',
        ]);

        $companyId = $request->input('company_id');
        $employeeId = $request->input('employee_id');

        try {
            $report = [];

            for ($i = 0; $i < 6; $i++) {
                $month = date('m', strtotime("-$i month"));
                $year = date('Y', strtotime("-$i month"));

                // Base query for the given month
                $baseQuery = DB::table('attendances')
                    ->where('company_id', $companyId)
                    ->where('employee_id', $employeeId)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year);

                // Fetch and sum hours
                $totalHours = (clone $baseQuery)->where('status', 'P')->pluck('total_hrs')->toArray();
                $totalPerformedHours = $this->sumTimeValues($totalHours);

                $totalLateComings = (clone $baseQuery)->where('late_coming', '!=', '---')->pluck('late_coming')->toArray();
                $lateComingHours = $this->sumTimeValues($totalLateComings);

                $totalEarlyGoings = (clone $baseQuery)->where('early_going', '!=', '---')->pluck('early_going')->toArray();
                $earlyGoingHours = $this->sumTimeValues($totalEarlyGoings);

                $totalOtHours = (clone $baseQuery)->where('ot', '!=', '---')->pluck('ot')->toArray();
                $otHours = $this->sumTimeValues($totalOtHours);

                $monthLabel = date('M Y', strtotime("-$i month"));

                $report[$monthLabel] = [
                    'total_performed' => [
                        "hours" => $this->formatMinutesToTime($totalPerformedHours),
                        "days" => count($totalHours)
                    ],
                    'late_coming' => [
                        "hours" => $this->formatMinutesToTime($lateComingHours),
                        "days" => count($totalLateComings)
                    ],
                    'early_going' => [
                        "hours" => $this->formatMinutesToTime($earlyGoingHours),
                        "days" => count($totalEarlyGoings)
                    ],
                    'overtime' => [
                        "hours" => $this->formatMinutesToTime($otHours),
                        "days" => count($totalOtHours)
                    ]
                ];
            }

            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function previousMonthPerformanceReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $employeeId = $request->input('employee_id', 0);
        $lastMonth = $request->input('date', date('m', strtotime('first day of last month')));

        $result = Attendance::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereMonth('date', $lastMonth)
            ->select('date', 'status')
            ->orderBy('date')
            ->groupBy('date', 'status')
            ->get();

        $arr = [];
        $stats = [
            "P" => 0,
            "A" => 0,
            "O" => 0,
            "OTHERS_COUNT" => 0,
        ];

        foreach ($result as $item) {
            $dateKey = date("Y-m-d", strtotime($item->date));

            if (in_array($item->status, ['P', "LC", "EG"])) {
                $arr[$dateKey] = "green";
                $stats["P"] += 1;
            } else if (in_array($item->status, ['A', "M"])) {
                $arr[$dateKey] = "red";
                $stats["A"] += 1;
            } else if (in_array($item->status, ['O'])) {
                $arr[$dateKey] = "primary";
                $stats["O"] += 1;
            } else {
                $arr[$dateKey] = "orange";
                $stats["OTHERS_COUNT"] += 1;
            }
        }

        return ["events" => $arr, "stats" => $stats];
    }

    public function currentMonthPerformanceReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $employeeId = $request->input('employee_id', 0);
        $currentMonth = $request->input('date', date('m'));

        $result = Attendance::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereMonth('date', $currentMonth)
            ->select('date', 'status')
            ->orderBy('date')
            ->groupBy('date', 'status')
            ->get();

        $arr = [];
        $stats = [
            "P" => 0,
            "A" => 0,
            "O" => 0,
            "L" => 0,
            "OTHERS_COUNT" => 0,
        ];

        foreach ($result as $item) {
            $itemDate = date("Y-m-d", strtotime($item->date));

            if (in_array($item->status, ['P', "LC", "EG"])) {
                $arr[$itemDate] = "green";
                $stats["P"] += 1;
            } else if (in_array($item->status, ['A', "M"])) {
                $arr[$itemDate] = "";
                if ($itemDate <= date("Y-m-d")) {
                    $stats["A"] += 1;
                    $arr[$itemDate] = "red";
                }
            } else if (in_array($item->status, ['L'])) {
                $arr[$itemDate] = "";
                if ($itemDate <= date("Y-m-d")) {
                    $stats["L"] += 1;
                    $arr[$itemDate] = "orange";
                }
            } else if (in_array($item->status, ['O'])) {
                $arr[$itemDate] = "primary";
                $stats["O"] += 1;
            } else {
                $arr[$itemDate] = "orange";
                $stats["OTHERS_COUNT"] += 1;
            }
        }

        return ["events" => $arr, "stats" => $stats];
    }


    /**
     * Helper function to sum time values in "HH:MM" format.
     *
     * @param array $timeValues Array of time strings in "HH:MM" format.
     * @return int Total time in minutes.
     */
    private function sumTimeValues(array $timeValues): int
    {
        $totalMinutes = 0;

        foreach ($timeValues as $time) {
            list($hours, $minutes) = explode(':', $time);
            $totalMinutes += ($hours * 60) + $minutes;
        }

        return $totalMinutes;
    }

    /**
     * Helper function to convert total minutes back to "HH:MM" format.
     *
     * @param int $minutes Total minutes.
     * @return string Time in "HH:MM" format.
     */
    private function formatMinutesToTime(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function calculateHoursAndMinutes(array $timeStrings): array
    {
        $totalMinutes = array_reduce($timeStrings, function ($carry, $time) {
            // Ensure the time is in the correct format
            if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                list($hours, $minutes) = explode(':', $time);
                return $carry + ($hours * 60) + $minutes; // Convert to total minutes
            }

            throw new \InvalidArgumentException("Invalid time format: {$time}. Expected 'hh:mm'.");
        }, 0);

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return [
            "hours" => $hours,
            "minutes" => $minutes,
            "hm" => sprintf("%02d:%02d", $hours, $minutes), // Format as 'hh:mm'
        ];
    }

    function getRenderedSalary($company_id, $id, $month, $year)
    {
        // Fetch the last six months' payroll data
        $Payroll = Payroll::where("employee_id", $id)
            ->where("company_id", $company_id)
            ->with(["company", "payroll_formula"])
            ->with(["employee" => function ($q) {
                $q->withOut(["user", "schedule"]);
            }])
            ->first(["basic_salary", "net_salary", "earnings", "employee_id", "company_id", "created_at"]);

        if (!$Payroll) {
            return response()->json(["message" => "No Data Found"], 404);
        }

        $days_countdate = DateTime::createFromFormat('Y-m-d', $Payroll->created_at->format('Y-m-d'));

        $Payroll->total_month_days = $days_countdate->format('t');

        $salary_type = $Payroll->payroll_formula->salary_type ?? "basic_salary";

        $Payroll->salary_type = ucwords(str_replace("_", " ", $salary_type));

        $Payroll->date = $Payroll->created_at->format('j F Y');

        $Payroll->SELECTEDSALARY = $salary_type == "basic_salary" ? $Payroll->basic_salary : $Payroll->net_salary;
        $Payroll->perDaySalary = number_format($Payroll->SELECTEDSALARY / $Payroll->total_month_days, 2);
        $Payroll->perHourSalary = number_format($Payroll->perDaySalary / 8, 2);

        $conditions = [
            "company_id" => $company_id,
            "employee_id" => $Payroll->employee->system_user_id
        ];

        $allStatuses = ['P', 'A', 'M', 'O', 'LC', 'EG'];

        $attendances = Attendance::where($conditions)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', $allStatuses)
            ->orderBy("date", "asc")
            ->get();

        $otherCalculations = $attendances;

        $lateHours = $otherCalculations->filter(function ($attendance) {
            return $attendance->late_coming !== '---';
        })->pluck('late_coming')->toArray();

        $earlyHours = $otherCalculations->filter(function ($attendance) {
            return $attendance->early_going !== '---';
        })->pluck('early_going')->toArray();

        $otHours = $otherCalculations->filter(function ($attendance) {
            return $attendance->ot !== '---';
        })->pluck('ot')->toArray();

        $Payroll->lateHours = $this->calculateHoursAndMinutes($lateHours);
        $Payroll->earlyHours = $this->calculateHoursAndMinutes($earlyHours);
        $Payroll->otHours = $this->calculateHoursAndMinutes($otHours);
        $shortHours = array_merge($lateHours, $earlyHours);
        $Payroll->combimedShortHours = $this->calculateHoursAndMinutes($shortHours);
        $totalHours = $Payroll->combimedShortHours["hours"] ?? 0;
        $remainingMinutes = $Payroll->combimedShortHours["minutes"] ?? "00:00";
        $decimalHours = $totalHours + ($remainingMinutes / 60);
        $rate = $Payroll->perHourSalary;

        $shortHours = 0; // Set a default value or handle it accordingly

        if ($Payroll->payroll_formula && isset($Payroll->payroll_formula->deduction_value)) {
            $shortHours = $decimalHours * $rate * $Payroll->payroll_formula->deduction_value;
        }


        $grouByStatus = $attendances
            ->groupBy('status')
            ->map(fn($group) => $group->count())
            ->toArray();

        $attendanceCounts = array_merge(array_fill_keys($allStatuses, 0), $grouByStatus);

        $Payroll->present = array_sum([
            $attendanceCounts["P"],
            $attendanceCounts["LC"],
            $attendanceCounts["EG"],
        ]);

        $Payroll->absent = array_sum([
            $attendanceCounts["A"],
            $attendanceCounts["M"]
        ]);

        $Payroll->week_off = $attendanceCounts["O"];

        $Payroll->deductedSalary = round($Payroll->absent * $Payroll->perDaySalary);

        $OTHours = $Payroll->otHours["hours"];

        $OTEarning = 0;

        if ($Payroll->payroll_formula && isset($Payroll->payroll_formula->ot_value)) {
            $OTEarning = $Payroll->perHourSalary * $OTHours * $Payroll->payroll_formula->ot_value;
        }


        $Payroll->earnings = array_merge(
            [
                [
                    "label" => "Basic",
                    "value" => (int) $Payroll->SELECTEDSALARY,
                ],
                [
                    "label" => "OT",
                    "value" => $OTEarning,
                ],
            ],
            $Payroll->earnings,
        );

        $Payroll->totalDeductions = ($Payroll->deductedSalary + $shortHours);

        $Payroll->salary_and_earnings = array_sum(array_column($Payroll->earnings, "value"));

        if ($Payroll->present == 0) {
            $Payroll->salary_and_earnings = 0;
            $Payroll->finalSalary = 0;
        }

        $Payroll->finalSalary = (($Payroll->salary_and_earnings) - $Payroll->totalDeductions);

        $Payroll->payslip_month_year = $days_countdate->format('F Y');

        return [
            "finalSalary" => number_format($Payroll->finalSalary < 0 ? 0 : $Payroll->finalSalary, 2),
            'year' => $year,
            'month' => DateTime::createFromFormat('!m', $month)->format('M'), // Convert month number to month name (e.g., "Jan")
            'salary_type' => $Payroll->salary_type,
            'salary_and_earnings' => number_format($Payroll->salary_and_earnings, 2),
            'ot' => number_format($OTEarning, 2),
            'total_deductions' => number_format($Payroll->totalDeductions, 2),

            'salary_and_earnings_value' => round($Payroll->salary_and_earnings),
            'ot_value' => round($OTEarning),
            'total_deductions_value' => round($Payroll->totalDeductions),
        ];
    }

    public function performanceReportShow(Request $request)
    {
        $companyId = $request->input('company_id', 0);

        $model = Attendance::where('company_id', $companyId)
            ->where('employee_id', request("system_user_id"))
            ->whereMonth('date', date("m"));

        // Get today's date
        $today = new DateTime();
        // Get the last day of the current month
        $endOfMonth = new DateTime('last day of this month');

        // Calculate remaining days (excluding today)
        $remainingDays = (int)$today->diff($endOfMonth)->format('%a');

        $model->select(
            DB::raw("COUNT(CASE WHEN status = 'P' THEN 1 END) AS p_count"),
            DB::raw("COUNT(CASE WHEN status = 'LC' THEN 1 END) AS lc_count"),
            DB::raw("COUNT(CASE WHEN status = 'EG' THEN 1 END) AS eg_count"),
            DB::raw("COUNT(CASE WHEN status = 'A' THEN 1 END) AS a_count"),
            DB::raw("COUNT(CASE WHEN status = 'M' THEN 1 END) AS m_count"),
            DB::raw("COUNT(CASE WHEN status = 'O' THEN 1 END) AS o_count"),
            DB::raw("COUNT(CASE WHEN status = 'L' THEN 1 END) AS l_count"),
            DB::raw("COUNT(CASE WHEN status = 'V' THEN 1 END) AS v_count"),
            DB::raw("COUNT(CASE WHEN status = 'H' THEN 1 END) AS h_count"),
        );

        $first = $model->first();

        $first->a_count = ($first->a_count + $first->m_count) -  $remainingDays;
        $first->other_count = $first->l_count + $first->v_count + $first->h_count + $first->lc_count + $first->eg_count;

        return $first;
    }
}
