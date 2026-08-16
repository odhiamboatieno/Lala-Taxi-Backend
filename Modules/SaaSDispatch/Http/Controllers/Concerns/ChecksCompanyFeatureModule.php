<?php

namespace Modules\SaaSDispatch\Http\Controllers\Concerns;

trait ChecksCompanyFeatureModule
{
    protected function ensureCompanyFeatureEnabled(string $slug): void
    {
        abort_unless(
            function_exists('company_saas_enabled')
            && function_exists('saas_feature_module_enabled')
            && company_saas_enabled()
            && saas_feature_module_enabled($slug),
            404
        );
    }
}
