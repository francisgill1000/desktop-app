<?php

namespace App\Http\Controllers\Community;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Community\AttendanceLog;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;


class AccessControlController extends Controller
{
    public function index(Request $request)
    {
        return $this->processFilters($request)->paginate($request->per_page);
    }

    public function ReportPrint(Request $request)
    {
        $data = $this->processFilters($request)->get()->toArray();

        if ($request->debug) return $data;

        $chunks = array_chunk($data, 10);

        return Pdf::setPaper('a4', 'landscape')->loadView('pdf.community.report', [
            "chunks" => $chunks,
            "company" => Company::whereId(request("company_id") ?? 0)->first(),
            "params" => $request->all(),

        ])->stream();
    }

    public function ReportDownload(Request $request)
    {
        $data = $this->processFilters($request)->get()->toArray();

        if ($request->debug) return $data;

        $chunks = array_chunk($data, 10);

        return Pdf::setPaper('a4', 'landscape')->loadView('pdf.community.report', [
            "chunks" => $chunks,
            "company" => Company::whereId(request("company_id") ?? 0)->first(),
            "params" => $request->all(),

        ])->download();
    }


    public function processFilters($request)
    {
        $model = AttendanceLog::query();

        $model->where("company_id", $request->company_id);

        $model->whereDate('LogTime', '>=', $request->filled("from_date") && $request->from_date !== 'null' ? $request->from_date : date("Y-m-d"));

        $model->whereDate('LogTime', '<=', $request->filled("to_date") && $request->to_date !== 'null' ? $request->to_date : date("Y-m-d"));

        $model->whereHas('device', fn ($q) => $q->whereIn('device_type', ["all", "Access Control"]));


        $model->where(function ($m) use ($request) {
            $m->whereHas('tanent', fn ($q) => $q->where("company_id", $request->company_id));
            $m->orWhereHas('member', fn ($q) => $q->where("company_id", $request->company_id));
        });

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

        $model->with('tanent', fn ($q) => $q->where('company_id', $request->company_id));
        $model->with('member', fn ($q) => $q->where('company_id', $request->company_id));

        // ->distinct("LogTime", "UserID", "company_id")
        $model->when($request->filled('department_ids'), function ($q) use ($request) {
            $q->whereHas('employee', fn (Builder $query) => $query->where('department_id', $request->department_ids));
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
                $q->whereHas('device', fn (Builder $query) => $query->where('mode', $request->mode));
            })
            ->when($request->filled('function'), function ($q) use ($request) {
                $q->whereHas('device', fn (Builder $query) => $query->where('function', $request->function));
            })
            ->when($request->filled('devicelocation'), function ($q) use ($request) {
                if ($request->devicelocation != 'All Locations') {

                    $q->whereHas('device', fn (Builder $query) => $query->where('location', env('WILD_CARD') ?? 'ILIKE', "$request->devicelocation%"));
                }
            })

            ->when($request->filled('branch_id'), function ($q) {
                $q->whereHas('employee', fn (Builder $query) => $query->where('branch_id', request("branch_id")));
            });

        return $model;
    }
}
