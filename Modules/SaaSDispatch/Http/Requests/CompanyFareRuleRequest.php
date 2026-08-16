<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyFareRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_vehicle_type_id' => ['required', 'exists:company_vehicle_types,id'],
            'company_service_area_id' => ['nullable', 'exists:company_service_areas,id'],
            'base_fare' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'per_km' => ['required', 'numeric', 'min:0'],
            'per_min' => ['required', 'numeric', 'min:0'],
            'schedule_fare' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'max:10'],
            'apply_peak' => ['required', Rule::in(['Yes', 'No'])],
            'apply_night' => ['required', Rule::in(['Yes', 'No'])],
            'waiting_time' => ['nullable', 'numeric', 'min:0'],
            'waiting_charge' => ['nullable', 'numeric', 'min:0'],
            'minimum_fare' => ['required', 'numeric', 'min:0'],
            'waiting_charge_per_minute' => ['nullable', 'numeric', 'min:0'],
            'cancellation_fee' => ['nullable', 'numeric', 'min:0'],
            'night_surcharge_type' => ['nullable', 'string', 'max:100'],
            'night_surcharge_value' => ['nullable', 'numeric', 'min:0'],
            'airport_fee' => ['nullable', 'numeric', 'min:0'],
            'commission_type' => ['nullable', 'string', 'max:100'],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
