<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeLeaves\StoreRequest;
use App\Http\Requests\EmployeeLeaves\UpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeLeaves;
use App\Models\EmployeeLeaveTimeline;
use App\Models\Holidays;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeLeavesController extends Controller
{
    public function getDefaultModelSettings($request)
    {
        $model = EmployeeLeaves::query();
        $model->with(["employee_leave_timelines", "leave_type", "employee.department.branch", "employee.leave_group", "reporting", "alternate_employee"]);
        $model->where('company_id', $request->company_id);

        // $model->when($request->filled('order'), function ($q) use ($request) {
        //     $q->where("order", ">=", $request->order);
        // });

        $model->when($request->filled('department_id'), function ($q) {
            $q->whereHas("employee.department", function ($q) {
                $q->where("department_id", request("department_id"));
            });
        });

        $model->when($request->filled('employee_id'), function ($q) use ($request) {
            $q->where("employee_id", $request->employee_id);
        });

        $model->when($request->filled('leave_type_id'), function ($q) use ($request) {
            $q->where('leave_type_id', $request->leave_type_id);
        });
        $model->when($request->start_date && $request->end_date, function ($q) use ($request) {
            $q->where("start_date", ">=", $request->start_date);
            $q->where("end_date", "<=", $request->end_date);
        });

        $model->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->whereHas('employee',  function ($qu) use ($request) {
                $qu->where("branch_id", $request->branch_id);
            });
        });

        $model->when($request->filled('status'), function ($q) use ($request) {
            if (strtolower($request->status) == 'approved') {
                $q->where('status', 1);
            } else if (strtolower($request->status) == 'rejected') {
                $q->where('status', 2);
            } else if (strtolower($request->status) == 'pending') {
                $q->where('status', 0);
            }
        });
        $model->when($request->filled('sortBy'), function ($q) use ($request) {
            $sortDesc = $request->input('sortDesc');
            if (strpos($request->sortBy, '.')) {
                if ($request->sortBy == 'employee.name') {
                    $q->orderBy(Employee::select("first_name")->whereColumn("employees.id", "employee_leaves.employee_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                } else if ($request->sortBy == 'group.name') {
                    $q->orderBy(Employee::select("first_name")->whereColumn("employees.id", "employee_leaves.employee_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                } else if ($request->sortBy == 'leave_type.name') {
                    $q->orderBy(LeaveType::select("name")->whereColumn("leave_types.id", "employee_leaves.leave_type_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                }
            } else {
                $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                }
            }
        });

        if (!$request->sortBy) {
            $model->orderBy('id', 'desc');
        }
        return $model;
    }

    public function index(Request $request)
    {

        return $this->getDefaultModelSettings($request)->paginate($request->per_page ?? 100);
    }

    function list(Request $request)
    {
        return $this->getDefaultModelSettings($request)->paginate($request->per_page ?? 100);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();

        try {
            // Database operations
            $data = $request->validated();

            $data["order"] = -1;

            $record = EmployeeLeaves::create($data);

            $record->load("employee");

            EmployeeLeaveTimeline::create([
                "employee_leave_id" => $record->id,
                "description" => "Employee <b>{$record->employee->first_name}</b> has sent a leave request.",
            ]);
            if ($record) {
                DB::commit();
                return $this->response('Employee Leave Successfully created.', $record, true);
            } else {
                return $this->response('Employee Leave cannot be created.', null, false);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    public function update(UpdateRequest $request, EmployeeLeaves $EmployeeLeaves, $id)
    {

        try {
            $record = $EmployeeLeaves::find($id)->update($request->all());

            if ($record) {

                return $this->response('Employee Leave successfully updated.', $record, true);
            } else {
                return $this->response('Employee Leave cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(EmployeeLeaves $EmployeeLeaves, $id)
    {

        if (EmployeeLeaves::find($id)->delete()) {

            return $this->response('Employee Leave successfully deleted.', null, true);
        } else {
            return $this->response('Employee Leave cannot delete.', null, false);
        }
    }
    public function search(Request $request, $key)
    {
        return $this->getDefaultModelSettings($request)->where('title', 'LIKE', "%$key%")->paginate($request->per_page ?? 100);
    }
    public function deleteSelected(Request $request)
    {
        $record = EmployeeLeaves::whereIn('id', $request->ids)->delete();
        if ($record) {

            return $this->response('Employee Leave Successfully delete.', $record, true);
        } else {
            return $this->response('Employee Leave cannot delete.', null, false);
        }
    }


    public function newNotifications(Request $request)
    {

        $model = EmployeeLeaves::query();
        $model->with(["leave_type", "employee.leave_group", "reporting"]);
        $model->where('company_id', $request->company_id);
        $model->where('status', 0);
        $model->when($request->filled("branch_id"), function ($q) use ($request) {
            $q->whereHas("employee", fn($q) => $q->where("branch_id", $request->branch_id));
        });
        $model->when($request->filled("department_id") && $request->department_id > 0, function ($q) use ($request) {
            $q->whereHas("employee", fn($q) => $q->where("department_id", $request->department_id));
        });
        /// $model->where('created_at', '>=', date('Y-m-d H:i:00', strtotime('-2 minutes')));

        $data['new_leaves_data'] = $model->paginate($request->per_page ?? 100);

        $model = EmployeeLeaves::query();
        //$model->with(["leave_type", "employee.leave_group", "reporting"]);
        $model->where('company_id', $request->company_id);
        $model->where('status', 0);
        $model->when($request->filled("branch_id"), function ($q) use ($request) {
            $q->whereHas("employee", fn($q) => $q->where("branch_id", $request->branch_id));
        });
        $model->when($request->filled("department_id") && $request->department_id > 0, function ($q) use ($request) {
            $q->whereHas("employee", fn($q) => $q->where("department_id", $request->department_id));
        });
        $data['total_pending_count'] = $model->count();
        $data['status'] = true;
        return $data;
    }
    public function newEmployeeNotifications(Request $request)
    {

        $model = EmployeeLeaves::query();
        $model->with(["leave_type", "employee.leave_group", "reporting"]);
        $model->where('company_id', $request->company_id);
        $model->where('employee_id', $request->employee_id);
        $model->where('status', '>', 0);
        $model->where('created_at', '>=', date('Y-m-d H:i:00', strtotime('-2 minutes')));

        $data['new_leaves_data'] = $model->paginate($request->per_page ?? 100);

        $model = EmployeeLeaves::query();
        $model->with(["leave_type", "employee.leave_group", "reporting"]);
        $model->where('company_id', $request->company_id);
        $model->where('employee_id', $request->employee_id);
        $model->where('status', 0);

        $data['total_pending_count'] = $model->count();
        $data['status'] = true;
        return $data;
    }

    public function approveLeave(Request $request, $leaveId)
    {
        $model = EmployeeLeaves::find($leaveId);

        if (!$model) {
            return $this->response('Employee Leave data is not available.', null, false);
        }

        $lastAdmin = User::where("company_id", $model->company_id)
            ->where("order", "<", $request->order)
            ->orderBy("order", "desc")
            ->value("order") ?? 0;

        if ($model->order == -1) {
            $lastAdmin = $this->getLastAdminInOrder($request->company_id);
        } else if ($model->order == 0) {
            $model->status = 1;
        }

        $model->approve_reject_notes = $request->approve_reject_notes;

        $model->order = $lastAdmin;

        $record = $model->save();

        $status_text = "approved";

        if ($record) {
            if ($model->order == 0) {
                $employee = Employee::where(["company_id" => $model->company_id, "employee_id" => $model->employee_id])->first();
                Notification::create([
                    "data" => "Leave application has been $status_text",
                    "action" => "Leave Status",
                    "model" => "EmployeeLeaves",
                    "user_id" => $employee->user_id ?? 0,
                    "company_id" => $model->company_id,
                    "redirect_url" => "leaves"
                ]);
            }

            $user_name = $request->user_name ?? 'Super User';

            $reason = $request->approve_reject_notes ? " Reason: <i>{$model->approve_reject_notes}</i>." : "";

            $description = "Leave application has been <span class='primary--text'>$status_text</span> by <b>$user_name</b>.<br>$reason";

            EmployeeLeaveTimeline::create([
                "employee_leave_id" => $leaveId,
                "description" => $description,
            ]);

            return $this->response("Employee Leave $status_text Successfully.", $record, true);
        } else {
            return $this->response("Employee Leave not $status_text.", null, false);
        }
        return;
        // return $this->processLeaveStatus($request, $leaveId, 1, "approved");
    }

    public function rejectLeave(Request $request, $leaveId)
    {
        return $this->processLeaveStatus($request, $leaveId, 2, "rejected");
    }

    public function processLeaveStatus($request, $leaveId, $status_id, $status_text)
    {
        $model = EmployeeLeaves::find($leaveId);

        if ($model) {
            $model->status = $status_id;
            $model->approve_reject_notes = $request->approve_reject_notes;
            $record = $model->save();

            if ($record) {

                $employee = Employee::where(["company_id" => $model->company_id, "employee_id" => $model->employee_id])->first();

                $user_name = $request->user_name ?? 'Super User';

                $reason = $request->approve_reject_notes ? " Reason: <i>{$model->approve_reject_notes}</i>." : "";

                $description = "Leave application has been <span class='red--text'>$status_text</span> by <b>$user_name</b>.<br>$reason";

                EmployeeLeaveTimeline::create([
                    "employee_leave_id" => $leaveId,
                    "description" => $description,
                ]);

                Notification::create([
                    "data" => "Leave application has been $status_text",
                    "action" => "Leave Status",
                    "model" => "EmployeeLeaves",
                    "user_id" => $employee->user_id ?? 0,
                    "company_id" => $model->company_id,
                    "redirect_url" => "leaves"
                ]);

                return $this->response("Employee Leave $status_text Successfully.", $record, true);
            } else {
                return $this->response("Employee Leave not $status_text.", null, false);
            }
        } else {
            return $this->response('Employee Leave data is not available.', null, false);
        }
    }

    public function getLastAdminInOrder($company_id)
    {
        return User::where("company_id", $company_id)
            ->where("order", ">", 0)
            ->orderBy("order", "desc")
            ->value("order") ?? 0;
    }


    public function getEvents(Request $request)
    {

        $startDate = new DateTime(); // Today's date
        $endDate = (new DateTime())->modify('+1 year'); // Next year's date
        $interval = new DateInterval('P1D'); // 1-day interval
        $period = new DatePeriod($startDate, $interval, $endDate);

        $companyId = $request->input('company_id', 0);

        $model = EmployeeLeaves::query();
        $model->with(["employee_leave_timelines", "leave_type", "employee.department.branch", "employee.leave_group", "reporting", "alternate_employee"]);
        $model->where('company_id', $companyId);
        $model->where("start_date", ">=", date("Y-m-d"));
        $model->where('status', 1);
        $leaves = $model->get(["start_date", "end_date"])->toArray();

        $model = Holidays::query();
        $model->where('company_id', $request->company_id);
        $model->where("start_date", ">=", date("Y-m-d"));
        $holidays = $model->get(["start_date", "end_date"])->toArray();

        $events = [];


        foreach ($period as $date) {
            foreach ($leaves as $leave) {
                if ($date->format('Y-m-d') >= $leave['start_date'] &&  $date->format('Y-m-d')  <= $leave['end_date']) {
                    $events[$date->format('Y-m-d')] = "orange";
                }
            }
            foreach ($holidays as $holiday) {
                if ($date->format('Y-m-d') >= $holiday['start_date'] &&  $date->format('Y-m-d')  <= $holiday['end_date']) {
                    $events[$date->format('Y-m-d')] = "primary";
                }
            }
        }

        return $events;
    }

    public function getLeavesForNextThirtyDaysMonth(Request $request)
    {
       

        $companyId = $request->input('company_id', 0);
        $today = date("Y-m-d");
        $nextThirtyDays = date("Y-m-d", strtotime("+30 days"));

        //  return EmployeeLeaves::where('company_id', $companyId)
        // ->whereBetween('start_date', [$today, $nextThirtyDays])
        // ->update(['status'=> 1]);

        return EmployeeLeaves::where('company_id', $companyId)
            ->whereBetween('start_date', [$today, $nextThirtyDays])
            ->where('status', 1)
            ->with(["leave_type","employee.leave_group"])
            ->get(["start_date", "end_date","employee_id"])
            ->toArray();
    }
}
