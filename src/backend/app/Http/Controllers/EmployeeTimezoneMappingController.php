<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeTimezoneMapping\StoreRequest;
use App\Http\Requests\EmployeeTimezoneMapping\UpdateRequest;
use App\Models\CompanyBranch;
use App\Models\Employee;
use App\Models\EmployeeTimezoneMapping;
use App\Models\Timezone;
use function PHPUnit\Framework\isJson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeTimezoneMappingController extends Controller
{
    public function index(EmployeeTimezoneMapping $model, Request $request)
    {
        return $model::with(["timezone", "branch"])->where('company_id', $request->company_id)
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })


            ->paginate($request->per_page);
    }

    public function gettimezonesinfo_search(Request $request, $text)
    {
        return EmployeeTimezoneMapping::query()->with(["timezone"])
            ->where('company_id', $request->company_id)

            ->when($request->filled('searchByTimezoneName'), function ($q) use ($request, $text) {
                $q->whereHas('timezone', fn(Builder $query) => $query->where(DB::raw('lower(timezone_name)'), 'LIKE', "$text%"));
            })
            ->paginate($request->per_page);
    }
    public function show(EmployeeTimezoneMapping $model, $id)
    {

        return $model->with(["timezone"])->where('id', $id)->first();
        //return $model->where("id", $id)->first();
    }

    public function store(StoreRequest $request)
    {

        try {
            $record = EmployeeTimezoneMapping::create($request->validated());

            if ($record) {

                $SDKjsonRequest = $this->prepareSDKrequestjson($record);

                $SDKObj = new SDKController;
                //$SDKresponse = ($SDKObj->processSDKRequest("localhost:5000/Person/AddRange", $SDKjsonRequest));
                $SDKresponse = ($SDKObj->PersonAddRangeWithData($SDKjsonRequest));

                $finalArray['SDKRequest'] = $SDKjsonRequest;
                $finalArray['SDKResponse'] = $SDKresponse;

                $finalArray['recordResponse'] = $record;
                return $this->response('EmployeeTimezoneMapping Successfully created.', $finalArray, true);
            } else {
                return $this->response('EmployeeTimezoneMapping cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function filterRequestpayloadBySDKResponse($request, $SDKresponse)
    {
        $SDKresponse = json_decode(json_encode($SDKresponse), true);

        $newRequestDevicesidArray = [];

        foreach ($request->device_id as $device) {

            foreach ($SDKresponse['data'] as $responseDevice) {

                if ($device['device_id'] == $responseDevice['sn'] && $responseDevice['message'] == '') {
                    $newRequestDevicesidArray[] = $device;
                }
            }
        }

        return $newRequestDevicesidArray;
    }
    public function prepareSDKrequestjson($phpArray)
    {

        $finalArray = [];
        if (!isJson($phpArray)) {
            $phpArray = json_decode($phpArray, true);
        } else {
            $phpArray = $phpArray;
        }

        $personsListArray = [];
        $snListArray = array_column($phpArray['device_id'], 'device_id');

        foreach ($phpArray['employee_id'] as $list) {

            //update timezone id in employee table
            $data['timezone_id'] = $phpArray['timezone_id'];
            $record = Employee::find($list['id']);
            if ($record) {
                $record->update($data);
            }

            $row = [];
            $row['name'] = $list['display_name'];
            $row['userCode'] = $list['system_user_id'];
            //$row['expiry'] = "2089-12-31 23:59:59";
            $row['timeGroup'] = $phpArray['timezone_id'];

            $personsListArray[] = $row;
        }

        $finalArray['snList'] = $snListArray;
        $finalArray['personList'] = $personsListArray;
        return $finalArray;
    }
    public function filterArrayByKeys(array $input, array $column_keys)
    {
        $result = array();
        $column_keys = array_flip($column_keys); // getting keys as values
        foreach ($input as $key => $val) {
            // getting only those key value pairs, which matches $column_keys
            $result[$key] = array_intersect_key($val, $column_keys);
        }
        return $result;
    }
    public function update(UpdateRequest $request, EmployeeTimezoneMapping $EmployeeTimezoneMapping)
    {
        try {

            //updating default timezone id which are already exist in TimezoneName
            if ($request->timezone_id) {
                Employee::where('timezone_id', $request->timezone_id)
                    ->update(['timezone_id' => 1]);
            }

            $record = $EmployeeTimezoneMapping->update($request->all());

            if ($record) {

                $SDKjsonRequest = $this->prepareSDKrequestjson($request->all());

                $SDKObj = new SDKController;
                //$SDKresponse = ($SDKObj->processSDKRequest("localhost:5000/Person/AddRange", $SDKjsonRequest));
                //$SDKresponse = ($SDKObj->processSDKRequest("", $SDKjsonRequest));
                $SDKresponse = ($SDKObj->PersonAddRangeWithData($SDKjsonRequest));

                $finalArray['SDKRequest'] = $SDKjsonRequest;

                try {
                    $finalArray['SDKResponse'] = json_decode($SDKresponse, true);

                    $finalArray['recordResponse'] = $request->all();
                } catch (\Throwable $th) {
                }
                return $this->response('EmployeeTimezoneMapping successfully updated.', $finalArray, true);
            } else {
                return $this->response('EmployeeTimezoneMapping cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(EmployeeTimezoneMapping $EmployeeTimezoneMapping)
    {
        $record = $EmployeeTimezoneMapping->delete();

        if ($record) {
            return $this->response('EmployeeTimezoneMapping successfully deleted.', $record, true);
        } else {
            return $this->response('EmployeeTimezoneMapping cannot delete.', null, false);
        }
    }
    public function deleteTimezone(Request $request)
    {

        if ($request->timezone_id) {
            Employee::where('timezone_id', $request->timezone_id)
                ->update(['timezone_id' => 1]);
        }
        $record = EmployeeTimezoneMapping::where('id', $request->id)->delete();

        // //updating default timezone id which are already exist in TimezoneName

        if ($record) {
            return $this->response('EmployeeTimezoneMapping successfully deleted.', $record, true);
        } else {
            return $this->response('EmployeeTimezoneMapping cannot delete.', null, false);
        }
    }
    public function get_employeeswith_timezonename(Employee $employee, Request $request)
    {
        // return $columns = collect(DB::select('PRAGMA table_info(employees)'))
        //     ->pluck('name') // Get the column names
        //     ->toArray();



        $employees['data'] = $employee
            ->with(["timezone", "finger_prints", "palms"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('department_id'), function ($q) use ($request) {
                if ($request->department_id != '---') {
                    $q->where('department_id', $request->department_id);
                }
            })
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->get(
                [
                    "id",
                    "first_name",
                    "last_name",
                    "profile_picture",
                    "phone_number",
                    "whatsapp_number",
                    "phone_relative_number",
                    "whatsapp_relative_number",
                    "employee_id",
                    "joining_date",
                    "designation_id",
                    "department_id",
                    "user_id",
                    "role_id",
                    "sub_department_id",
                    "overtime",
                    "mobile_application",
                    "relation",
                    "file_no",
                    "type",
                    "title",
                    "grade",
                    "work_site",
                    "status",
                    "employee_role_id",
                    "local_address",
                    "local_tel",
                    "local_mobile",
                    "local_fax",
                    "local_city",
                    "local_country",
                    "local_email",
                    "local_residence_no",
                    "home_address",
                    "home_tel",
                    "home_mobile",
                    "home_fax",
                    "home_city",
                    "home_state",
                    "home_country",
                    "home_email",
                    "company_id",
                    "branch_id",
                    "created_at",
                    "updated_at",
                    "isAutoShift",
                    "system_user_id",
                    "display_name",
                    "timezone_id",
                    "leave_group_id",
                    "reporting_manager_id",
                    "face_uuid",
                    "rfid_card_number",
                    "rfid_card_password",
                    "lockDevice",
                    // "full_name"
                ]
            );
        return $employees;
    }
    public function get_employeeswith_timezonename_id(Employee $employee, Request $request, $id)
    {

        $employees['data'] = $employee
            ->with(["timezone"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->whereIn('timezone_id', Timezone::where('timezone_id', $id)->where('company_id', $request->company_id)->select('timezone_id')->get())
            ->get();
        return $employees;
    }
    public function gettimezonesinfo(EmployeeTimezoneMapping $model, Request $request)
    {
        return $model->where('company_id', $request->company_id)
            ->when($request->filled('branch_id'), fn($q) =>  $q->where('branch_id', $request->branch_id))
            ->when($request->filled('timezoneName'), function ($q) use ($request) {
                $q->whereHas('timezone', fn(Builder $query) => $query->where('timezone_name', env('WILD_CARD') ?? 'ILIKE', "$request->timezoneName%"));
            })
            ->when($request->filled('device'), function ($q) use ($request) {
                $q->whereJsonContains('device_id', [['name' => "$request->device"]]);
            })
            ->when($request->filled('employees'), function ($q) use ($request) {
                $q->whereJsonContains('employee_id', [['first_name' => "$request->employees"]]);
            })
            ->when($request->filled('employee_id'), function ($q) use ($request) {
                $q->whereJsonContains('employee_id', [['employee_id' => $request->employee_id]]);
            })

            ->with(["timezone", "branch"])
            ->paginate($request->per_page);
    }
}
