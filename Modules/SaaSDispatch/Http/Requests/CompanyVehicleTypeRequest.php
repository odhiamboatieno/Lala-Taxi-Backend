<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyVehicleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicleTypeId = $this->route('companyVehicleType')?->id;
        $companyId = $this->route('company')?->id ?? auth('company')->id();

        return [
            'global_vehicle_category_id' => ['nullable', 'exists:global_vehicle_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('company_vehicle_types', 'slug')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($vehicleTypeId),
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'vehicle_image' => [$vehicleTypeId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,gif'],
            'active_image' => [$vehicleTypeId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,gif'],
            'app_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif'],
            'is_pool' => ['required', Rule::in(['Yes', 'No'])],
            'passenger_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'luggage_capacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_ev' => ['nullable', 'boolean'],
            'is_accessible' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
