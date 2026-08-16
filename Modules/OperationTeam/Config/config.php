<?php

return [
    'module_permissions' => [
        'dashboard' => ['manage_operation_team_dashboard'],
        'zone_management' => ['manage_locations', 'manage_company_service_areas'],
        'trip_management' => ['manage_trips', 'manage_requests', 'manage_rating'],
        'parcel_management' => ['manage_parcel_categories', 'manage_parcel_package_sizes', 'manage_parcel_tasks', 'manage_parcel_pricing', 'manage_parcel_settings', 'manage_parcel_trips'],
        'promotion_management' => ['manage_promo_code'],
        'vehicle_management' => ['manage_vehicle', 'manage_vehicle_type', 'view_vehicle_make', 'view_vehicle_model', 'manage_company_vehicle_types'],
        'fare_management' => ['manage_company_fares', 'manage_company_fees', 'manage_company_dispatch_settings', 'manage_company_service_areas', 'manage_company_vehicle_types', 'manage_company_driver_fees', 'manage_company_surge_pricing', 'manage_company_payment_gateways'],
        'user_management' => ['view_driver', 'create_driver', 'update_driver', 'delete_driver', 'view_rider', 'create_rider', 'update_rider', 'delete_rider', 'manage_driver_levels', 'manage_rider_levels'],
        'transaction_management' => ['manage_payments', 'manage_statements', 'manage_driver_payments', 'manage_owe_amount', 'manage_company_accounts_invoicing'],
        'business_management' => ['update_company', 'manage_saas_module', 'manage_company_branding', 'manage_company_domains', 'manage_operation_team_roles', 'manage_operation_team_members', 'manage_company_networking', 'manage_company_data_suite'],
        'support' => ['manage_support', 'manage_send_message', 'manage_company_messages', 'manage_company_support_tickets', 'manage_company_documents'],
    ],
];
