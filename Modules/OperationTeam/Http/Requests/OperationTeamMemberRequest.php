<?php

namespace Modules\OperationTeam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OperationTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id ?? $this->route('member');

        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:191'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'mobile_number' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:191'],
            'identity_type' => ['nullable', 'string', 'max:120'],
            'identity_number' => ['nullable', 'string', 'max:120'],
            'role_id' => ['required', 'integer'],
            'password' => [$memberId ? 'nullable' : 'required', 'string', 'min:6'],
            'image' => ['nullable', 'image'],
            'identity_image' => ['nullable', 'image'],
            'status' => ['nullable'],
        ];
    }
}
