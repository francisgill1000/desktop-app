<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeRequest\StoreRequest;
use App\Http\Requests\ChangeRequest\UpdateRequest;
use App\Models\Attendance;
use App\Models\ChangeRequest;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $model = ChangeRequest::query();

        $model->where("company_id", $request->company_id);
        $model->when($request->filled("employee_device_id"), fn ($q) => $q->where('employee_device_id', $request->employee_device_id));
        $model->when($request->filled("UserID"), fn ($q) => $q->where('employee_device_id', $request->employee_device_id));
        $model->when($request->filled("request_type"), fn ($q) => $q->where('request_type', $request->request_type));
        $model->when($request->filled("status"), fn ($q) => $q->where('status', $request->status));

        $model->when($request->filled("branch_id"), function ($query) {
            $query->whereHas("employee", fn ($q) => $q->where('branch_id', request("branch_id")));
        });

        $model->with(["branch", "employee"]);

        $model->orderBy("id", "desc");

        return $model->paginate($request->per_page ?? 100);
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $request->validated();
            if (isset($request->attachment) && $request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('attachment')->move(public_path('/ChangeRequest/attachments'), $fileName);
                $data['attachment'] = $fileName;
            }

            $record = ChangeRequest::create($data);

            if ($record) {
                return $this->response('ChangeRequest created.', $record, true);
            } else {
                return $this->response('ChangeRequest cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(ChangeRequest $ChangeRequest)
    {
        return $ChangeRequest;
    }

    public function updateChangeRequest($id, UpdateRequest $request)
    {
        try {
            // Validate the request data using the UpdateRequest rules
            $data = $request->all();

            // Start a database transaction
            DB::beginTransaction();

            // Update Attendance records

            // A status = Approve from change request table
            if ($data['status'] == "A") {

                Attendance::where('company_id', $data['company_id'])
                    ->where('employee_id', $data['employee_device_id'])
                    ->whereBetween('date', [$data['from_date'], $data['to_date']])
                    ->update(['status' => "P"]);
            }

            // Update the ChangeRequest
            $record = ChangeRequest::where('id', $id)->update(['status' => $data['status']]);

            // Commit the transaction if all operations are successful
            DB::commit();

            if ($record) {

                $employee = Employee::where("system_user_id", $data['employee_device_id'])->where("company_id", $data['company_id'])->first();

                Notification::create([
                    "data" => "Attendance request has been updated",
                    "action" => "Attendance Request",
                    "model" => "Attendance",
                    "user_id" => $employee->user_id ?? 0,
                    "company_id" => $data['company_id'],
                    "redirect_url" => "change_requests"
                ]);

                return $this->response('ChangeRequest updated.', $record, true);
            } else {
                return $this->response('ChangeRequest cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            // Roll back the transaction in case of an error
            DB::rollBack();

            return $this->response('An error occurred while updating the ChangeRequest.', null, false);
        }
    }


    public function destroy(ChangeRequest $ChangeRequest)
    {
        if ($ChangeRequest->delete()) {
            return $this->response('ChangeRequest successfully deleted.', null, true);
        } else {
            return $this->response('ChangeRequest cannot delete.', null, false);
        }
    }
}
