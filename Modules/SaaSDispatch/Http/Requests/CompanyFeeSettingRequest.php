<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyFeeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_fee' => ['nullable', 'numeric', 'min:0'],
            'driver_peak_fare' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'driver_service_fee' => ['nullable', 'numeric', 'min:0'],
            'additional_rider_fare' => ['required', 'numeric', 'min:0'],
            'additional_fee' => ['required', Rule::in(['Yes', 'No'])],
        ];
    }
}
