<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee') ? $this->route('employee')->id : null;

        return [
            'employee_id' => 'required|not_regex:/^0/|unique:employees,employee_id,' . $employeeId,
            'site_id' => 'required|exists:sites,id',
            'web_bundy_code' => 'required|string|min:4',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:employees,email,' . $employeeId,
            'position' => 'required',
            'daily_rate' => 'required|numeric',
            'birthday' => 'required|date',
            'gender' => 'required',
            'civil_status' => 'required',
            'location' => 'nullable',
            'employment_type' => 'required',
            'classification' => 'required',
            'date_employed' => 'required|date',
            'tax_code' => 'nullable',
            'pay_type' => 'required',
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'bank_name' => 'nullable|string',
            'account_no' => 'nullable|string',
            'tin_no' => 'nullable|string',
            'sss_no' => 'nullable|string',
            'pagibig_no' => 'nullable|string',
            'philhealth_no' => 'nullable|string',
            'mobile_no_1' => 'nullable|string',
            'mobile_no_2' => 'nullable|string',
            'tel_no_1' => 'nullable|string',
            'tel_no_2' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|string',
            'instagram_url' => 'nullable|string',
            'permanent_address_brgy' => 'required|string',
            'permanent_address_province' => 'required|string',
            'present_address_brgy' => 'required|string',
            'present_address_province' => 'required|string',
            'other_information' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
