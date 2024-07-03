<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportNotification\StoreRequest;
use App\Http\Requests\ReportNotification\UpdateRequest;
use App\Mail\ReportNotificationMail;
use App\Models\ReportNotification;
use App\Models\ReportNotificationManagers;
use App\Notifications\CompanyCreationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReportNotificationController extends Controller
{
    public function index(ReportNotification $model, Request $request)
    {


        $model = $model->with(["managers", "logs"])
            ->where('company_id', $request->company_id)
            ->where('type', request("type") ?? "automation")


            ->with("managers", function ($query) use ($request) {
                $query->where("company_id", $request->company_id);
            })
            ->when($request->filled('subject'), function ($q) use ($request) {
                $q->where('subject', env('WILD_CARD') ?? 'ILIKE', "$request->subject%");
            })
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->when($request->filled('frequency'), function ($q) use ($request) {
                $q->where('frequency', env('WILD_CARD') ?? 'ILIKE', "$request->frequency%");
            })


            ->when($request->filled('manager1'), function ($q) use ($request) {

                $q->whereHas("managers", fn ($q) => $q->where("name", env('WILD_CARD') ?? 'ILIKE', $request->manager1 . '%')->orWhere("email", env('WILD_CARD') ?? 'ILIKE', $request->manager1 . '%')->orWhere("whatsapp_number", env('WILD_CARD') ?? 'ILIKE', $request->manager1 . '%'));
            })
            ->when($request->filled('manager2'), function ($q) use ($request) {

                $q->whereHas("managers", fn ($q) => $q->where("name", env('WILD_CARD') ?? 'ILIKE', $request->manager2 . '%')->orWhere("email", env('WILD_CARD') ?? 'ILIKE', $request->manager2 . '%')->orWhere("whatsapp_number", env('WILD_CARD') ?? 'ILIKE', $request->manager2 . '%'));
            })
            ->when($request->filled('manager3'), function ($q) use ($request) {

                $q->whereHas("managers", fn ($q) => $q->where("name", env('WILD_CARD') ?? 'ILIKE', $request->manager3 . '%')->orWhere("email", env('WILD_CARD') ?? 'ILIKE', $request->manager3 . '%')->orWhere("whatsapp_number", env('WILD_CARD') ?? 'ILIKE', $request->manager3 . '%'));
            })
            ->when($request->filled('time'), function ($q) use ($request) {
                $q->where('time', env('WILD_CARD') ?? 'ILIKE', "$request->time%");
            })
            ->when($request->filled('medium'), function ($q) use ($request) {
                $q->where('mediums', env('WILD_CARD') ?? 'ILIKE', "%$request->medium%");
            })

            ->when($request->filled('serach_medium'), function ($q) use ($request) {
                $key = strtolower($request->serach_medium);
                //$q->where(DB::raw("json_contains('mediums', '$key')"));
                //$q->WhereJsonContains('mediums', $key);
                $q->WhereJsonContains(DB::raw('lower("mediums"::text)'), $key);
            })
            ->when($request->filled('serach_email_recipients'), function ($q) use ($request) {
                $key = strtolower($request->serach_email_recipients);
                $q->WhereJsonContains(DB::raw('lower("tos"::text)'), $key);
            })

            ->when($request->filled('sortBy'), function ($q) use ($request) {
                $sortDesc = $request->input('sortDesc');
                if (strpos($request->sortBy, '.')) {
                    // if ($request->sortBy == 'department.name.id') {
                    //     $q->orderBy(Department::select("name")->whereColumn("departments.id", "employees.department_id"), $sortDesc == 'true' ? 'desc' : 'asc');

                    // }

                } else {
                    $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                    }
                }
            });

        if (!$request->filled('sortBy')) {
            $model = $model->orderBy('updated_at', 'desc');
        }
        return $model->with("branch")
            ->paginate($request->per_page);
    }
    public function testmail()
    {
        $model = ReportNotification::with(["managers"])->where("id", 35)->first();

        // $test = Mail::to("akildevs1004@gmail.com")
        //     ->queue(new ReportNotificationMail($model));

        $test2 = Mail::to('akildevs1004@gmail.com')->send(new ReportNotificationMail($model));

        // $test3 = NotificationsController::toSend(["email" => "akildevs1004@gmail.com"], new CompanyCreationNotification, $model);

        return ['111111',   $test2];
    }
    public function store(StoreRequest $request)
    {
        if (!$request->validated())
            return false;

        try {
            $record = ReportNotification::create($request->except('managers'));

            if ($record) {
                $notification_id = $record->id;

                $managers = $request->only('managers');
                foreach ($managers['managers'] as $manager) {
                    $manager['notification_id'] = $notification_id;


                    ReportNotificationManagers::create($manager);
                }



                return $this->response('Report Notification created.', $record, true);
            } else {
                return $this->response('Report Notification cannot created.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(ReportNotification $ReportNotification)
    {
        return $ReportNotification->load("branch");
    }

    public function update(UpdateRequest $request, ReportNotification $ReportNotification)
    {
        try {

            if (!$request->validated())
                return false;

            $record = $ReportNotification->update($request->except('managers'));

            if ($record) {


                $notification_id = $ReportNotification->id;

                ReportNotificationManagers::where("notification_id", $notification_id)->delete();

                $managers = $request->only('managers');
                foreach ($managers['managers'] as $manager) {
                    $manager['notification_id'] = $notification_id;


                    ReportNotificationManagers::create($manager);
                }


                return $this->response('Report Notification updated.', $record, true);
            } else {
                return $this->response('Report Notification not updated.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(ReportNotification $ReportNotification)
    {
        $record = $ReportNotification->delete();

        if ($record) {
            return $this->response('Report Notification deleted.', $record, true);
        } else {
            return $this->response('Report Notification cannot delete.', null, false);
        }
    }
}
