<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'super-admin', 'hr']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payroll_code' => 'required|string|unique:payrolls,payroll_code',
            'payroll_group_id' => 'required_without:employee_id|nullable|exists:payroll_groups,id',
            'employee_id' => 'required_without:payroll_group_id|nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
        ];
    }
}
