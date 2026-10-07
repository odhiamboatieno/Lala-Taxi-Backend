<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\Admin;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyContextResolver
{
    public function isAdmin(): bool
    {
        return Auth::guard('admin')->check() && !$this->isCompany();
    }

    public function isCompany(): bool
    {
        return (request()->segment(1) === 'company' || request()->is('api/company/*')) && Auth::guard('company')->check();
    }

    public function actingCompany(?Company $company = null): Company
    {
        if ($this->isCompany()) {
            $authCompany = Auth::guard('company')->user();
            abort_unless($authCompany instanceof Company, 403);
            abort_if($company && (int) $company->id !== (int) $authCompany->id, 404);

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
