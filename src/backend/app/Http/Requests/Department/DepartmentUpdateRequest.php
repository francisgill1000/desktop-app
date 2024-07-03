<?php

namespace App\Http\Requests\Department;

use App\Traits\failedValidationWithName;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentUpdateRequest extends FormRequest
{
    use failedValidationWithName;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|min:4|max:50',
            'branch_id' => 'required',
            'company_id' => 'required',

            'managers' => 'required|array',
            'managers.*.role_id' => 'required',
            'managers.*.name' => 'required|string|max:255',
            'managers.*.email' => 'required|email|max:255',
            'managers.*.password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'managers.*.name.required' => 'The manager name is required.',
            'managers.*.name.string' => 'The manager name must be a string.',
            'managers.*.name.max' => 'The manager name may not be greater than 255 characters.',
            'managers.*.email.required' => 'The manager email is required.',
            'managers.*.email.email' => 'The manager email must be a valid email address.',
            'managers.*.email.max' => 'The manager email may not be greater than 255 characters.',
            'managers.*.email.unique' => 'The manager email has already been taken.',
            'managers.*.password.required' => 'The manager password is required.',
            'managers.*.password.string' => 'The manager password must be a string.',
            'managers.*.password.min' => 'The manager password must be at least 8 characters.',

            'managers.*.role_id.required' => 'The manager role is required.',
        ];
    }
}
