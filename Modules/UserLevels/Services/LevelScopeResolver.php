<?php

namespace Modules\UserLevels\Services;

use Illuminate\Support\Facades\Auth;

class LevelScopeResolver
{
    public function currentCompanyId(): ?int
    {
        if (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company') {
            return Auth::guard('company')->id();
        }

        return null;
    }

    public function isCompanyScope(): bool
    {
        return $this->currentCompanyId() !== null;
    }
}
