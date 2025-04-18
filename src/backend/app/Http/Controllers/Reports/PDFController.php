<?php

namespace App\Http\Controllers\Reports;

use App\Models\Shift;
use App\Models\Device;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ShiftType;
use App\Models\Attendance;
use App\Models\Department;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateAccessControlReport;
use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Builder;

class PDFController extends Controller
{

    public function daily_summary(Request $request)
    {
        return Pdf::loadView('pdf.html.daily.daily_summary')->stream();
    }
    public function weekly_summary(Request $request)
    {
        return Pdf::loadView('pdf.html.weekly.weekly_summary_v1')->stream();
    }
    public function monthly_summary()
    {
        return Pdf::loadView('pdf.html.monthly.monthly_summary_v1')->stream();
    }

    public function dailyAccessControl()
    {
        return Pdf::loadView('pdf.html.daily.access_control')->stream();
    }
    public function weeklyAccessControl()
    {
        return Pdf::loadView('pdf.html.weekly.access_control')->stream();
    }
    public function monthlyAccessControl()
    {
        return Pdf::loadView('pdf.html.monthly.access_control')->stream();
    }
    public function monthlyAccessControlV1()
    {
        return Pdf::loadView('pdf.html.monthly.access_control_v1')->stream();
    }
    public function monthlyAccessControlCount()
    {
        return Pdf::loadView('pdf.html.monthly.access_control_count')->stream();
    }
    public function monthlyAccessControlByDevice()
    {
        return Pdf::loadView('pdf.html.monthly.access_control_by_device')->stream();
    }

    public function testPDF()
    {
        $dataArray = [];

        // Populate the array with dummy data for demonstration purposes
        for ($i = 0; $i < 30; $i++) {
            $dataArray[] = [
                'id' => $i + 1,
                'name' => 'John Doe',
                'phone' => '123-456-7890',
                'code' => '101',
                'date' => '2024-01-25 08:00:00',
                'startTime' => '08:00 AM',
                'endTime' => '05:00 PM',
                'mode' => 'Entry',
                'status' => 'Present',
                'user_type' => 'Employee',
            ];
        }

        $chunks = array_chunk($dataArray, 20);
        return Pdf::setPaper('a4', 'landscape')->loadView('pdf.access_control_reports.report', ["chunks" => $chunks])->stream();
    }

    public function accessControlReportPrint()
    {
        $company_id = request("company_id", 0);
        $from_date = request("from_date", date("Y-m-d"));
        $to_date = request("to_date", date("Y-m-d"));
        return $this->PDFMerge("I", $company_id, $from_date, $to_date);
    }

    public function accessControlReportDownload()
    {
        $company_id = request("company_id", 0);
        $from_date = request("from_date", date("Y-m-d"));
        $to_date = request("to_date", date("Y-m-d"));
        return $this->PDFMerge("D", $company_id, $from_date, $to_date);
    }



    public function processFilters($request)
    {
        $model = AttendanceLog::query();

        $model->where("company_id", $request->company_id);

        $model->where('LogTime', '>=', request()->filled("from_date") && request("from_date") !== 'null' ? request("from_date") . " 00:00:00" : date("Y-m-d 00:00:00"));

        $model->where('LogTime', '<=', request()->filled("to_date") && request("to_date") !== 'null' ? request("to_date") .  " 23:59:59" : date("Y-m-d 23:59:59"));

        $model->whereHas('device', fn($q) => $q->whereIn('device_type', ["all", "Access Control"]));


        // $model->where(function ($m) use ($request) {
        //     $m->whereHas('tanent', fn ($q) => $q->where("company_id", $request->company_id));
        //     $m->orWhereHas('member', fn ($q) => $q->where("company_id", $request->company_id));
        // });

        $model->when(request()->filled("report_type"), function ($query) use ($request) {
            if ($request->report_type == "Allowed") {
                return $query->where('status', $request->report_type);
            } else if ($request->report_type == "Access Denied") {
                return $query->where('status', $request->report_type);
            }
        });

        $model->when(request()->filled("UserID"), function ($query) use ($request) {
            return $query->where('UserID', $request->UserID);
        });

        $model->when(request()->filled("DeviceID"), function ($query) use ($request) {
            return $query->where('DeviceID', $request->DeviceID);
        });

        $model->with("device");

        // $model->with('tanent', fn ($q) => $q->where('company_id', $request->company_id));
        // $model->with('member', fn ($q) => $q->where('company_id', $request->company_id));

        // ->distinct("LogTime", "UserID", "company_id")
        $model->when($request->filled('department_ids'), function ($q) use ($request) {
            $q->whereHas('employee', fn(Builder $query) => $query->where('department_id', $request->department_ids));
        })

            ->with('device', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            })


            ->when($request->filled('device'), function ($q) use ($request) {
                $q->where('DeviceID', $request->device);
            })
            ->when($request->filled('system_user_id'), function ($q) use ($request) {
                $q->where('UserID', $request->system_user_id);
            })
            ->when($request->filled('mode'), function ($q) use ($request) {
                $q->whereHas('device', fn(Builder $query) => $query->where('mode', $request->mode));
            })
            ->when($request->filled('function'), function ($q) use ($request) {
                $q->whereHas('device', fn(Builder $query) => $query->where('function', $request->function));
            })
            ->when($request->filled('devicelocation'), function ($q) use ($request) {
                if ($request->devicelocation != 'All Locations') {

                    $q->whereHas('device', fn(Builder $query) => $query->where('location', env('WILD_CARD') ?? 'ILIKE', "$request->devicelocation%"));
                }
            })

            ->with('employee', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            })

            ->when($request->filled('branch_id'), function ($q) {
                $q->whereHas('employee', fn(Builder $query) => $query->where('branch_id', request("branch_id")));
            });

        return $model;
    }

    public function PDFMerge($action = "I", $company_id, $from_date, $to_date)
    {
        $filesDirectory = public_path("access_control_reports/companies/$company_id");

        // Check if the directory exists
        if (!is_dir($filesDirectory)) {
            return response()->json(['error' => 'Directory not found'], 404);
        }

        // $pdfFiles = glob($filesDirectory . '/*.pdf');
        $pdfFiles = [];

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($from_date);
        $endDate = new \DateTime($to_date);

        while ($startDate <= $endDate) {

            $date = $startDate->format("Y-m-d");

            if (glob($filesDirectory . "/$date.pdf")) {
                $pdfFiles[] = glob($filesDirectory . "/$date.pdf")[0];
            }

            $startDate->modify('+1 day');
        }


        if (empty($pdfFiles)) {
            return 'No PDF files found';
        }

        if ($action == "I") {
            return (new Controller)->mergePdfFiles($pdfFiles, $action, "Access-Control-Report.pdf");
        }

        return (new Controller)->mergePdfFiles($pdfFiles, $action, "Access-Control-Report.pdf");
    }
}
