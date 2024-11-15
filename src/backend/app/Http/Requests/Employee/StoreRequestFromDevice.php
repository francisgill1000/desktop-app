<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequestFromDevice extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'employees' => 'required|array|min:1',
            'employees.*.full_name' => 'required|string|min:2|max:100',
            'employees.*.company_id' => 'required|integer',
            'employees.*.profile_picture' => 'nullable',
            'employees.*.rfid_card_number' => 'nullable',
            'employees.*.rfid_card_password' => 'nullable',
            'employees.*.fp' => 'array',
            'employees.*.palm' => 'array',
        ];

        foreach ($this->input('employees', []) as $index => $employee) {
            $rules["employees.$index.employee_id"][] = "required";
            $rules["employees.$index.employee_id"][] = Rule::unique("employees", "employee_id")
                ->where("company_id", $employee['company_id'] ?? null);

            $rules["employees.$index.system_user_id"][] = "required";
            $rules["employees.$index.system_user_id"][] = "regex:/^[1-9][0-9]*$/";
            $rules["employees.$index.system_user_id"][] = Rule::unique("employees", "system_user_id")
                ->where("company_id", $employee['company_id'] ?? null);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'employees.*.employee_id.required' => 'Each employee must have an employee ID.',
            'employees.*.employee_id.unique' => 'The employee ID must be unique.',

            'employees.*.system_user_id.required' => 'System user ID is required for each employee.',
            'employees.*.employee_id.unique' => 'The System user ID must be unique.',
            'employees.*.system_user_id.regex' => 'The employee device ID should not start with zero.',

            'employees.*.full_name.required' => 'Each employee must have a full name.',
            'employees.*.full_name.min' => 'The full name must be at least 2 characters.',
            'employees.*.profile_picture.required' => 'Each employee must have a profile picture.',

        ];
    }
}
