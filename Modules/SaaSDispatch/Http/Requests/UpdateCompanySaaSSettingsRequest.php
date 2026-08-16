<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanySaaSSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'dispatch_mode' => ['required', Rule::in(['global', 'saas', 'hybrid'])],
            'use_own_locations' => ['nullable', 'boolean'],
            'use_own_fares' => ['nullable', 'boolean'],
            'use_own_fees' => ['nullable', 'boolean'],
            'use_own_vehicle_types' => ['nullable', 'boolean'],
            'use_own_dispatch_settings' => ['nullable', 'boolean'],
            'use_own_branding' => ['nullable', 'boolean'],
            'domain' => ['nullable', 'string', 'max:255'],
            'subdomain' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];

        if (auth('admin')->check()) {
            $rules['is_saas_enabled'] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}
