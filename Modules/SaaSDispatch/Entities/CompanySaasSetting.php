<?php

namespace Modules\SaaSDispatch\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanySaasSetting extends Model
{
    protected $fillable = [
        'company_id',
        'is_saas_enabled',
        'dispatch_mode',
        'use_own_locations',
        'use_own_fares',
        'use_own_fees',
        'use_own_vehicle_types',
        'use_own_dispatch_settings',
        'use_own_branding',
        'use_company_documents',
        'use_networking',
        'use_accounts_invoicing',
        'use_data_suite',
        'use_messages',
        'use_support_system_status',
        'domain',
        'subdomain',
        'status',
    ];

    protected $casts = [
        'is_saas_enabled' => 'boolean',
        'use_own_locations' => 'boolean',
        'use_own_fares' => 'boolean',
        'use_own_fees' => 'boolean',
        'use_own_vehicle_types' => 'boolean',
        'use_own_dispatch_settings' => 'boolean',
        'use_own_branding' => 'boolean',
        'use_company_documents' => 'boolean',
        'use_networking' => 'boolean',
        'use_accounts_invoicing' => 'boolean',
        'use_data_suite' => 'boolean',
        'use_messages' => 'boolean',
        'use_support_system_status' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
