<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Community\Member;
use App\Models\Community\Room;
use App\Models\Community\Tanent;
use App\Models\Department;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Theme;
use App\Models\VisitorLog;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return Theme::truncate();
        // return Theme::count();

        $id = $request->company_id;

        $counts = $this->getCounts($request->company_id ?? 8, $request);

        $jsonColumn = Theme::where("company_id", $id)
            ->where("page", $request->page)
            ->where("type", $request->type)
            ->value("style") ?? [];

        foreach ($jsonColumn as &$card) {
            $card["calculated_value"] = str_pad($counts[$card["value"]] ?? "", 2, '0', STR_PAD_LEFT);
        }
        return $jsonColumn;
    }
    public function dashboardCount(Request $request)
    {
        return $this->getCounts($request->company_id, $request);
    }
    public function getCounts($id = 0, $request): array
    {
        $model = Attendance::with("employee")->where('company_id', $id)

            ->when($request->filled("department_ids") && count($request->department_ids) > 0, function ($q) use ($request) {
                $q->with(["employee" =>  function ($q) use ($request) {
                    $q->whereHas('department', fn (Builder $query) => $query->whereIn('department_id', $request->department_ids));
                }]);
            })
            ->when($request->filled("branch_id"), function ($q) use ($request) {
                $q->whereHas("employee", fn ($q) => $q->where("branch_id", $request->branch_id));
            })
            ->whereIn('status', ['P', 'A', 'M', 'O', 'H', 'L', 'V'])
            ->whereDate('date', date("Y-m-d"))
            ->select('status')
            ->get();

        $attendanceCounts = AttendanceLog::with(["employee"])->where("company_id", $id)
            ->whereDate("LogTime", date("Y-m-d"))
            ->when($request->filled("branch_id"), function ($q) use ($request) {
                $q->whereHas("employee", fn ($q) => $q->where("branch_id", $request->branch_id));
            })
            ->groupBy("UserID")

            ->selectRaw('"UserID", COUNT(*) as count')
            ->get();

        $countsByParity = $attendanceCounts->groupBy(fn ($item) => $item->count % 2 === 0 ? 'even' : 'odd')->map->count();

        return [
            "employeeCount" => Employee::where("company_id", $id)
                ->when($request->filled("department_ids") && count($request->department_ids) > 0, function ($q) use ($request) {
                    $q->whereIn("department_id", $request->department_ids);
                })

                ->when($request->filled("branch_id"), function ($q) use ($request) {
                    $q->where("branch_id", $request->branch_id);
                })
                ->count() ?? 0,
            'totalIn' => $countsByParity->get('odd', 0),
            'totalOut' => $countsByParity->get('even', 0),
            "presentCount" => $model->where('status', 'P')->count(),
            "absentCount" => $model->where('status', 'A')->count(),
            "missingCount" => $model->where('status', 'M')->count(),
            "offCount" => $model->where('status', 'O')->count(),
            "holidayCount" => $model->where('status', 'H')->count(),
            "leaveCount" => $model->where('status', 'L')->count(),
            "vaccationCount" => $model->where('status', 'V')->count(),
        ];
    }
    public function dashboardGetCountDepartment(Request $request)
    {
        $model = Attendance::with(['employee:id,employee_id,status,system_user_id,department_id'])->where('company_id', $request->company_id)
            ->whereIn('status', ['P', 'A', 'M', 'O', 'H', 'L', 'V'])
            ->whereDate('date', date('Y-m-d'))
            ->when($request->filled("branch_id"), function ($q) use ($request) {
                $q->whereHas("employee", fn ($q) => $q->where("branch_id", $request->branch_id));
            })

            ->get();

        $departments = Department::where('company_id', $request->company_id)->orderBy("name", "asc")->get();

        $return = [];
        foreach ($departments as $department) {


            $return[$department->name] =   [

                "presentCount" => $model->where('status', 'P')->where('employee.department_id', $department->id)->count(),
                "absentCount" => $model->where('status', 'A')->where('employee.department_id', $department->id)->count(),
                "missingCount" => $model->where('status', 'M')->where('employee.department_id', $department->id)->count(),
                "offCount" => $model->where('status', 'O')->where('employee.department_id', $department->id)->count(),
                "holidayCount" => $model->where('status', 'H')->where('employee.department_id', $department->id)->count(),
                "leaveCount" => $model->where('status', 'L')->where('employee.department_id', $department->id)->count(),
                "vaccationCount" => $model->where('status', 'V')->where('employee.department_id', $department->id)->count(),
            ];
        }

        return  $return;
    }
    public function previousWeekAttendanceCount(Request $request, $id)
    {
        $dates = [];

        for ($i = 13; $i >= 7; $i--) {
            $date = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            $dates[] = $date;
        }

        $date = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
        $model = Attendance::with("employee")->where('company_id', $id)
            ->whereIn('status', ['P', 'A', 'M', 'O', 'H', 'L', 'V'])
            ->whereIn('date', $dates)
            ->when($request->filled("branch_id"), function ($q) use ($request) {
                $q->whereHas("employee", fn ($q) => $q->where("branch_id", $request->branch_id));
            })
            ->select('status')
            ->get();

        return [
            "date" => $date,
            "presentCount" => $model->where('status', 'P')->count(),
            "absentCount" => $model->where('status', 'A')->count(),
            "missingCount" => $model->where('status', 'M')->count(),
            "offCount" => $model->where('status', 'O')->count(),
            "holidayCount" => $model->where('status', 'H')->count(),
            "leaveCount" => $model->where('status', 'L')->count(),
            "vaccationCount" => $model->where('status', 'V')->count(),
        ];
    }
    public function dashboardGetCountslast7Days(Request $request)
    {




        $finalarray = [];
        $dateStrings = [];
        if ($request->has("date_from") && $request->has("date_to")) {
            // Usage example:
            $startDate = new DateTime($request->date_from); // Replace with your start date
            $endDate = new DateTime($request->date_to);   // Replace with your end date

            $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $dateStrings[] = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            }
        }


        foreach ($dateStrings as $key => $value) {

            $date = $value; //date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            $AttendanceLogModel = AttendanceLog::where('company_id', $request->company_id)
                ->whereDate("LogTime",  $date)->distinct("UserID");

            $EmployeesCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('employees');
                })
                ->when(request()->filled("device_id"), function ($query) use ($request) {
                    $query->where('DeviceID', $request->DeviceID);
                })
                ->distinct("UserID")
                ->get()->count();

            $VisitorsCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('visitors');
                })
                ->get()->count();


            $TenantsCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('tanents');
                })
                ->get()->count();

            $DeniedCount = $AttendanceLogModel->clone()
                ->where("status", "Access Denied")
                ->get()->count();

            $finalarray[] = [
                "date" => $date,
                "EmployeeCount" => $EmployeesCount,
                "VisitorCount" => $VisitorsCount,
                "TenantCount" =>  $TenantsCount,
                "DeniedCount" =>  $DeniedCount
            ];
        }


        return  $finalarray;
    }
    public function dashboardMaleFemaleCount(Request $request)
    {


        $kids_count = Member::where('company_id', $request->company_id)
            ->where('age', "<", 18)
            ->whereIn('tanent_id', function ($query) use ($request) {
                $query->select('tanent_id')
                    ->where('company_id', $request->company_id)

                    ->from('rooms');
            })->get()->count();


        $members = Member::where('company_id', $request->company_id)
            ->where('age', ">=", 18)
            ->whereIn('tanent_id', function ($query) use ($request) {
                $query->select('tanent_id')
                    ->where('company_id', $request->company_id)

                    ->from('rooms');
            });


        $male_count = $members->clone()->where("geneder", "Male")->get()->count();
        $female_count = $members->clone()->where("geneder", "FeMale")->get()->count();




        $finalarray  = [
            "male" => $male_count,
            "female" => $female_count,
            "kids" => $kids_count,
        ];

        return  $finalarray;
    }
    public function dashboardGetAssetsStatistics(Request $request)
    {
        $expiryDate = date("Y-m-d", strtotime("+30 days"));

        $contract_expiring_count = Tanent::where('company_id', $request->company_id)
            ->whereDate("end_date", "<=",  $expiryDate)
            ->where("checkout_date", null)
            ->get()->count();

        $flats_count = Room::where('company_id', $request->company_id)->get()->count();
        // $occupied_count = Tanent::where('company_id', $request->company_id)
        //     ->where("start_date", "<=", date('Y-m-d'))
        //     ->where("end_date", ">=", date('Y-m-d'))
        //     ->where("checkout_date", "!=", null)
        //     ->get()->count();

        $occupied_count =  Room::where('company_id', $request->company_id)->where("tenant_id", "!=", 0)->get()->count();


        $offline_devices = Device::where('company_id', $request->company_id)->where('status_id', 2)->get()->count();


        $finalarray  = [
            "flats_count" => $flats_count,
            "occupied_count" => $occupied_count,
            "car_parking_count" => 0,
            "allocated_count" => 0,
            "offline_devices" =>  $offline_devices,
            "contract_expiring_count" => $contract_expiring_count,


        ];

        return  $finalarray;
    }
    public function dashboardGetCountsTodayStatistics(Request $request)
    {




        $finalarray = [];
        $dateStrings = [];
        if ($request->has("date_from") && $request->has("date_to")) {
            // Usage example:
            $startDate = new DateTime($request->date_from); // Replace with your start date
            $endDate = new DateTime($request->date_to);   // Replace with your end date

            $dateStrings = $this->createDateRangeArray($startDate, $endDate);
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $dateStrings[] = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            }
        }


        foreach ($dateStrings as $key => $value) {

            $date = $value; //date('Y-m-d', strtotime(date('Y-m-d') . '-' . $i . ' days'));
            $AttendanceLogModel = AttendanceLog::where('company_id', $request->company_id)
                ->whereDate("LogTime",  $date)->distinct("UserID");

            $EmployeesCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('employees');
                })
                ->when(request()->filled("device_id"), function ($query) use ($request) {
                    $query->where('DeviceID', $request->DeviceID);
                })
                ->distinct("UserID")
                ->get()->count();

            $VisitorsCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('visitors');
                })
                ->get()->count();


            $TenantsCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('tanents');
                })
                ->get()->count();

            $DeniedCount = $AttendanceLogModel->clone()
                ->where("status", "Access Denied")
                ->get()->count();

            $finalarray  = [
                "date" => $date,
                "EmployeeCount" => $EmployeesCount,
                "VisitorCount" => $VisitorsCount,
                "TenantCount" =>  $TenantsCount,
                "DeniedCount" =>  $DeniedCount
            ];
        }


        return  $finalarray;
    }
    function createDateRangeArray($startDate, $endDate)
    {
        $dateStrings = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $dateStrings[] = $currentDate->format('Y-m-d'); // Change the format as needed
            $currentDate->modify('+1 day');
        }

        return $dateStrings;
    }




    public function dashboardGetCountsTodayHourInOut(Request $request)
    {

        $finalarray = [];

        for ($i = 0; $i < 24; $i++) {

            $j = $i;

            $j = $i <= 9 ? "0" . $i : $i;

            $date = $request->date_to; // date('Y-m-d', $request->date_to);

            $AttendanceLogModel = AttendanceLog::where('company_id', $request->company_id)
                ->where('LogTime', '>=', $date . ' ' . $j . ':00:00')
                ->where('LogTime', '<', $date  . ' ' . $j . ':59:59');

            $AttendanceLogModel->when(request()->filled("device_id"), function ($query) use ($request) {
                $query->where('DeviceID', $request->DeviceID);
            });

            $EmployeesCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('employees');
                })
                ->distinct("UserID")
                ->get()->count();

            $VisitorsCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('visitors');
                })
                ->get()->count();


            $TenantsCount = $AttendanceLogModel->clone()
                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('company_id', $request->company_id)
                        ->from('tanents');
                })
                ->get()->count();

            $DeniedCount = $AttendanceLogModel->clone()
                ->where("status", "Access Denied")
                ->get()->count();




            $finalarray[] = [
                "date" => $date,
                "hour" => $i,
                "EmployeeCount" => $EmployeesCount,
                "VisitorCount" => $VisitorsCount,
                "TenantCount" =>  $TenantsCount,
                "DeniedCount" =>  $DeniedCount

            ];
        }


        return  $finalarray;
    }

    public function dashboardGetVisitorCountsTodayHourInOut(Request $request)
    {

        $finalarray = [];

        for ($i = 0; $i < 24; $i++) {

            $j = $i;

            $j = $i <= 9 ? "0" . $i : $i;

            $date = date('Y-m-d'); //, strtotime(date('Y-m-d') . '-' . $i . ' days'));
            $model = AttendanceLog::with(["visitor"])->where('company_id', $request->company_id)

                ->whereIn('UserID', function ($query) use ($request) {
                    $query->select('system_user_id')
                        ->where('visit_from', "<=", date('Y-m-d'))
                        ->where('visit_to', ">=", date('Y-m-d'))
                        ->when($request->filled("branch_id"), function ($query) use ($request) {
                            return $query->where('branch_id', $request->branch_id);
                        })
                        ->from('visitors');
                })
                // ->when($request->filled("branch_id"), function ($q) use ($request) {
                //     $q->whereHas("visitor", fn ($q) => $q->where("branch_id", $request->branch_id));
                // })
                // ->whereDate('LogTime', $date)

                ->where('LogTime', '>=', $date . ' ' . $j . ':00:00')
                ->where('LogTime', '<', $date  . ' ' . $j . ':59:59')
                ->get();

            $finalarray[] = [
                "date" => $date,
                "hour" => $i,
                "count" => $model->count(),

            ];
        }


        return  $finalarray;
    }
    public function dashboardAnnouncementList(Request $request)
    {

        return (new Announcement())->with(['category', 'user', 'branch'])->withOut("employees")
            // ->where('start_date', '<=', date("Y-m-d"))
            // ->where('end_date', '>=', date("Y-m-d"))
            ->when($request->filled("branch_id"), function ($q) use ($request) {
                $q->where("branch_id", $request->branch_id);
            })
            ->with('category', function ($query) use ($request) {
                $query
                    ->where('name', "community");
            })

            ->paginate(4);
    }

    public function dashboardGetCountsTodayMultiGeneral(Request $request)
    {

        $finalarray = []; {



            $model = Attendance::with("employee")->where('company_id', $request->company_id)
                ->whereIn('status', ['P', 'A', 'M', 'O', 'H', 'L', 'V'])
                ->whereDate('date', date('Y-m-d'))
                ->when($request->filled("branch_id"), function ($q) use ($request) {
                    $q->whereHas("employee", fn ($q) => $q->where("branch_id", $request->branch_id));
                })
                ->select('status')
                ->get();

            $finalarray['multi'] = [
                "date" => date('Y-m-d'),
                "presentCount" => $model->where('status', 'P')->where('shift_type_id', 2)->count(),
                "absentCount" => $model->where('status', 'A')->where('shift_type_id', 2)->count(),
                "missingCount" => $model->where('status', 'M')->where('shift_type_id', 2)->count(),
                "offCount" => $model->where('status', 'O')->where('shift_type_id', 2)->count(),
                "holidayCount" => $model->where('status', 'H')->where('shift_type_id', 2)->count(),
                "leaveCount" => $model->where('status', 'L')->where('shift_type_id', 2)->count(),
                "vaccationCount" => $model->where('status', 'V')->where('shift_type_id', 2)->count(),
            ];

            $finalarray['general'] = [
                "date" => date('Y-m-d'),
                "presentCount" => $model->where('status', 'P')->where('shift_type_id', '!=', 2)->count(),
                "absentCount" => $model->where('status', 'A')->where('shift_type_id', '!=', 2)->count(),
                "missingCount" => $model->where('status', 'M')->where('shift_type_id', '!=', 2)->count(),
                "offCount" => $model->where('status', 'O')->where('shift_type_id', '!=', 2)->count(),
                "holidayCount" => $model->where('status', 'H')->where('shift_type_id', '!=', 2)->count(),
                "leaveCount" => $model->where('status', 'L')->where('shift_type_id', '!=', 2)->count(),
                "vaccationCount" => $model->where('status', 'V')->where('shift_type_id', '!=', 2)->count(),
            ];
        }


        return  $finalarray;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Theme::where("company_id", $request->company_id)->where("page", $request->page)->where("type", $request->type)->delete();

        return Theme::create([
            "page" => $request->page,
            "type" => $request->type,
            "style" => $request->style,
            "company_id" => $request->company_id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Theme  $theme
     * @return \Illuminate\Http\Response
     */
    public function theme_count(Request $request)
    {
        return $counts = $this->getCounts(0, $request->company_id);
        return str_pad($counts[$request->value] ?? "", 2, '0', STR_PAD_LEFT);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Theme  $theme
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Theme $theme)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Theme  $theme
     * @return \Illuminate\Http\Response
     */
    public function destroy(Theme $theme)
    {
        //
    }
}
