<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WebBundyPunchRequest extends FormRequest
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
        return [
            'employee_id_string' => 'required|string',
            'web_bundy_code' => 'required|string',
            'punch_type' => 'required|in:am_in,am_out,pm_in,pm_out,break1_out,break1_in,break2_out,break2_in,lunch_out,lunch_in,none'
        ];
    }
}
