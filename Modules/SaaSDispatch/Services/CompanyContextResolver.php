<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\Admin;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyContextResolver
{
    public function isAdmin(): bool
    {
        return Auth::guard('admin')->check() && LOGIN_USER_TYPE !== 'company';
    }

    public function isCompany(): bool
    {
        return Auth::guard('company')->check() || LOGIN_USER_TYPE === 'company';
    }

    public function actingCompany(?Company $company = null): Company
    {
        if ($this->isCompany()) {
            $authCompany = Auth::guard('company')->user();
            abort_unless($authCompany instanceof Company, 403);

            return $authCompany;
        }

        abort_unless($this->isAdmin(), 403);
        abort_unless($company instanceof Company, 404);

        return $company;
    }

    public function actor(): Admin|Company
    {
        if ($this->isCompany()) {
            $company = Auth::guard('company')->user();
            abort_unless($company instanceof Company, 403);

            return $company;
        }

        $admin = Auth::guard('admin')->user();
        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    public function routePrefix(): string
    {
        return $this->isCompany() ? 'company' : 'admin';
    }
}
