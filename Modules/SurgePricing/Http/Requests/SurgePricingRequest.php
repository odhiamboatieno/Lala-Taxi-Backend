<?php

namespace Modules\SurgePricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurgePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'applies_to' => ['required', 'in:ride,parcel,both'],
            'zone_scope' => ['required', 'in:all,selected'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer'],
            'ride_rate_mode' => ['required', 'in:same,different'],
            'ride_rate' => ['nullable', 'numeric', 'min:0'],
            'parcel_rate' => ['nullable', 'numeric', 'min:0'],
            'schedule_type' => ['required', 'in:daily,weekly,custom'],
            'range_start' => ['nullable', 'date'],
            'range_end' => ['nullable', 'date'],
            'time_ranges' => ['nullable', 'array'],
            'time_ranges.*.date' => ['nullable', 'date'],
            'time_ranges.*.start_time' => ['nullable'],
            'time_ranges.*.end_time' => ['nullable'],
            'weekdays' => ['nullable', 'array'],
            'customer_note' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable'],
        ];
    }
}
