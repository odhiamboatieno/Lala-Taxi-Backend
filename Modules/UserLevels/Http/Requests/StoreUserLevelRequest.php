<?php

namespace Modules\UserLevels\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sequence' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:191'],
            'icon' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'is_default' => ['nullable', 'boolean'],

            // SaaS extension: when present, the new / updated level will
            // be marked as inheriting from the chosen admin level.  The
            // value must reference an existing admin level.
            'inherits_from_id' => ['nullable', 'integer', 'exists:user_levels,id'],

            'reward_type' => ['nullable', 'string', 'max:100'],
            'reward_amount' => ['nullable', 'numeric', 'min:0'],
            'minimum_completed_trips' => ['nullable', 'integer', 'min:0'],
            'minimum_earnings' => ['nullable', 'numeric', 'min:0'],
            'minimum_spend' => ['nullable', 'numeric', 'min:0'],
            'maximum_cancellation_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'minimum_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'minimum_reviews' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
