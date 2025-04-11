<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleEmployee\StoreRequest;
use App\Http\Requests\ScheduleEmployee\UpdateRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Roster;
use App\Models\ScheduleEmployee;
use App\Models\ShiftType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ScheduleEmployeeController extends Controller
{

    public function index(Request $request, ScheduleEmployee $model)
    {
        return $model
            ->where('company_id', $request->company_id)
            ->with("shift_type", "shift", "employee")
            ->paginate($request->per_page);
    }
    public function employeesWithScheduleCount(Request $request)
    {
        $model = Employee::with(["branch", "sub_department",  "department.branch", "sub_department", "schedule"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->with(["schedule.shift:id,name"]);

        $model->with([
            'schedule_active.shift' => function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            }
        ]);
        $model->when($request->filled('common_search'), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->Where('system_user_id', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
                $q->orWhere('employee_id', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
                $q->orWhere('first_name', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
                $q->orWhere('last_name', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
                $q->orWhere('full_name', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
                $q->orWhere('phone_number', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
                $q->orWhere('local_email', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");

                $q->orWhereHas('branch', fn (Builder $query) => $query->where('branch_name', env('WILD_CARD') ?? 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
                $q->orWhereHas('department', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
                // $q->orWhereHas('schedule.shift', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id)->where('company_id', $request->company_id));

                $q->orWhereHas('schedule_active.shift', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE',  "$request->common_search%")

                    ->where('company_id', $request->company_id));
            });
        });
        // $model->when($request->filled('common_search'), function ($q) use ($request) {
        //     $q->Where('system_user_id', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
        //     $q->orWhere('employee_id', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
        //     $q->orWhere('first_name', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
        //     $q->orWhere('last_name', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
        //     $q->orWhere('full_name', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
        //     $q->orWhere('phone_number', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");
        //     $q->orWhere('local_email', env('WILD_CARD') ?? 'ILIKE', "%$request->common_search%");

        //     $q->orWhereHas('branch', fn (Builder $query) => $query->where('branch_name', env('WILD_CARD') ?? 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
        //     $q->orWhereHas('department', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id));
        //     $q->orWhereHas('schedule.shift', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->common_search%")->where('company_id', $request->company_id)->where('company_id', $request->company_id));

        //     $q->WhereHas('schedule_active.shift', fn (Builder $query) => $query->where('name',  "$request->common_search")

        //         ->where('company_id', $request->company_id));
        //     // $q->whereHas('schedule_active', function ($q) use ($request) {
        //     //     $q->where('company_id', $request->company_id);
        //     // });
        //     // $q->whereHas('schedule_active.shift', function ($q) use ($request) {


        //     //     $q->where('name', "$request->common_search");
        //     //     $q->where('company_id', $request->company_id);
        //     // });
        // });









        if ($request->department_ids) {
            if (!in_array("---", $request->department_ids)) {
                $model->whereIn("department_id", $request->department_ids);
            }


            $model->with("department", function ($q) use ($request) {
                $q->whereCompanyId($request->company_id);
            });
            $model->with("sub_department", function ($q) use ($request) {
                $q->whereCompanyId($request->company_id);
            });

            $model->with("schedule", function ($q) use ($request) {
                $q->whereCompanyId($request->company_id);
            });
        }



        //$model->has('schedule_active.shift_type_id', '>', 2);

        $model->with(["schedule_all" => function ($q) use ($request) {
            $q->where("company_id", $request->company_id);
        }]);

        if ($request->filled('schedules_count')) {
            if ($request->schedules_count == 0) {
                $model->whereDoesntHave('schedule_active', function ($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                });
            } elseif ($request->schedules_count == 1) {
                $model->whereHas('schedule_active', function ($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                });
            }
        }



        $model->with(["schedule" => function ($q) use ($request) {
            $q->where("company_id", $request->company_id);
            $q->where("to_date", ">=", date('Y-m-d'));


            // $q->select("shift_id", "isOverTime", "employee_id", "shift_type_id", "shift_id", "shift_id");
            $q->orderBy("to_date", "asc");
        }]);


        $model->when($request->filled('first_name'), function ($q) use ($request) {


            $q->Where(function ($q) use ($request) {
                $q->where('first_name', env('WILD_CARD') ?? 'ILIKE', "$request->first_name%");
                $q->orWhere('last_name', env('WILD_CARD') ?? 'ILIKE', "$request->first_name%");
            });
        });

        $model->when($request->filled('employee_id'), function ($q) use ($request) {


            $q->Where(function ($q) use ($request) {
                $q->where('employee_id', env('WILD_CARD') ?? 'ILIKE', "$request->employee_id%");
                $q->orWhere('system_user_id', env('WILD_CARD') ?? 'ILIKE', "$request->employee_id%");
            });
        });


        $model->without(["user"]);




        return   $model->paginate($request->per_page);
    }



    public function employees_by_departments(Request $request)
    {
        // return $request->all();
        return Employee::select("first_name", "system_user_id", "employee_id", "department_id", "display_name")
            ->withOut(["user", "sub_department", "sub_department", "designation", "role", "schedule"])
            ->whereIn('department_id', $request->department_ids)
            ->where('company_id', $request->company_id)
            ->get();
    }

    public function store(StoreRequest $request, ScheduleEmployee $model)
    {
        $data = $request->validated();

        $arr = [];

        foreach ($data["employee_ids"] as $item) {

            if ($item) {
                foreach ($data["schedules"] as $shift) {


                    $value = [
                        "isAutoShift" => array_key_exists("isAutoShift", $shift) && $shift["isAutoShift"] && $shift["shift_id"] == 0 ? 1 : 0,
                        "shift_id" => array_key_exists("isAutoShift", $shift) && $shift["isAutoShift"] && $shift["shift_id"] == 0 ? 0 :  $shift["shift_id"],
                        "shift_type_id" => array_key_exists("isAutoShift", $shift) && $shift["isAutoShift"] && $shift["shift_id"] == 0 ? 0 : 2,
                        "isOverTime" => $shift["is_over_time"],
                        "employee_id" => $item,
                        "from_date" => $shift["from_date"],
                        "to_date" => $shift["to_date"],
                        "company_id" => $data["company_id"],
                        "branch_id" => $data["branch_id"] ?? 0,
                    ];
                    $arr[] = $value;
                }
            }


            // if (!$found) {
            //     $arr[] = $value;
            // }
        }

        try {
            if ($request->replace_schedules) {
                $model = ScheduleEmployee::query();
                $model->where("company_id", $data["company_id"]);
                // $model->where("branch_id", $data["branch_id"] ?? 0);
                $model->whereIn("employee_id", array_column($arr, "employee_id"));
                // $model->whereIn("shift_type_id", array_column($arr, "shift_type_id"));
                // $model->whereIn("shift_id", array_column($arr, "shift_id"));
                // $model->where("from_date", array_column($arr, "from_date"));
                // $model->where("to_date", array_column($arr, "to_date"));
                $model->delete();
            }
            $result = $model->insert($arr);

            if ($result) {
                return $this->response('Schedule Employee successfully added.', null, true);
            } else {
                return $this->response('Schedule Employee cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(ScheduleEmployee $ScheduleEmployee)
    {
        return $ScheduleEmployee;
    }

    public function update(UpdateRequest $request, $id)
    {
        try {

            $data = $request->validated();
            $data['updated_at'] = date('Y-m-d H:i:s');
            $record = ScheduleEmployee::where('employee_id', $id)->update($request->validated($data));
            if ($record) {
                return response()->json(['status' => true, 'message' => 'Schedule Employee successfully updated']);
            } else {
                return response()->json(['status' => false, 'message' => 'Schedule Employee cannot update']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function schedule_employees_delete(Request $request)
    {

        $record = ScheduleEmployee::where("company_id", $request->company_id)
            ->where("employee_id", $request->employee_id)
            //->where("branch_id", $request->branch_id)
            ->delete();

        try {
            if ($record) {
                return $this->response('Employee Schedule deleted.', null, true);
            } else {
                return $this->response('Employee Schedule cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id, Request $request)
    {


        $record = ScheduleEmployee::where("employee_id", $id)->delete();

        $record = ScheduleEmployee::where("company_id", $request->company_id)
            ->where("employee_id", $request->employee_id)
            //->where("branch_id", $request->branch_id)
            ->delete();

        try {
            if ($record) {
                return $this->response('Employee Schedule deleted.', null, true);
            } else {
                return $this->response('Employee Schedule cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteSelected(Request $request)
    {
        $record = ScheduleEmployee::whereIn('id', $request->ids)->delete();
        if ($record) {
            return response()->json(['status' => true, 'message' => 'ScheduleEmployee Successfully Deleted']);
        } else {
            return response()->json(['status' => false, 'message' => 'ScheduleEmployee cannot Deleted']);
        }
    }

    public function deleteAll(Request $request)
    {
        $record = ScheduleEmployee::whereCompanyId($request->company_id)->whereIn('id', $request->ids);

        if ($record->delete()) {
            return response()->json(['status' => true, 'message' => 'ScheduleEmployee Successfully Deleted']);
        } else {
            return response()->json(['status' => false, 'message' => 'ScheduleEmployee cannot Deleted']);
        }
    }

    public function assignSchedule()
    {
        $companyIds = Company::pluck("id");

        if (count($companyIds) == 0) {
            return "No Record found.";
        }

        $currentDate = date('Y-m-d');

        $currentDay = date("D", strtotime($currentDate));

        $arrays = [];

        $str = "";

        $date = date("Y-m-d H:i:s");
        $script_name = "AssignScheduleToEmployee";

        $meta = "[$date] Cron: $script_name.";

        foreach ($companyIds as $company_id) {

            $no_of_employees = 0;

            $model = ScheduleEmployee::query();

            $model->where("company_id", $company_id);

            $model->where(function ($q) use ($currentDate) {
                $q->where('from_date', '<=', $currentDate)
                    ->where('to_date', '>=', $currentDate);
            });

            $model->with(["roster"]);

            $rows = $model->get();

            if ($rows->isEmpty()) {
                $str .= "$meta $no_of_employees employee(s) found for Company ID $company_id.\n";
                continue;
            };

            foreach ($rows as $row) {

                $roster = $row["roster"];

                $index = array_search($currentDay, $roster["days"]);

                $model = ScheduleEmployee::query();
                $model->where("company_id", $company_id);

                $model->where(function ($q) use ($currentDate) {
                    $q->where('from_date', '<=', $currentDate)
                        ->where('to_date', '>=', $currentDate);
                });

                $model->where("employee_id", $row["employee_id"]);
                $model->where("roster_id", $roster["id"]);
                $arr = [];

                if ($index == -2) {
                    $arr = [
                        "shift_id" => -2,
                        "shift_type_id" => -2,
                    ];
                } else if ($index == -1) {
                    $arr = [
                        "shift_id" => -1,
                        "shift_type_id" => -1,
                    ];
                } else {
                    $arr = [
                        "shift_id" => $roster->shift_ids[$index],
                        "shift_type_id" => $roster->shift_type_ids[$index]
                    ];
                }


                // $shiftTypeIdIndex = $roster["shift_type_ids"][$index] == 0 ? $index - 1 : $index;

                // $arr = [
                //     "shift_id" => $roster["shift_ids"][$index],
                //     "shift_type_id" => $roster["shift_type_ids"][$shiftTypeIdIndex],
                // ];

                $model->update($arr);
                $arr["employee_id"] = $row["employee_id"];
                $arrays[] = $arr;
                $no_of_employees++;
            }

            $str .= "$meta Total $no_of_employees employee(s) for Company ID $company_id has been scheduled.\n";
        }
        return $str;
    }

    public function assignScheduleByManual(Request $request)
    {
        $company_id = $request->company_id;
        $currentDate = $request->date ?? date('Y-m-d');
        $currentDay = date("D", strtotime($currentDate));

        $employeesScheduled = 0;

        $model = ScheduleEmployee::query();
        $model = $this->custom_with($model, "roster", $company_id);

        $model->where("company_id", $company_id);
        $model->where(function ($q) use ($currentDate) {
            $q->where('from_date', '<=', $currentDate)
                ->where('to_date', '>=', $currentDate);
        });

        $scheduleEmployees = $model->get();

        if ($scheduleEmployees->isEmpty()) {
            return "No employee(s) found";
        }

        foreach ($scheduleEmployees as $schedule) {

            $roster = $schedule->roster;

            $index = array_search($currentDay, $roster->days);

            $arr = [];

            if ($index == -2) {
                $arr = [
                    "shift_id" => -2,
                    "shift_type_id" => -2,
                ];
            } else if ($index == -1) {
                $arr = [
                    "shift_id" => -1,
                    "shift_type_id" => -1,
                ];
            } else {
                $arr = [
                    "shift_id" => $roster->shift_ids[$index],
                    "shift_type_id" => $roster->shift_type_ids[$index]
                ];
            }


            $schedule->update($arr);

            $employeesScheduled++;
        }

        return "$employeesScheduled Employee(s) has been scheduled.\n";
    }

    public function scheduled_employees(Employee $employee, Request $request)
    {
        return $employee->with("branch")->where("company_id", $request->company_id)
            ->whereHas('schedule', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            })->when($request->filled('branch_id'), function ($q) use ($request) {

                $q->where('branch_id', $request->branch_id);
            })
            ->paginate($request->per_page);
    }

    public function not_scheduled_employees(Employee $employee, Request $request)
    {
        return $employee->with("branch")->where("company_id", $request->company_id)
            ->whereDoesntHave('schedule', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            })
            ->when($request->filled('branch_id'), function ($q) use ($request) {

                $q->where('branch_id', $request->branch_id);
            })
            ->paginate($request->per_page);
    }

    public function scheduled_employees_index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $employee = ScheduleEmployee::query()->with("branch");
        $model = $employee->where('company_id', $request->company_id);
        // $model->whereHas('roster');

        // $model =  $model->whereBetween('from_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        // $model->whereDate('from_date', '<=', $date);
        // $model->whereDate('to_date', '>=', $date);
        $model->when($request->filled('employee_first_name'), function ($q) use ($request) {

            $q->whereHas('employee', fn (Builder $query) => $query->where('first_name', env('WILD_CARD') ?? 'ILIKE', "$request->employee_first_name%"));
        });




        $model->when($request->filled('department_ids') && count($request->department_ids) > 0, function ($q) use ($request) {

            $q->whereHas('employee', fn (Builder $query) => $query->whereIn('department_id', $request->department_ids));
        });
        $model->when($request->filled('roster_name'), function ($q) use ($request) {

            $q->whereHas('roster', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->roster_name%"));
        });
        $model->when($request->filled('shift_name'), function ($q) use ($request) {

            $q->whereHas('shift', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->shift_name%"));
        });
        $model->when($request->filled('shift_type_name'), function ($q) use ($request) {

            $q->whereHas('shift_type', fn (Builder $query) => $query->where('name', env('WILD_CARD') ?? 'ILIKE', "$request->shift_type_name%"));
        });
        $model->when($request->filled('employee_id'), function ($q) use ($request) {

            //$q->where('employee_id', env('WILD_CARD') ?? 'ILIKE', "$request->employee_id%");
            $q->whereHas('employee', fn (Builder $query) => $query->where('employee_id', env('WILD_CARD') ?? 'ILIKE', "$request->employee_id%"));
        });
        $model->when($request->filled('show_from_date'), function ($q) use ($request) {

            $q->where('from_date', 'LIKE', "$request->show_from_date%");
        });
        $model->when($request->filled('show_to_date'), function ($q) use ($request) {

            $q->where('to_date', 'LIKE', "$request->show_to_date%");
        });
        $model->when($request->filled('from_date'), function ($q) use ($request) {

            $q->where('from_date', $request->from_date);
        });
        $model->when($request->filled('to_date'), function ($q) use ($request) {

            $q->where('to_date', $request->to_date);
        });
        $model->when($request->filled('isOverTime'), function ($q) use ($request) {

            $q->where('isOverTime', $request->isOverTime);
        });
        $model->when($request->filled('shift_id'), function ($q) use ($request) {

            $q->where('shift_id', $request->shift_id);
        });
        $model->when($request->filled('shift_type_id'), function ($q) use ($request) {

            $q->where('shift_type_id', $request->shift_type_id);
        });

        $model = $this->custom_with($model, "shift", $request->company_id);
        $model = $this->custom_with($model, "roster", $request->company_id);
        $model = $this->custom_with($model, "employee", $request->company_id);

        // $model->when($request->filled('sortBy'), function ($q) use ($request) {
        //     $sortDesc = $request->input('sortDesc');
        //     $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc');
        // });
        $model->when($request->filled('sortBy'), function ($q) use ($request) {
            $sortDesc = $request->input('sortDesc');
            if (strpos($request->sortBy, '.')) {
                if ($request->sortBy == 'employee.first_name') {
                    $q->orderBy(Employee::select("first_name")->where('company_id', $request->company_id)->whereColumn("employees.system_user_id", "schedule_employees.employee_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                } else if ($request->sortBy == 'roster.name') {
                    $q->orderBy(Roster::select("name")->where('company_id', $request->company_id)->whereColumn("rosters.id", "schedule_employees.roster_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                } else if ($request->sortBy == 'shift.name') {
                    $q->orderBy(Roster::select("name")->where('company_id', $request->company_id)->whereColumn("rosters.id", "schedule_employees.roster_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                } else if ($request->sortBy == 'shift_type.name') {
                    $q->orderBy(ShiftType::select("name")->where('company_id', $request->company_id)->whereColumn("shift_types.id", "schedule_employees.shift_type_id"), $sortDesc == 'true' ? 'desc' : 'asc');
                }
            } else {
                $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc');
            }
        });

        return $model->paginate($request->per_page ?? 20);
    }
    public function scheduled_employees_with_type(Employee $employee, Request $request)
    {
        return $employee
            ->where("company_id", $request->company_id)
            ->where("status", 1)
            // ->whereHas('schedule')
            // ->whereHas('schedule.shift_type', function ($q) use ($request) {
            //     $q->where('id', '=', $request->shift_type_id);
            // })
            ->when($request->filled('branch_id'), function ($q) use ($request) {

                $q->where('branch_id', $request->branch_id);
            })
            ->withOut(["user", "department", "sub_department", "designation", "role", "schedule"])
            ->when(count($request->department_ids ?? []) > 0, function ($q) use ($request) {
                $q->whereIn('department_id', $request->department_ids);
            })
            ->orderBy("first_name", "ASC")
            ->get(["first_name", "last_name", "system_user_id", "employee_id", "display_name"]);
    }

    public function getShiftsByEmployee(Request $request, $id)
    {
        try {
            $model = ScheduleEmployee::query();
            $data = $model
                ->whereCompanyId($request->company_id)
                ->whereEmployeeId($id)
                // ->withOut(["shift", "shift_type"])
            ;
            $data->when($request->filled('edit_id'), function ($q) use ($request) {

                $q->where('id', $request->edit_id);
            });

            // ->with('roster')
            $data =   $data->orderBy("from_date", "DESC");



            if ($request->pagination) {
                return $data->paginate($request->per_page ?? 10);
            } else {

                return   $data->get(['id', 'employee_id', 'isOverTime as is_over_time', 'shift_type_id', 'shift_id', 'branch_id', 'from_date', 'to_date', "isAutoShift"])
                    ->makeHidden(['employee_id', 'show_from_date', 'show_to_date']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
