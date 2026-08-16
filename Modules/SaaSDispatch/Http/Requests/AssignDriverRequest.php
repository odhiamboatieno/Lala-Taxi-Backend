<?php

namespace Modules\SaaSDispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
