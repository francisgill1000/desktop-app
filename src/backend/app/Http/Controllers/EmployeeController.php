<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\ContactRequest;
use App\Http\Requests\Employee\EmployeeContactRequest;
use App\Http\Requests\Employee\EmployeeImportRequest;
use App\Http\Requests\Employee\EmployeeOtherRequest;
use App\Http\Requests\Employee\EmployeeRequest;
use App\Http\Requests\Employee\EmployeeUpdateContact;
use App\Http\Requests\Employee\EmployeeUpdateRequest;
use App\Http\Requests\Employee\StoreRequest;
use App\Http\Requests\Employee\StoreRequestFromDevice;
use App\Http\Requests\Employee\UpdateRequestFromDevice;
use App\Http\Requests\Employee\UpdateRequest;
use App\Imports\excelEmployeesData;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\CompanyContact;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Device;
use App\Models\Employee;
use App\Models\FingerPrint;
use App\Models\Palm;
use App\Models\Payroll;
use App\Models\Payslips;
use App\Models\ScheduleEmployee;
use App\Models\Timezone;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function dropdownList()
    {
        $model = Employee::query();
        $model->where('company_id', request('company_id'));
        $model->when(request()->filled('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')));
        //$model->excludeRelations();
        $model->with(["department", "sub_department", "designation"]);
        $model->select("profile_picture", "id",  "first_name as name",   "first_name", "last_name", "system_user_id",  "employee_id", "branch_id", "department_id", "designation_id", "sub_department_id");
        $model->orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc");
        $model->with("schedule_all:employee_id,shift_type_id");
        $model->with("latestSchedule:employee_id,shift_type_id");
        return $model->get();
    }

    public $company_id = null;

    public function validateEmployee(EmployeeRequest $request)
    {
        return ['status' => true];
    }
    public function validateContact(EmployeeContactRequest $request)
    {
        return ['status' => true];
    }
    public function validateOther(EmployeeOtherRequest $request)
    {
        return ['status' => true];
    }
    public function donwnloadStorageFile(Request $request)
    {

        if (isset($request->file_name)) {


            $filePath = storage_path(urldecode($request->file_name));

            // Check if the file exists
            if (file_exists($filePath)) {
                // Create a response to download the file
                return response()->download($filePath, "download.pdf");
            } else {
            }
        } else {
            return null;
        }
    }
    public function employeeStore(StoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $fileName);
            $data['profile_picture'] = $fileName;
        }

        $maximumEmployeeCount = Company::whereId($data["company_id"])->pluck("max_employee")[0];
        $existEmployeeCount = Employee::where("company_id", $data["company_id"])->count();



        if ($maximumEmployeeCount - $existEmployeeCount <= 0) {
            return $this->response("Account Maximum " . $maximumEmployeeCount . " Employee count is reached.", null, false);
        }

        // DB::beginTransaction();

        if ($request->filled('email')) {

            $user = User::create([
                "user_type" => "employee",
                "name" => "null",
                "email" => $request->email,
                "password" => Hash::make("secret"),
                "company_id" => $data["company_id"],
            ]);

            if (!$user) {
                return $this->response('User cannot add.', null, false);
            }

            $data["user_id"] = $user->id;
        }

        unset($data['email']);

        try {

            $employee = Employee::create($data);
            if (!$employee) {
                return $this->response('Employee cannot add.', null, false);
            }
            $employee->profile_picture = asset('media/employee/profile_picture' . $employee->profile_picture);

            // DB::commit();

            //set default attendance data for new Employees(1 month) 

            (new AttendanceController)->seedDefaultData($data["company_id"], [$data["system_user_id"]], $data["branch_id"]);


            return $this->response('Employee successfully created.', null, true);
        } catch (\Throwable $th) {
            // DB::rollBack();
            return $this->response('Error Occured. Try again ', null, false);
            throw $th;
        }
    }

    public function employeeUpdate(UpdateRequest $request, $id)
    {
        $data = $request->validated();

        $employee = Employee::where("id", $id)->first();

        if ($request->employee_role_id) {

            $user = User::where('id', $employee->user_id)->first();

            if ($user) {
                $user->update(['employee_role_id' => $request->employee_role_id, 'role_id' => $request->employee_role_id]);
            } else {
                $user = User::create(
                    [
                        "user_type" => "employee",
                        'name' => 'null',
                        'email' => "---",
                        'password' => "---",
                        'company_id' => $employee->company_id,
                        'employee_role_id' => $request->employee_role_id,
                        'role_id' => $request->employee_role_id,
                    ]
                );

                $data["user_id"] = $user->id;
            }
        }

        if ($request->profile_picture && $request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $fileName);
            $data['profile_picture'] = $fileName;
        }


        // if ($data['sub_department_id']=='---')
        // {

        // }
        try {
            $updated = $employee->update($data);
            if (!$updated) {
                return $this->response('Employee cannot update.', null, false);
            }

            // $employee->profile_picture = asset('media/employee/profile_picture' . $request->profile_picture);
            return $this->response('Employee Details successfully updated.', $employee, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function readExcel()
    {
        $reader = new ReaderXlsx();
    }
    public function employeeSingle($id)
    {
        return Employee::with("user")->find($id);
    }

    public function index(Employee $employee, Request $request)
    {

        $data = $employee->filter($request)->paginate($request->per_page ?? 100);

        return $this->getPayslipstatus($data, $request);
    }

    public function indexV1(Employee $employee, Request $request)
    {
        return  $employee->filterV1($request)->paginate($request->per_page ?? 100);
    }

    public function document_expiry(Request $request)
    {

        $expiryDate = date("Y-m-d", strtotime("+30 days"));
        $company_id = request("company_id");


        $data = (new Employee)->document_expiry_filter($request);

        $data->where(function ($query) use ($expiryDate) {
            $query->whereHas("passport", function ($q) use ($expiryDate) {
                $q->whereDate("expiry_date", "<=", $expiryDate);
            });
            $query->orWhereHas("emirate", function ($q) use ($expiryDate) {
                $q->whereCompanyId(request("company_id"))
                    ->whereDate("expiry", "<=", $expiryDate);
            });
            $query->orWhereHas("visa", function ($q) use ($expiryDate) {
                $q->whereCompanyId(request("company_id"))
                    ->whereDate("expiry_date", "<=", $expiryDate);
            });
        });


        $data->with([
            "passport" => function ($q) use ($expiryDate, $company_id) {
                $q->whereCompanyId($company_id)
                    ->whereDate("expiry_date", "<=", $expiryDate);
            },
            "emirate" => function ($q) use ($expiryDate, $company_id) {
                $q->whereCompanyId($company_id)
                    ->whereDate("expiry", "<=", $expiryDate);
            },
            "visa" => function ($q) use ($expiryDate, $company_id) {
                $q->whereCompanyId($company_id)
                    ->whereDate("expiry_date", "<=", $expiryDate);
            },

            "branch",
            "department",
            "designation",

            "user" => fn($q) => $q->select("id", "email")
        ]);

        $data->withOut("schedule");

        $data->select([
            "id",
            "first_name",
            "last_name",
            "profile_picture",
            "phone_number",
            "whatsapp_number",
            "employee_id",
            "designation_id",
            "department_id",
            "user_id",
            "system_user_id",
            "display_name",
            "branch_id"
        ]);

        return $data->paginate($request->per_page ?? 100);
    }

    public function document_expiry_print_pdf(Request $request)
    {

        $expiryDate = date("Y-m-d", strtotime("+30 days"));

        $company_id = request("company_id");

        $data = (new Employee)->document_expiry_filter($request);

        $data->where(function ($query) use ($expiryDate) {
            $query->whereHas("passport", function ($q) use ($expiryDate) {
                $q->whereDate("expiry_date", "<=", $expiryDate);
            });
            $query->orWhereHas("emirate", function ($q) use ($expiryDate) {
                $q->whereCompanyId(request("company_id"))
                    ->whereDate("expiry", "<=", $expiryDate);
            });
            $query->orWhereHas("visa", function ($q) use ($expiryDate) {
                $q->whereCompanyId(request("company_id"))
                    ->whereDate("expiry_date", "<=", $expiryDate);
            });
            $query->orWhereHas("visa", function ($q) use ($expiryDate) {
                $q->whereCompanyId(request("company_id"))
                    ->whereDate("labour_expiry_date", "<=", $expiryDate);
            });
        });


        $data->with([
            "passport" => function ($q) use ($expiryDate, $company_id) {
                $q->whereCompanyId($company_id)
                    ->whereDate("expiry_date", "<=", $expiryDate);
            },
            "emirate" => function ($q) use ($expiryDate, $company_id) {
                $q->whereCompanyId($company_id)
                    ->whereDate("expiry", "<=", $expiryDate);
            },
            "visa" => function ($q) use ($expiryDate, $company_id) {
                $q->whereCompanyId($company_id)
                    ->whereDate("expiry_date", "<=", $expiryDate);
            },

            "branch",
            "department",
            "designation",

            "user" => fn($q) => $q->select("id", "email")
        ]);

        $data->withOut("schedule");

        $data->select([
            "id",
            "first_name",
            "last_name",
            "profile_picture",
            "phone_number",
            "whatsapp_number",
            "employee_id",
            "designation_id",
            "department_id",
            "user_id",
            "system_user_id",
            "display_name",
            "branch_id"
        ]);

        return $this->print_pdf($request, $data->get()->toArray());
    }

    public function print_pdf($request, $data)
    {
        return Pdf::loadView("pdf.document_expiry.custom", [
            "data" => $data,
            "company" => Company::whereId($request->company_id ?? 0)->first(),
            "params" => $request->all()
        ])->stream();
    }

    public function searchby_emp_table_salary(Request $request, $text)
    {

        $text = strtolower($text);
        $data = Employee::query()
            ->latest()
            ->with(["reportTo", "schedule", "user", "department", "sub_department", "designation", "role", "payroll", "timezone"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->whereHas('department', fn(Builder $query) => $query->where('department_id', $request->department_id));
            })
            ->when($request->filled('search_column_name'), function ($q) use ($request, $text) {
                $q->where($request->search_column_name, env('WILD_CARD') ?? 'ILIKE', "$text%");
            })
            ->when($request->filled('search_department_name'), function ($q) use ($request, $text) {
                $q->whereHas('department', fn(Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$text%"));
            })
            ->when($request->filled('search_designation_name'), function ($q) use ($request, $text) {
                $q->whereHas('designation', fn(Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$text%"));
            })
            ->when($request->filled('searchBybasic_salary'), function ($q) use ($request, $text) {
                $q->whereHas('payroll', fn(Builder $query) => $query->where('basic_salary', '>=', $text));
            })
            ->when($request->filled('searchBynet_salary'), function ($q) use ($request, $text) {
                $q->whereHas('payroll', fn(Builder $query) => $query->where('net_salary', '>=', $text));
            })

            ->paginate($request->perPage ?? 20);

        $data = $this->getPayslipstatus($data, $request);

        return $data;
    }

    public function getEmployeePayslipYear(Request $request)
    {

        $payroll = Payslips::where("company_id", $request->company_id)->where('employee_id', $request->employee_id)->where("year", $request["year"])->orderBy('month', 'asc')->get();
        foreach ($payroll as $key => $value) {

            $pdfFile_name = 'payslips/' . $request["company_id"] . '/' . $request["company_id"] . '_' . $value->employee_id . '_' . $value["month"] . '_' . $request["year"] . '_payslip.pdf';
            if (Storage::disk('local')->exists($pdfFile_name)) {
                $value->payslip_status = true;
            } else {
                $value->payslip_status = false;
            }

            $value->payroll_month = $value["month"];
            $value->payroll_year = $request["year"];
        }
        return $payroll;
        // $payroll = Payroll::where("company_id", $request->company_id)->where('employee_id', $request->employee_id)->get();

        // $payslips = [];
        // for ($i = 1; $i <= 12; $i++) {

        //     $row = [];

        //     $pdfFile_name = 'payslips/' . $request["company_id"] . '/' . $request["company_id"] . '_' . $request->employee_id . '_' . $i . '_' . $request["year"] . '_payslip.pdf';
        //     if (Storage::disk('local')->exists($pdfFile_name)) {
        //         $row['payslip_status'] = true;
        //     } else {
        //         $row['payslip_status'] = false;
        //     }
        //     $row['employee_id'] = $request->employee_id;
        //     $row['company_id'] = $request["company_id"];
        //     $row['payroll_month'] = $i;
        //     $row['payroll_year'] = $request["year"];
        //     $row['net_salary'] = 1000;
        //     $row['basic_salary'] = 1000;


        //     $payslips[] = $row;
        // }
        // $data['payroll'] = $payroll;
        // $data['payslips'] = $payslips;
        // return $data;
    }

    public function getPayslipstatus($data, $request)
    {
        if (isset($request["company_id"]) && $request["year"] && $request["month"]) {
            foreach ($data as $key => $value) {

                $pdfFile_name = 'payslips/' . $request["company_id"] . '/' . $request["company_id"] . '_' . $value->employee_id . '_' . $request["month"] . '_' . $request["year"] . '_payslip.pdf';
                if (Storage::disk('local')->exists($pdfFile_name)) {
                    $value->payslip_status = true;
                } else {
                    $value->payslip_status = false;
                }

                $value->payroll_month = $request["month"];
                $value->payroll_year = $request["year"];
            }
        }

        return $data;
    }

    public function attendance_employees(Employee $employee, Request $request)
    {
        //
        return Attendance::with('employeeAttendance')->get('employee_id');
    }

    public function show(Employee $employee)
    {
        return $employee->with(["reportTo", "schedule", "user", "department", "sub_department", "designation", "role"])->whereId($employee->id)->first();
    }
    public function getSingleEmployeeProfile($id)
    {
        return Employee::with(["company", "reportTo", "schedule", "user.branchLogin", "department", "sub_department", "designation", "role", "leave_group"])->whereId($id)->first();
    }
    public function getSingleEmployeeProfileAll()
    {
        return Employee::with(["company", "reportTo", "schedule", "user.branchLogin", "department", "sub_department", "designation", "role", "leave_group"])->offset(0)->limit(10)->get();
    }
    public function employeesList(Request $request)
    {
        $columns = $request->columns;
        $condition = gettype($columns) == "array" && !in_array("*", $columns) && count($columns) > 0 ? true : false;

        $model = Employee::query();
        $model->where(function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
        $model->when($condition, function ($q) use ($columns) {
            $q->select($columns);
            $q->withOut(["schedule", "department"]);
        });
        $model->orderBy("first_name", "asc");
        return $model->paginate(Employee::where('company_id', $request->company_id)->count());
    }

    public function employeeTodayAnnouncements(Request $request, $id)
    {
        return Employee::where("company_id", $request->company_id)->find($id)->announcements()->with("category")->where('start_date', '=', date("Y-m-d"))->paginate($request->per_page ?? 100);
    }

    public function employeeAnnouncements(Request $request, $id)
    {
        return Employee::where("company_id", $request->company_id)->find($id)->announcements()->with(["category", "user.employee", "user.employeeData", "user.company"])->paginate($request->per_page ?? 100);
    }


    public function employeesByDepartmentForAnnoucements(Request $request)
    {
        $model = Employee::query();

        $model->where('company_id', $request->company_id);

        if (!in_array("---", $request->department_ids)) {
            $model->whereIn("department_id", $request->department_ids);
        }

        $model->with("department", function ($q) use ($request) {
            $q->whereCompanyId($request->company_id);
        });

        return $model->paginate($request->per_page);
    }

    public function employeesByDepartment(Request $request)
    {
        $model = Employee::query();
        $model->whereHas('schedule', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
        $model->where('company_id', $request->company_id);

        if (!in_array("---", $request->department_ids)) {
            $model->whereIn("department_id", $request->department_ids);
        }

        $model->with("department", function ($q) use ($request) {
            $q->whereCompanyId($request->company_id);
        });
        $model->with("schedule", function ($q) use ($request) {
            $q->whereCompanyId($request->company_id);
        });
        return $model->paginate($request->per_page);
    }
    public function employeesBySubDepartment(Request $request)
    {
        $model = Employee::query();
        $model->whereHas('schedule', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
        $model->whereCompanyId($request->company_id);
        if (!in_array("---", $request->sub_department_ids)) {
            $model->whereIn("sub_department_id", $request->sub_department_ids);
        }

        return $model->whereIn("department_id", $request->department_ids)->paginate($request->per_page);
    }

    public function employeesByEmployeeId(Request $request)
    {
        $model = Employee::query();
        $model->where('company_id', $request->company_id);
        $model->where('employee_id', $request->employee_search);
        $model->whereHas('schedule', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
        return $model->paginate($request->per_page);
    }
    public function employeesByDesignation($id, Request $request)
    {
        $model = Employee::query();
        $model = $this->FilterCompanyList($model, $request);
        if ($id) {
            $model->whereDesignationId($id);
            $model->whereCompanyId($request->company_id);
        }
        return $model->select('id', 'first_name', 'last_name')->get();
    }
    public function designationsByDepartment($id, Request $request, Designation $model)
    {
        $model = $this->FilterCompanyList($model, $request);
        if ($id) {
            $model->whereDepartmentId($id);
            $model->whereCompanyId($request->company_id);
        }
        return $model->select('id', 'name')->get();
    }
    public function update(Request $request, $id)
    {
        $data = $request->except(['contact_name', 'contact_no', 'contact_position', 'contact_whatsapp', 'user_name', 'email', 'password_confirmation', 'password']);
        if (isset($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        if (isset($request->logo)) {
            $data['logo'] = saveFile($request, 'media/company/logo', 'logo', $request->name, 'logo');
        }
        DB::beginTransaction();
        try {
            $record = Company::find($id);
            $record->update($data);
            $company = $request->setContactFields();
            CompanyContact::where('company_id', $record->id)->update($company);
            $user = $request->setUserFields();
            if (@$request->password) {
                $user['password'] = Hash::make($request->password);
            }
            User::find($record->user_id)->update($user);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        return Response::json([
            'record' => Company::with(['contact'])->find($id),
            'message' => 'Company Successfully updated.',
            'status' => true,
        ], 200);
    }
    public function employeeContactUpdate(Employee $model, ContactRequest $request)
    {
        try {
            $model->find($request->employee_id)->update($request->validated());
            return Response::json([
                'message' => 'Contact Successfully updated.',
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function destroy($id)
    {
        try {
            $record = Employee::find($id);

            if (!$record) {
                return response()->json(['message' => 'No such record found.'], 404);
            }

            $user_id = $record->user_id;
            $employee_id = $record->employee_id;

            DB::transaction(function () use ($record, $user_id, $employee_id) {
                $record->delete();
                User::where('id', $user_id)->delete();
                ScheduleEmployee::where('employee_id', $employee_id)->delete();
            });

            return response()->json(['message' => 'Employee successfully deleted.', 'status' => true], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function search(Request $request, $key)
    {

        if ($request->datatable_column_filter && $request->datatable_column_filter == true) {

            $key = strtolower($key);

            return Employee::query()
                ->latest()
                ->with(["user.branchLogin", "department", "sub_department", "designation", "timezone"])
                ->where('company_id', $request->company_id)
                ->when($request->filled('department_id'), function ($q) use ($request) {
                    $q->whereHas('department', fn(Builder $query) => $query->where('department_id', $request->department_id));
                })
                ->when($request->filled('search_employee_id'), function ($q) use ($request, $key) {
                    //$q->where('employee_id', 'LIKE', "$key%");
                    $q->where(function ($q) use ($key) {
                        $q->Where('employee_id', env('WILD_CARD') ?? 'ILIKE', "$key%");
                        $q->orWhere('system_user_id', env('WILD_CARD') ?? 'ILIKE', "$key%");
                    });
                })
                ->when($request->filled('search_phone_number'), function ($q) use ($request, $key) {
                    $q->where('phone_number', 'LIKE', "$key%");
                })
                ->when($request->filled('search_employee_name'), function ($q) use ($request, $key) {
                    $q->where(function ($q) use ($key) {
                        $q->Where('first_name', env('WILD_CARD') ?? 'ILIKE', "$key%");
                        $q->orWhere('last_name', env('WILD_CARD') ?? 'ILIKE', "$key%");
                    });
                })

                ->when($request->filled('search_emailid'), function ($q) use ($request, $key) {
                    $q->where('local_email', 'LIKE', "$key%");
                })
                ->when($request->filled('search_department_name'), function ($q) use ($request, $key) {
                    $q->whereHas('department', fn(Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$key%"));
                    // $q->orWhereHas('sub_department', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$key%"));
                })
                ->when($request->filled('search_shiftname'), function ($q) use ($request, $key) {
                    $q->whereHas('schedule.shift', fn(Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$key%"));
                    $q->whereHas('schedule.shift', fn(Builder $query) => $query->whereNotNull('name'));
                    $q->whereHas('schedule.shift', fn(Builder $query) => $query->where('name', '<>', '---'));
                })
                ->when($request->filled('search_timezonename'), function ($q) use ($request, $key) {
                    $q->whereHas('timezone', fn(Builder $query) => $query->where('timezone_name', env('WILD_CARD') ?? 'ILIKE', "$key%"));
                })
                ->paginate($request->perPage ?? 20);
        } else {
            return Employee::query()
                ->latest()
                ->filter($key)
                ->with(["user", "department", "sub_department", "designation", "timezone"])
                ->where('company_id', $request->company_id)
                ->paginate($request->perPage ?? 20);
        }
    }
    public function scheduled_employees_search(Request $request, $input)
    {
        // $model = Employee::query();
        $model = ScheduleEmployee::query();
        $model->where('employee_id', $input);
        $model->where('company_id', $request->company_id);

        $model = $this->custom_with($model, "shift", $request->company_id);
        $model = $this->custom_with($model, "roster", $request->company_id);
        $model = $this->custom_with($model, "employee", $request->company_id);

        // return $model->whereHas('schedule', function ($q) use ($request) {
        //     $q->where('company_id', $request->company_id);
        // })

        return $model->paginate($request->perPage ?? 10);

        //  return $model->whereHas('schedule')->with(["reportTo", "schedule", "user", "department", "sub_department", "designation", "role"])->paginate($request->perPage ?? 10);
    }
    public function updateEmployee(EmployeeUpdateRequest $request, $id)
    {
        $data = $request->except(['user_name', 'email', 'password', 'password_confirmation']);
        $data['role_id'] = $request->role_id ?? 0;
        $employee = Employee::find($id);

        $user_arr = [
            'name' => $request->display_name,
            'email' => $request->email,
            'employee_role_id' => $request->role_id ?? 0,
            'role_id' => $request->role_id ?? 0,
        ];

        if ($request->password) {
            $user_arr['password'] = Hash::make($request->password);
        }

        User::where('id', $employee->user_id)->update($user_arr);

        // if ($request->role_id) {
        //     $user_arr['employee_role_id'] = $request->role_id;
        //     User::where('id', $employee->user_id)->update($user_arr);
        // }

        // $user = User::where('id', $employee->user_id)->update($user_arr);
        // if (isset($request->profile_picture)) {
        //     $arr['profile_picture'] = saveFile($request, 'media/employee/profile_picture', 'profile_picture', $request->name, 'profile_picture');
        // }
        if ($request->hasFile('profile_picture')) {
            //$profile_picture = $request->profile_picture->getClientOriginalName();
            $profile_picture = $id . '.jpg';
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $profile_picture);
            $product_image = url('media/employee/profile_picture/' . $profile_picture);
            $data['profile_picture'] = $profile_picture;
        }
        $employee->update($data);
        return Response::json([
            'record' => $employee,
            'message' => 'Employee Successfully Updated.',
            'status' => true,
        ], 200);
    }
    public function downloadEmployeePic(Request $request, $id, $employee_id)
    {
        // Define the path to the file in the public folder
        $filePath = public_path("media/employee/profile_picture/" . $id);

        // Check if the file exists
        if (file_exists($filePath)) {
            // Create a response to download the file
            return response()->download($filePath, $employee_id . ".jpg");
        } else {
            // Return a 404 Not Found response if the file doesn't exist
            abort(404);
        }
    }
    public function downloadEmployeeDocuments(Request $request, $employee_id, $file_name)
    {
        // Define the path to the file in the public folder
        $filePath = public_path("documents/" . $employee_id) .  '/' . $file_name;

        // Check if the file exists
        if (file_exists($filePath)) {
            // Create a response to download the file
            return response()->download($filePath, $file_name);
        } else {
            // Return a 404 Not Found response if the file doesn't exist
            abort(404);
        }
    }
    public function downloadEmployeeProfilepdfView(Request $request, $id)
    {



        $employeeProfile = $this->getSingleEmployeeProfile($id);
        return  View('pdf.employee_profile', ["employee" => $employeeProfile]);; //->donwload();
        return Pdf::loadView('pdf.employee_profile', ["employee" => $employeeProfile])->setPaper('A4', 'potrait')->download();; //->donwload();
    }
    public function downloadEmployeeProfilepdf(Request $request, $id)
    {

        $employeeProfile = $this->getSingleEmployeeProfile($id);
        //return  View('pdf.employee_profile', ["employee" => $employeeProfile]);; //->donwload();
        return Pdf::loadView('pdf.employee_profile', ["employee" => $employeeProfile])->setPaper('A4', 'potrait')->download();; //->donwload();
    }
    public function employeeLoginUpdate(Request $request, $id)
    {
        $arr = [];
        $arr["user_type"] = "employee";
        $arr["name"] = "null";
        $arr["email"] = $request->email;
        $arr["company_id"] = $request->company_id;
        $arr["employee_role_id"] = $request->employee_role_id;
        $arr["role_id"] = $request->employee_role_id ?? 0;


        $isEmailExist = User::with(["employee"])->where("id", '!=',  $id)->where("email",   $request->email)->get();


        if (count($isEmailExist) > 0) {

            //return ["status" => false, "errors" => ["email" => ['Employee Email is already exist  ']]];
            if ($isEmailExist[0]->employee) {
                $name = $isEmailExist[0]->employee->first_name  . ' ' . $isEmailExist[0]->employee->last_name;
                return [
                    "status" => false,
                    "errors" => ["email" => ['Employee Email is already exist with Name:' . $name]]

                ];

                //return $this->response('Employee Email is already exist with Name:' . $isEmailExist[0]->employeeData->first_name ?? '' . ' ' . $isEmailExist[0]->employeeData->last_name ?? '', null, false);
            } else {
                //   return $this->response('Employee Email is already exist ', null, false);
                return ["status" => false, "errors" => ["email" => ['Employee Email is already exist  ']]];
            }
        }
        if ($request->password != '' || $request->password != "********") {
            $arr['password'] = Hash::make($request->password ?? "secret");
        }

        try {
            $user = User::updateOrCreate(['id' => $id], $arr);
            Employee::where("id", $request->employee_id)->update(["user_id" => $user->id]);

            if (!$user) {
                return $this->response('Employee cannot update.', null, false);
            }

            return $this->response('Employee successfully updated.', $user->id, true);
        } catch (\Throwable $th) {
            return $this->response('Employee cannot update.', $th, false);
            throw $th;
        }
    }

    public function employeeRFIDUpdate(Request $request, $id)
    {



        // $validatedData = $request->validate([
        //     'rfid_card_number' => 'required|max:15',

        //     'rfid_card_password' => 'required|min:4|max:8',

        // ]);

        //if ($validatedData) 

        // if ($request->rfid_card_number == '' ||  $request->rfid_card_password == '') {

        //     return $this->response('RFID card and password are required', null, false);
        // }
        $data = [];

        $updateData = true;
        if ($request->rfid_card_number != '') {

            $isRFIdExist = Employee::where("id", '!=',  $request->employee_id)->where("rfid_card_number",   $request->rfid_card_number)->get();

            if (count($isRFIdExist) > 0) {

                return $this->response('Error: RFID number is already assigned Employee Name :' . $isRFIdExist[0]["first_name"] . ', EmpId: ' . $isRFIdExist[0]['employee_id'], null, false);
            }
        }

        try {
            $data['rfid_card_number'] = $request->rfid_card_number;
            $data['rfid_card_password'] = $request->rfid_card_password;
            if (count($data)) {
                $user = Employee::where("id",   $request->employee_id)->update($data);

                if (!$user) {
                    return $this->response('Employee cannot update.', null, false);
                }
            } else {
                return $this->response('Employee successfully updated.', null, true);
            }
            return $this->response('Employee successfully updated.', null, true);
        } catch (\Throwable $th) {
            throw $th;
        }





        $isRFIdExist = Employee::where("id", '!=',  $request->employee_id)->where("rfid_card_number",   $request->rfid_card_number)->get();

        if (count($isRFIdExist) == 0) {

            try {

                if (count($data)) {
                    $user = Employee::where("id",   $request->employee_id)->update($data);

                    if (!$user) {
                        return $this->response('Employee cannot update.', null, false);
                    }
                } else {
                    return $this->response('Employee successfully updated.', null, true);
                }
                return $this->response('Employee successfully updated.', null, true);
            } catch (\Throwable $th) {
                throw $th;
            }
        } else {
            if ($request->rfid_card_number != '')
                return $this->response('Error: RFID number is already assigned Employee Name :' . $isRFIdExist[0]["first_name"] . ', EmpId: ' . $isRFIdExist[0]['employee_id'], null, false);
        }

        return $this->response('Employee successfully updated.', null, true);
    }

    public function updateContact(Employee $model, EmployeeUpdateContact $request, $id)
    {
        // return $request->all();
        $model->whereId($id)->update($request->all());
        return Response::json([
            'record' => $model,
            'message' => 'Contact successfully Updated.',
            'status' => true,
        ], 200);
    }
    public function updateOther(Employee $model, EmployeeOtherRequest $request, $id): JsonResponse
    {
        $data = $request->except(['sub_department_id']);
        $model->whereId($id)->update($data);
        return Response::json([
            'record' => $model,
            'message' => 'Other details successfully Updated.',
            'status' => true,
        ], 200);
    }
    public function import(Request $request)
    {


        $file = $request->file('employees');
        $rowCount = file($file);

        $this->company_id = $request->company_id ?? 0;
        $branch_id = $request->branch_id ?? 0;

        $company = Company::withCount('employees')->find($this->company_id);
        $totalEmployee = $company->employees_count ?? 0;
        $maxEmployee = $company->max_employee ?? 0;
        $remainingEmployee = max(0, (int) $maxEmployee - (int) $totalEmployee);

        if (!(count($rowCount) - 1 <= $remainingEmployee)) {
            return ["status" => false, "errors" => ["Employee limit reached. Maximum limit is " . $maxEmployee]];
        }

        $dataCSV = $this->saveFile($file);

        if (is_array($dataCSV) && !$dataCSV["status"]) {
            return ["status" => false, "errors" => $dataCSV["errors"]];
        }

        $dataCSV = $this->csvParser($dataCSV);

        if (array_key_exists("status", $dataCSV)) {
            return ["status" => false, "errors" => $dataCSV["errors"]];
        }
        $success = false;

        DB::beginTransaction();
        try {
            foreach ($dataCSV as $data1) {
                $data = [];
                foreach ($data1 as $key => $value) {
                    $data[$key] = trim($value);
                }


                $validator = $this->validateImportData($data);
                if (!$this->checkIfDepartmentExist($data['department_code'])) {
                    return [
                        "status" => false,
                        "errors" => ["Department code ({$data['department_code']}) does not exist"],
                    ];
                }

                if ($validator->fails()) {
                    return [
                        "status" => false,
                        "errors" => $validator->errors()->all(),
                    ];
                }
                $imageName = '';

                $employee = [
                    'title' => trim($data['title']),
                    'display_name' => trim($data['display_name']),
                    'first_name' => trim($data['first_name']),
                    'last_name' => trim($data['last_name']),
                    'employee_id' => trim($data['employee_id']),
                    'company_id' => $this->company_id,
                    'system_user_id' => trim($data['employee_device_id']),
                    'department_id' => trim($data['department_code']),
                    'branch_id' => trim($branch_id),

                ];

                if ($data['profile_picture'] != '') {
                    if (file_exists($data['profile_picture'])) {
                        $imageName = (time() + rand(10000, 20000)) . ".png";
                        $newFileLocation = public_path('media/employee/profile_picture/') . '/' . $imageName;
                        copy($data['profile_picture'], $newFileLocation);

                        $employee["profile_picture"] = trim($imageName);
                    }
                }



                $record = null;

                if ($data['email'] != "") {
                    $record = User::create([
                        "user_type" => "employee",
                        'name' => 'null',
                        'email' => $data['email'],
                        'password' => Hash::make('secret'),
                        'company_id' => $this->company_id,
                    ]);

                    $employee['user_id'] = $record->id;
                }

                $success = Employee::create($employee) ? true : false;



                (new AttendanceController)->seedDefaultData($employee["company_id"], [$employee['system_user_id']], $branch_id);
            }

            if ($success) {
                DB::commit();
            }

            $msg = $success ? 'Employee imported successfully.' : 'Employee cannot import.';

            return $this->response($msg, null, true);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public function deleteEmployeeFromDevice(Request $request)
    {

        if ($request->system_user_id != '' && $request->device_id) {
            $preparedJson = [
                "userCodeArray" => [$request->system_user_id],
            ];

            try {
                (new SDKController)->processSDKRequestJobDeletePersonJson($request->device_id, $preparedJson);
            } catch (\Throwable $th) {
            }
        }
    }
    public function  defaultAttendanceForMissingScheduleIds(Request $request)
    {
        $company_id = $request->company_id;
        $system_user_id = $request->system_user_id;
        $date = $request->date;
        $this->AttendanceForMissingScheduleIds($company_id, $system_user_id, $date);
    }
    public function AttendanceForMissingScheduleIds($company_id, $system_user_id, $date)
    {



        //$company_id = $request->company_id;
        //$system_user_id = $request->system_user_id;
        //$date = $request->date;
        $daysInMonth = Carbon::now()->month(date('m', strtotime($date)))->daysInMonth;
        $employees = Employee::query();

        $employees->with(["schedule_active" => function ($q) use ($company_id, $date) {
            $q->where("company_id", $company_id);
            $q->where("company_id", $company_id);
            $q->where("to_date", ">=", $date);

            $q->withOut("shift_type");
            // $q->select("shift_id", "isOverTime", "employee_id", "shift_type_id", "shift_id", "shift_id");
            $q->orderBy("to_date", "asc");
        }]);


        $employeesEmptyShiftids = Attendance::where('date', ">=", date("Y-m-", strtotime($date)) . sprintf("%02d",  01))
            ->where('date', "<=", date("Y-m-", strtotime($date)) . sprintf("%02d",  $daysInMonth))
            ->where(function ($query) {
                $query->where("shift_id", null)
                    ->Orwhere("shift_id", 0);
            })
            ->where("company_id", $company_id)
            ->when($system_user_id > 0, function ($q) use ($system_user_id) {
                $q->where("employee_id", $system_user_id);
            })->pluck("employee_id");



        $employees->whereIn("system_user_id", $employeesEmptyShiftids);

        $employees->where("company_id", $company_id);
        if ($system_user_id > 0)
            $employees = $employees->where("system_user_id", $system_user_id);



        $employees = $employees->get();



        $data = [];

        foreach ($employees as $employee) { {



                //  $attendaceExistDates = array_column(json_decode($employee->attendances, true), 'edit_date'); 


                foreach (range(1, $daysInMonth) as $day) {
                    $date = date("Y-m-", strtotime($date)) . sprintf("%02d",  $day);
                    $attendance = Attendance::where("company_id", $company_id);
                    $attendanceRow = $attendance->where("employee_id", $employee->system_user_id)->where("shift_id", null)->where("date", $date)->get();
                    $count = $attendanceRow->count();

                    if ($count == 1 && $employee->system_user_id != '') {




                        $data[] =  $updateData  = [

                            "employee_id" => $employee->system_user_id,
                            "shift_id" => $employee->schedule_active->shift_id,
                            "shift_type_id" => $employee->schedule_active->shift_type_id,

                            "created_at" => date('Y-m-d H:i:s'),
                            "updated_at" => date('Y-m-d H:i:s'),
                        ];

                        Attendance::where("id", $attendanceRow[0]->id)->update($updateData);
                    }
                }
            }
        }




        return "Successfully Updated " . count($data);
    }
    public function defaultAttendanceForMissing(Request $request)
    {



        $company_id = $request->company_id;
        $system_user_id = $request->system_user_id;
        $date = $request->date;
        $daysInMonth = Carbon::now()->month(date('m', strtotime($date)))->daysInMonth;
        $employees = Employee::query();

        $employees->with(["schedule_active" => function ($q) use ($request, $date) {
            $q->where("company_id", $request->company_id);
            $q->where("company_id", $request->company_id);
            $q->where("to_date", ">=", $date);

            $q->withOut("shift_type");
            // $q->select("shift_id", "isOverTime", "employee_id", "shift_type_id", "shift_id", "shift_id");
            $q->orderBy("to_date", "asc");
        }]);

        // $employees->whereHas('attendances', fn (Builder $query) => $query->where('date', ">=", date("Y-m-") . "1")->where('date', "<=", date("Y-m-") .  $daysInMonth));
        $employees->where("company_id", $company_id);
        if ($system_user_id)
            $employees = $employees->where("system_user_id", $system_user_id);

        $employees = $employees->get();



        $data = [];

        foreach ($employees as $employee) {

            //  $attendaceExistDates = array_column(json_decode($employee->attendances, true), 'edit_date'); 


            // foreach (range(1, $daysInMonth) as $day)
            {
                //$date = date("Y-m-", strtotime($date)) . sprintf("%02d",  $day);
                $attendance = Attendance::where("company_id", $company_id);
                $count = $attendance->where("employee_id", $employee->system_user_id)->where("date", $date)->get()->count();


                if ($count == 0 && $employee->system_user_id != '') {




                    $data[] = [
                        "date" =>  $date,
                        "employee_id" => $employee->system_user_id,
                        "shift_id" => $employee->schedule_active->shift_id,
                        "shift_type_id" => $employee->schedule_active->shift_type_id,
                        "status" => "A",
                        "in" => "---",
                        "out" => "---",
                        "total_hrs" => "---",
                        "ot" => "---",
                        "late_coming" => "---",
                        "early_going" => "---",
                        "device_id_in" => "---",
                        "device_id_out" => "---",
                        "company_id" => $company_id,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }


        $attendance = Attendance::query();
        $attendance->insert($data);

        return "Successfully Inserted " . count($data);
    }
    public function validateImportData($data)
    {
        if (isset($data["employee_device_id"]))
            $data["system_user_id"] = $data["employee_device_id"];

        $employee = [
            "employee_id" => $data["employee_id"],
            "company_id" => $this->company_id,
        ];

        $employeeDevice = [
            "system_user_id" => $data["system_user_id"],
            "company_id" => $this->company_id,
        ];

        $rules = [
            'title' => ['required', 'in:Mr,Mrs,Miss,Ms,Dr'],
            'employee_id' => ['required', $this->uniqueRecord("employees", $employee)],
            'system_user_id' => ['required', $this->uniqueRecord("employees", $employeeDevice)],
            'display_name' => ['required', 'min:3', 'max:10'],
            'email' => 'nullable|min:3|max:191|unique:users',
            'department_code' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
        ];

        $messages = [
            'title.in' => "Invalid title. Valid titles are (Mr,Mrs,Miss,Ms,Dr)",
            'system_user_id.required' => "The employee device id is required",
            'system_user_id.unique' => "The employee device id (" . $data["system_user_id"] . ") has already been taken",
            'employee_id.unique' => "The employee id (" . $data["employee_id"] . ") has already been taken",
            'email.unique' => "The employee email (" . $data["email"] . ") has already been taken",
            'first_name.required' => "The employee First Name required.",
            'last_name.required' => "The employee Last Name required.",
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function saveFile($file)
    {

        $filename = $file->getClientOriginalName();
        // if ($filename != "employees.csv") {
        //     return [
        //         "status" => false,
        //         "errors" => ["wrong file " . $filename . " (valid file is employees.csv)"],
        //     ];
        // }
        $filename = "employees.csv";
        $file->move("upload", $filename);
        return public_path("upload/" . $filename);
    }
    public function csvParser($filepath)
    {
        $columns = [
            "title",
            "employee_id",
            "employee_device_id",
            "display_name",
            "first_name",
            "last_name",
            "email",
            "department_code",
            "profile_picture",
        ];
        $header = null;
        $data = [];
        if (($filedata = fopen($filepath, "r")) !== false) {
            while (($row = fgetcsv($filedata, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                    // dd($row);
                    if ($header != $columns) {
                        return [
                            "status" => false,
                            "errors" => ["header mismatch"],
                        ];
                    }
                } else {
                    if (count($header) != count($row)) {
                        return [
                            "status" => false,
                            "errors" => ["column mismatch"],
                        ];
                    }

                    $data[] = array_combine($header, $row);
                }
            }
            fclose($filedata);
        }
        if (count($data) == 0) {
            return [
                "status" => false,
                "errors" => "file is empty",
            ];
        }
        // dd($data);
        return $data;
    }

    public function checkIfDepartmentExist($department_code)
    {
        return Department::where(["id" => $department_code])->exists();
    }
    public function employeeUpdateSetting(Request $request)
    {
        $data = $request->only(['overtime', 'status', 'lockDevice', 'leave_group_id', 'reporting_manager_id']);

        $model = Employee::where('employee_id', $request->employee_id)
            ->where('company_id', $request->company_id)
            ->first();

        // $data["timezone_id"] = $request->status == 1 ? 1 : 64;

        // $timezoneData = [
        //     "snList" => Device::where("company_id", $request->company_id)->where("status_id", 1)->pluck("device_id"),
        //     "personList" => [
        //         [
        //             "name" => $model->first_name,
        //             "userCode" =>  $model->system_user_id,
        //             "timeGroup" => $request->status == 1 ? 1 : 64
        //         ]
        //     ],
        // ];

        // $this->SDKCommand(env("SDK_URL") . "/Person/AddRange", $timezoneData);

        $model->update($data);

        $users = User::where('id', $request->user_id);



        $users->update([
            'web_login_access' => $request->web_login_access ?? 0,
            'mobile_app_login_access' => $request->mobile_app_login_access ?? 0,
            'enable_whatsapp_otp' => $request->enable_whatsapp_otp ?? 0,
            'tracking_status' => $request->tracking_status ?? 0
        ]);

        return response()->json(['status' => true, 'message' => 'Setting successfully updated']);
    }

    public function updateEmployeeTimezone($status, $model, $company_id)
    {

        $data = [
            "snList" => Device::where("company_id", $company_id)->where("status_id", 1)->pluck("device_id"),
            "personList" => [
                [
                    "name" => $model->first_name,
                    "userCode" =>  $model->system_user_id,
                    "timeGroup" => $status == 1 ? 1 : 64
                ]
            ],
        ];

        return $this->SDKCommand(env("SDK_URL") . "/Person/AddRange", $data);
    }

    public function employeeToReporter(Request $request, $id)
    {
        $model = Employee::Find($id);
        $model->reportTo()->attach($request->report_id);
        return response()->json(['status' => true, 'message' => 'Reporter successfully Assigned']);
    }
    public function employeeReporters()
    {
        $ids = DB::table('employee_report')->pluck('employee_id');
        return Employee::with('reportTo')->whereIn('id', $ids)->paginate();
    }
    public function getReporterByEmployee($id)
    {
        $emp = Employee::find($id);
        return $emp->reportTo;
    }
    public function employeeRemoveReporter(Request $request, $id)
    {
        DB::table('employee_report')
            ->where('employee_id', $id)
            ->where('report_id', $request->report_id)
            ->delete();
        return response()->json(['status' => true, 'message' => 'Reporter successfully Deleted']);
    }

    // public function employeeRemoveReporter($id)
    // {
    //     DB::table('employee_report')->where('employee_id', $id)->delete();
    //     return response()->json(['status' => true, 'message' => 'Reporter successfully Deleted']);
    // }

    public function dumpEmployeesData()
    {
        $fullPath = storage_path("app/mycsv.csv");

        if (!file_exists($fullPath)) {
            return ["error" => true, "message" => 'File doest not exist.'];
        }

        $data = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $records = [];

        foreach ($data  as $row) {
            $columns = explode(',', $row);
            $records[] = [
                "title" => $columns[0],
                "first_name" => $columns[1],
                "last_name" => $columns[2],
                "display_name" => $columns[3],
                "employee_id" => $columns[4],
                "system_user_id" => $columns[5],
                "company_id" => 22
            ];
        }

        Employee::insert($records);
    }

    public function mapEmployeesData(Request $request)
    {
        $count = 0;
        foreach ($request->all() as $json) {
            $count += Employee::where('company_id', $json["company_id"])
                ->where('system_user_id', $json["system_user_id"])
                ->update(["branch_id" => $json["branch_id"]]);
        }

        return $count;

        // 92 + 95 + 88 + 151

        // Employee::update($records);
    }

    public function employeesShortList(Request $request)
    {
        $model = Employee::query();
        $model->where('company_id', $request->company_id);
        $model->when(request()->filled("department_id"), function ($q) {
            $q->where('department_id', request()->filled("department_id"));
        });
        $model->with("branch_test");
        $model->withOut(["branch", "department", "schedule", "designation", "sub_department", "user"]);
        $result = $model->get(["title", "display_name", "first_name", "last_name", "employee_id", "system_user_id", "department_id", "branch_id"])->toArray();
        return ($result);
    }

    public function employeesDepartmentMapping(Request $request, $id)
    {
        // return $request->all();
        // Define an associative array of system_user_id => department_id pairs

        // $userDepartments = [];

        // foreach ($request->all() as $item) {
        //     $userDepartments[$item['system_user_id']] = (string) $item['department_id'];
        // }

        $userDepartments =  $request->all();

        // return $userDepartments;

        // Define the chunk size for batch processing
        $chunkSize = 50;
        $resultCount = 0;

        // Chunk the array into smaller parts for batch processing
        $chunks = array_chunk($userDepartments, $chunkSize, true);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                Employee::where("company_id", $id)
                    ->where('system_user_id', $row['system_user_id'])
                    ->update(["department_id" => $row['department_id']]);
            }

            $resultCount += $chunkSize;
        }

        // Optionally, you can return or do something after the update loop
        return "$resultCount records has been updated";
    }

    public function employeeStoreFromDevice(StoreRequestFromDevice $request)
    {
        $employeeData = $request->validated();
        $employeeData["display_name"] = $employeeData["full_name"];
        $nameAsArray = explode(" ", $employeeData["full_name"], 2);
        $employeeData["first_name"] = $nameAsArray[0];
        $employeeData["last_name"] = $nameAsArray[1] ?? "";

        // Save profile picture if available
        if (!empty($employeeData["profile_picture"])) {
            try {
                $employeeData["profile_picture"] = $this->saveProfilePicture($employeeData);
            } catch (\Exception $e) {
                $results[] = [
                    'status' => false,
                    'message' => 'Failed to save profile picture: ' . $e->getMessage(),
                    'employee' => $employeeData
                ];
            }
        }

        $results = [];

        try {
            DB::transaction(function () use ($employeeData) {
                $company_id = $employeeData["company_id"];
                $fp = $employeeData["fp"];
                $palm = $employeeData["palm"];
                $system_user_id = $employeeData["system_user_id"]; // Assuming this is unique for each employee

                unset($employeeData["fp"]);
                unset($employeeData["palm"]);

                // Find existing employee by system_user_id, or create a new one
                $employee = Employee::updateOrCreate(
                    ['system_user_id' => $system_user_id],
                    $employeeData
                );

                // Delete existing fingerprints and palms for this employee
                FingerPrint::where("employee_id", $employee->id)->delete();
                Palm::where("employee_id", $employee->id)->delete();

                // Prepare new fingerprint and palm data
                $fpArray = [];
                foreach ($fp as $value) {
                    $fpArray[] = [
                        "fp" => $value,
                        "employee_id" => $employee->id,
                    ];
                }

                $palmArray = [];
                foreach ($palm as $value) {
                    $palmArray[] = [
                        "palm" => $value,
                        "employee_id" => $employee->id,
                    ];
                }

                // Insert new fingerprint and palm data
                FingerPrint::insert($fpArray);
                Palm::insert($palmArray);

                // Seed default attendance data for the company and employee
                (new AttendanceController)->seedDefaultData($company_id, [$employee->system_user_id]);
            });

            return $this->response("Employee successfully created or updated.", true, true);
        } catch (\Exception $e) {
            // Rollback is automatically handled by DB::transaction() in case of exception
            return $this->response("An error occurred: " . $e->getMessage(), false, false);
        }
    }

    public function employeeUpdateFromDevice(UpdateRequestFromDevice $request, $id)
    {
        $employeeData = $request->validated();
        $employeeData["display_name"] = $employeeData["full_name"];
        $nameAsArray = explode(" ", $employeeData["full_name"], 2);
        $employeeData["first_name"] = $nameAsArray[0];
        $employeeData["last_name"] = $nameAsArray[1] ?? "";

        // Save profile picture if available
        if (!empty($employeeData["profile_picture"])) {
            try {
                $employeeData["profile_picture"] = $this->saveProfilePicture($employeeData);
            } catch (\Exception $e) {
                $results[] = [
                    'status' => false,
                    'message' => 'Failed to save profile picture: ' . $e->getMessage(),
                    'employee' => $employeeData
                ];
            }
        }


        $results = [];
        $fpArray = [];
        $palmArray = [];

        foreach ($employeeData["fp"] as $value) {
            $fpArray[] = [
                "fp" => $value,
                "employee_id" => $id
            ];
        }

        foreach ($employeeData["palm"] as $value) {
            $palmArray[] = [
                "palm" => $value,
                "employee_id" => $id
            ];
        }


        unset($employeeData["fp"]);
        unset($employeeData["palm"]);

        try {
            DB::transaction(function () use ($employeeData, $palmArray, $fpArray, $id) {
                $company_id = $employeeData["company_id"];

                Employee::where("id", $id)->update([
                    'full_name' => $employeeData["full_name"],
                    'company_id' => $employeeData["company_id"],
                    'profile_picture' => $employeeData["profile_picture"],
                    'rfid_card_number' => $employeeData["rfid_card_number"] ?? null,
                    'rfid_card_password' => $employeeData["rfid_card_password"] ?? null,
                ]);

                $fingerPrintCount = FingerPrint::where("employee_id", $id)->count();

                if ($fingerPrintCount > 0) {
                    FingerPrint::where("employee_id", $id)->delete();
                }
                $palmCount = Palm::where("employee_id", $id)->count();
                if ($palmCount > 0) {
                    Palm::where("employee_id", $id)->delete();
                }

                FingerPrint::insert($fpArray);
                Palm::insert($palmArray);
            });

            return $this->response("All employees successfully created.", true, true);
        } catch (\Exception $e) {
            // Rollback is automatically handled by DB::transaction() in case of exception
            return $this->response("An error occurred: " . $e->getMessage(), false, false);
        }
    }

    /**
     * Save the base64 profile picture to the server.
     *
     * @param string $base64Image
     * @return string The saved image name
     */
    private function saveProfilePicture($employeeData)
    {
        // Decode and save the base64 image
        $imageData = base64_decode($employeeData["profile_picture"]);

        if ($imageData === false) {
            throw new \Exception('Invalid base64 image data');
        }

        $imageName = $employeeData["system_user_id"] . time() . '.png';
        $publicDirectory = public_path('media/employee/profile_picture');

        // Ensure directory exists
        if (!file_exists($publicDirectory)) {
            mkdir($publicDirectory, 0777, true);
        }

        file_put_contents($publicDirectory . '/' . $imageName, $imageData);

        return $imageName;
    }
}
