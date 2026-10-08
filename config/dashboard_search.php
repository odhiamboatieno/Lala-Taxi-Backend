<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard search
    |--------------------------------------------------------------------------
    |
    | Central registry for the backend global search (Ctrl/⌘+K). Keeping the
    | quick-create shortcuts and the record sources in one place means the
    | navigation index and the record index can never drift apart.
    |
    */

    /*
    | Quick-create destinations. Each is surfaced in the search dialog only when
    | the signed-in operator holds the permission attached to the route (the
    | server-side route access map decides). Paths are scope-relative and are
    | prefixed with the active scope (admin|company) at render time.
    */
    'features' => [
        ['path' => 'add_driver', 'parent' => 'driver', 'title' => 'Add driver', 'keywords' => 'create onboard chauffeur'],
        ['path' => 'add_rider', 'parent' => 'rider', 'title' => 'Add customer', 'keywords' => 'create rider passenger'],
        ['path' => 'add_vehicle', 'parent' => 'vehicle', 'title' => 'Add vehicle', 'keywords' => 'create register fleet car'],
        ['path' => 'add_company', 'parent' => 'company', 'title' => 'Add company', 'keywords' => 'create onboard business tenant'],
        ['path' => 'add_vehicle_type', 'parent' => 'vehicle_type', 'title' => 'Add vehicle type', 'keywords' => 'create category class car'],
        ['path' => 'add-vehicle-make', 'parent' => 'vehicle_make', 'title' => 'Add vehicle make', 'keywords' => 'create manufacturer brand'],
        ['path' => 'add-vehicle_model', 'parent' => 'vehicle_model', 'title' => 'Add vehicle model', 'keywords' => 'create model variant'],
        ['path' => 'add_location', 'parent' => 'locations', 'title' => 'Add location', 'keywords' => 'create zone coverage place'],
        ['path' => 'add_manage_fare', 'parent' => 'manage_fare', 'title' => 'Add peak fare', 'keywords' => 'create surge pricing rate'],
        ['path' => 'add-additional-reason', 'parent' => 'additional-reasons', 'title' => 'Add toll reason', 'keywords' => 'create additional charge reason'],
        ['path' => 'add-cancel-reason', 'parent' => 'cancel-reason', 'title' => 'Add cancel reason', 'keywords' => 'create cancellation reason'],
        ['path' => 'add_help_category', 'parent' => 'help_category', 'title' => 'Add help category', 'keywords' => 'create faq category'],
        ['path' => 'add_help_subcategory', 'parent' => 'help_subcategory', 'title' => 'Add help subcategory', 'keywords' => 'create faq subcategory'],
        ['path' => 'add_help', 'parent' => 'help', 'title' => 'Add help article', 'keywords' => 'create faq article knowledge'],
        ['path' => 'add_support', 'parent' => 'support', 'title' => 'Add support entry', 'keywords' => 'create ticket assistance'],
        ['path' => 'add_document', 'parent' => 'documents', 'title' => 'Add document', 'keywords' => 'create compliance upload file'],
        ['path' => 'add_promo_code', 'parent' => 'promo_code', 'title' => 'Add promo code', 'keywords' => 'create discount coupon voucher'],
        ['path' => 'add_page', 'parent' => 'pages', 'title' => 'Add page', 'keywords' => 'create static content cms'],
        ['path' => 'add_country', 'parent' => 'country', 'title' => 'Add country', 'keywords' => 'create region market'],
        ['path' => 'add_currency', 'parent' => 'currency', 'title' => 'Add currency', 'keywords' => 'create money forex'],
        ['path' => 'add_language', 'parent' => 'language', 'title' => 'Add language', 'keywords' => 'create locale translation'],
        ['path' => 'add_banner', 'parent' => 'banner', 'title' => 'Add banner', 'keywords' => 'create promotion image'],
        ['path' => 'add_app_version', 'parent' => 'mobile_app_version', 'title' => 'Add app version', 'keywords' => 'create release build mobile'],
        ['path' => 'add_parcel_category', 'parent' => 'parcel_categories', 'title' => 'Add parcel category', 'keywords' => 'create delivery category'],
        ['path' => 'add_parcel_package_size', 'parent' => 'parcel_package_sizes', 'title' => 'Add package size', 'keywords' => 'create parcel size'],
        ['path' => 'add_parcel_task', 'parent' => 'parcel_tasks', 'title' => 'Add parcel task', 'keywords' => 'create delivery task'],
        ['path' => 'add_parcel_pricing', 'parent' => 'parcel_pricing', 'title' => 'Add parcel pricing', 'keywords' => 'create delivery rate'],
        ['path' => 'add_admin_user', 'parent' => 'admin_user', 'title' => 'Add administrator', 'keywords' => 'create admin staff'],
        ['path' => 'add_role', 'parent' => 'roles', 'title' => 'Create role', 'keywords' => 'permissions access'],
        ['path' => 'operation-team/members/create', 'parent' => 'operation-team/members', 'title' => 'Add staff member', 'keywords' => 'invite employee team'],
        ['path' => 'operation-team/roles/create', 'parent' => 'operation-team/roles', 'title' => 'Create staff role', 'keywords' => 'permissions access team'],
        ['path' => 'saas-dispatch/service-areas/create', 'parent' => 'saas-dispatch/service-areas', 'title' => 'Create service area', 'keywords' => 'zone coverage location'],
        ['path' => 'saas-dispatch/vehicle-types/create', 'parent' => 'saas-dispatch/vehicle-types', 'title' => 'Create vehicle type', 'keywords' => 'category fleet car'],
        ['path' => 'saas-dispatch/fare-rules/create', 'parent' => 'saas-dispatch/fare-rules', 'title' => 'Create fare rule', 'keywords' => 'pricing rates tariff'],
        ['path' => 'saas-dispatch/accounts/create', 'parent' => 'saas-dispatch/accounts', 'title' => 'Create corporate account', 'keywords' => 'employer billing invoicing company account'],
    ],
];
