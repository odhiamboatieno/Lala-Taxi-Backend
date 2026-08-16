<?php

namespace Modules\OperationTeam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OperationTeamRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'status' => ['nullable'],
        ];
    }
}
