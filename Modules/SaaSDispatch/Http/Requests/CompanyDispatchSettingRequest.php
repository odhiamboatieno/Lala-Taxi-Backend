<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDispatchSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dispatch_strategy' => ['nullable', 'string', 'max:255'],
            'driver_search_radius' => ['required', 'numeric', 'min:0'],
            'max_driver_search_radius' => ['required', 'numeric', 'min:0'],
            'request_timeout_seconds' => ['required', 'integer', 'min:5'],
            'retry_rounds' => ['required', 'integer', 'min:0'],
            'allow_auto_dispatch' => ['nullable', 'boolean'],
            'allow_manual_dispatch' => ['nullable', 'boolean'],
            'allow_scheduled_trips' => ['nullable', 'boolean'],
            'allow_airport_trips' => ['nullable', 'boolean'],
            'allow_out_of_area_trips' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
