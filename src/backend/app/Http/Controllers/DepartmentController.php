<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\DepartmentRequest;
use App\Http\Requests\Department\DepartmentUpdateRequest;
use App\Models\CompanyBranch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DepartmentController extends Controller
{
    public function dropdownList(Request $request)
    {
        $model = Department::query();
        $model->where('company_id', $request->company_id);
        $model->when($request->user_type == "department", fn ($q) => $q->where("id", $request->department_id));
        $model->when(request()->filled('branch_id'), fn ($q) => $q->where('branch_id', request('branch_id')));
        $model->orderBy(request('order_by') ? "id" : 'name', request('sort_by_desc') ? "desc" : "asc");
        return $model->get(["id", "name"]);
    }

    public function index(Department $department, Request $request)
    {
        return $department->filter($request)->orderBy("id", "desc")->paginate($request->per_page);
    }

    public function departmentEmployee(Request $request)
    {
        $model = Department::query();
        $model->where('company_id', $request->company_id);
        $model->with(['branch', 'employees:id,employee_id,system_user_id,first_name,last_name,display_name,department_id']);

        $model->select("id", "name");
        return $model->paginate($request->per_page);
    }

    public function search(Request $request, $key)
    {
        $model = Department::query();
        $model->where('id', 'LIKE', "%$key%");
        $model->where('company_id', $request->company_id);
        $model->orWhere('name', 'LIKE', "%$key%");
        return $model->with('children')->paginate($request->per_page);
    }

    public function store(Department $model, DepartmentRequest $request)
    {
        try {
            $data = $request->validated();

            $record = $model->create([
                "name" => $data["name"],
                "branch_id" => $data["branch_id"],
                "company_id" => $data["company_id"],
            ]);

            $arr = [];

            foreach ($data["managers"] as $manager) {
                $arr[] = [
                    "name" => $manager["name"],
                    "email" => $manager["email"],
                    "password" => Hash::make($manager["password"]),
                    "user_type" => "department",
                    "department_id" => $record->id,
                    "company_id" => $data["company_id"],
                    "branch_id" => $data["branch_id"],
                    "role_id" => $manager["role_id"],
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }

            User::insert($arr);


            if ($record) {
                return $this->response('Department successfully added.', $record->with('children'), true);
            } else {
                return $this->response('Department cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(Department $Department)
    {
        return $Department->with('children');
    }

    public function update(DepartmentUpdateRequest $request, Department $Department)
    {
        try {
            $data = $request->validated();

            $record = $Department->update([
                "name" => $data["name"],
                "company_id" => $data["company_id"],
                "branch_id" => $data["branch_id"],
            ]);


            $arr = [];

            foreach ($data["managers"] as $manager) {
                $payload = [
                    "name" => $manager["name"],
                    "email" => $manager["email"],
                    "user_type" => "department",
                    "department_id" => $Department->id,
                    "company_id" => $data["company_id"],
                    "branch_id" => $data["branch_id"],
                    "role_id" => $manager["role_id"],
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
                if ($manager["password"]) {
                    $payload["password"] = Hash::make($manager["password"]);
                }

                $arr[] = $payload;
            }

            $user = User::query();
            $user->where("department_id", $Department->id);
            $user->delete();
            $user->insert($arr);

            if ($record) {
                return $this->response('Department successfully updated.', $Department->with('children'), true);
            } else {
                return $this->response('Department cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Department $Department)
    {
        try {
            $record = $Department->delete();

            User::where("department_id", $Department->id)->delete();

            if ($record) {
                return $this->response('Department successfully deleted.', null, true);
            } else {
                return $this->response('Department cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteSelected(Department $model, Request $request)
    {
        try {
            $record = $model->whereIn('id', $request->ids)->delete();

            if ($record) {
                return $this->response('Department successfully deleted.', null, true);
            } else {
                return $this->response('Department cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
