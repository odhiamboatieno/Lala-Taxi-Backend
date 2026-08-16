<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zone_name' => ['nullable', 'string', 'max:255'],
            'coordinates' => ['required', 'string'],
            'polygon' => ['nullable', 'string'],
            'pickup_enabled' => ['nullable', 'boolean'],
            'dropoff_enabled' => ['nullable', 'boolean'],
            'extra_fare_enabled' => ['nullable', 'boolean'],
            'extra_fare_percentage' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'extra_fare_reason' => ['nullable', 'string', 'max:191'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
