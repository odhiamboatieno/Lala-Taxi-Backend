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
    | Source keys
    | -----------
    | type              stable entity key used in the index table
    | table             source table
    | where             fixed column filters (e.g. user_type)
    | scope             both | admin | company
    | permission        required permission (null = any signed-in operator)
    | feature           optional feature-module gate
    | title             columns concatenated into the result title
    | detail            columns concatenated into the result description
    | fields            columns indexed for matching
    | group             display group label
    | icon              bootstrap/fa icon class
    | aliases           extra category words that browse this section
    | path              detail URL relative to the scope (may contain {id})
    | path_admin        admin-only override for `path`
    | list_path         fallback list URL relative to the scope
    | detail_permission permission required to open `path` (defaults to permission)
    | company_column    direct tenant column (default company_id)
    | company_via       [table, foreign_key] used to resolve the tenant when the
    |                   source has no company_id of its own
    | id_column         primary key column (default id)
    | updated           source timestamp column used for ranking/delta sync
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

    /*
    | How long an index build is considered fresh before a search request
    | queues a background rebuild (works even without a scheduler container).
    */
    'stale_after' => 900,

    /*
    | Maximum records shown per group in the dialog before "load more".
    */
    'results_per_group' => 5,

    'sources' => [
        // ------------------------------------------------------------------
        // People & fleet — available to both portals
        // ------------------------------------------------------------------
        [
            'type' => 'driver', 'table' => 'users', 'where' => ['user_type' => 'Driver'],
            'scope' => 'both', 'permission' => 'view_driver', 'detail_permission' => 'update_driver',
            'title' => ['first_name', 'last_name'], 'detail' => ['email', 'mobile_number', 'status'],
            'fields' => ['first_name', 'last_name', 'email', 'mobile_number'], 'fallback' => 'email',
            'group' => 'Drivers', 'icon' => 'fa-id-card', 'aliases' => 'driver chauffeur',
            'path' => 'edit_driver/{id}', 'list_path' => 'driver', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'customer', 'table' => 'users', 'where' => ['user_type' => 'Rider'],
            'scope' => 'both', 'permission' => 'view_rider', 'detail_permission' => 'update_rider',
            'title' => ['first_name', 'last_name'], 'detail' => ['email', 'mobile_number', 'status'],
            'fields' => ['first_name', 'last_name', 'email', 'mobile_number'], 'fallback' => 'email',
            'group' => 'Customers', 'icon' => 'bi-people', 'aliases' => 'customer rider passenger',
            'path' => 'edit_rider/{id}', 'list_path' => 'rider', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'vehicle', 'table' => 'vehicle', 'scope' => 'both', 'permission' => 'manage_vehicle', 'detail_permission' => 'manage_vehicle',
            'title' => ['vehicle_number', 'vehicle_name'], 'detail' => ['vehicle_name', 'status'],
            'fields' => ['vehicle_number', 'vehicle_name', 'vehicle_id'],
            'group' => 'Vehicles', 'icon' => 'bi-car-front', 'aliases' => 'vehicle fleet plate registration',
            'path' => 'edit_vehicle/{id}', 'list_path' => 'vehicle', 'company_column' => 'company_id', 'updated' => null,
        ],
        [
            'type' => 'trip', 'table' => 'trips', 'scope' => 'both', 'permission' => 'manage_trips', 'detail_permission' => 'manage_trips',
            'title' => ['pickup_location'], 'detail' => ['drop_location', 'status'],
            'fields' => ['pickup_location', 'drop_location', 'status'],
            'group' => 'Trips', 'icon' => 'bi-signpost-split', 'aliases' => 'trip ride rides',
            'path' => 'view_trips/{id}', 'list_path' => 'trips', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'booking', 'table' => 'schedule_ride', 'scope' => 'both', 'permission' => 'manage_manual_booking',
            'feature' => 'manual_booking', 'detail_permission' => 'manage_manual_booking',
            'title' => ['booking_contact_name', 'pickup_location'], 'detail' => ['drop_location', 'status'],
            'fields' => ['pickup_location', 'drop_location', 'booking_contact_name', 'booking_contact_phone', 'status'],
            'group' => 'Bookings', 'icon' => 'bi-calendar-event', 'aliases' => 'booking scheduled dispatch reservation',
            'path' => 'manual_booking/{id}', 'list_path' => 'later_booking', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'request', 'table' => 'request', 'scope' => 'both', 'permission' => 'manage_requests', 'detail_permission' => 'manage_requests',
            'title' => ['pickup_location'], 'detail' => ['drop_location', 'status'],
            'fields' => ['pickup_location', 'drop_location', 'booking_contact_name', 'booking_contact_phone', 'status'],
            'group' => 'Booking requests', 'icon' => 'bi-inboxes', 'aliases' => 'request booking dispatch',
            'path' => 'detail_request/{id}', 'list_path' => 'request', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],

        // ------------------------------------------------------------------
        // Finance & operations
        // ------------------------------------------------------------------
        [
            'type' => 'payment', 'table' => 'payment', 'scope' => 'both', 'permission' => 'manage_payments', 'detail_permission' => 'manage_payments',
            'title' => [], 'detail' => ['trip_id', 'payment_trip_id_foreign'],
            'fields' => [],
            'group' => 'Payments', 'icon' => 'bi-credit-card', 'aliases' => 'payment collection transaction',
            'path' => 'view_payments/{id}', 'list_path' => 'payments', 'company_via' => ['trips', 'trip_id'], 'updated' => null,
        ],
        [
            'type' => 'rating', 'table' => 'rating', 'scope' => 'both', 'permission' => 'manage_rating', 'detail_permission' => 'manage_rating',
            'title' => [], 'detail' => ['rider_rating', 'driver_rating'],
            'fields' => ['rider_comments', 'driver_comments'],
            'group' => 'Ratings', 'icon' => 'bi-star', 'aliases' => 'rating feedback review',
            'path' => null, 'list_path' => 'rating', 'company_via' => ['trips', 'trip_id'], 'updated' => 'updated_at',
        ],
        [
            'type' => 'wallet', 'table' => 'wallet', 'scope' => 'both', 'permission' => 'manage_wallet', 'detail_permission' => 'manage_wallet',
            'id_column' => 'user_id', 'title' => [], 'detail' => ['amount', 'currency_code'],
            'fields' => ['paykey'],
            'group' => 'Wallets', 'icon' => 'bi-wallet2', 'aliases' => 'wallet balance top up',
            'path' => null, 'list_path' => 'wallet/Rider', 'company_via' => ['users', 'user_id'], 'updated' => null,
        ],
        [
            'type' => 'driver_withdrawal', 'table' => 'driver_withdrawal_requests', 'scope' => 'both', 'permission' => 'manage_driver_payments',
            'title' => [], 'detail' => ['amount', 'currency_code', 'status'],
            'fields' => ['note', 'status'],
            'group' => 'Driver payouts', 'icon' => 'bi-cash-stack', 'aliases' => 'payout withdrawal driver payment',
            'path' => null, 'list_path' => 'payout/overall', 'company_via' => ['users', 'driver_id'], 'updated' => 'updated_at',
        ],
        [
            'type' => 'referral_user', 'table' => 'referral_users', 'scope' => 'both', 'permission' => 'manage_rider_referrals', 'detail_permission' => 'manage_rider_referrals',
            'title' => [], 'detail' => ['user_type', 'amount', 'payment_status'],
            'fields' => ['user_type', 'payment_status'],
            'group' => 'Referrals', 'icon' => 'bi-share', 'aliases' => 'referral invite reward',
            'path' => null, 'list_path' => 'referrals/rider', 'company_via' => ['users', 'user_id'], 'updated' => 'updated_at',
        ],
        [
            'type' => 'driver_score', 'table' => 'driver_scores', 'scope' => 'both', 'permission' => null,
            'title' => [], 'detail' => ['score_percentage', 'status', 'marketplace_access_status'],
            'fields' => ['status', 'marketplace_access_status'],
            'group' => 'Driver scores', 'icon' => 'bi-speedometer', 'aliases' => 'score rating quality performance',
            'path' => 'driver-score/{id}', 'list_path' => 'driver-score', 'company_via' => ['users', 'driver_id'], 'updated' => 'updated_at',
        ],

        // ------------------------------------------------------------------
        // Parcel module
        // ------------------------------------------------------------------
        [
            'type' => 'parcel_category', 'table' => 'parcel_categories', 'scope' => 'both', 'permission' => 'manage_parcel_categories', 'detail_permission' => 'manage_parcel_categories',
            'title' => ['title'], 'detail' => ['description', 'is_active'], 'fields' => ['title', 'description', 'key'],
            'group' => 'Parcel categories', 'icon' => 'bi-box-seam', 'aliases' => 'parcel delivery category',
            'path' => null, 'list_path' => 'parcel_categories', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'parcel_package_size', 'table' => 'parcel_package_sizes', 'scope' => 'both', 'permission' => 'manage_parcel_package_sizes', 'detail_permission' => 'manage_parcel_package_sizes',
            'title' => ['title'], 'detail' => ['subtitle', 'description'], 'fields' => ['title', 'subtitle', 'description', 'key'],
            'group' => 'Package sizes', 'icon' => 'bi-box', 'aliases' => 'parcel package size',
            'path' => null, 'list_path' => 'parcel_package_sizes', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'parcel_task', 'table' => 'parcel_tasks', 'scope' => 'both', 'permission' => 'manage_parcel_tasks', 'detail_permission' => 'manage_parcel_tasks',
            'title' => ['title'], 'detail' => ['key', 'is_active'], 'fields' => ['title', 'key'],
            'group' => 'Parcel tasks', 'icon' => 'bi-list-check', 'aliases' => 'parcel task delivery',
            'path' => null, 'list_path' => 'parcel_tasks', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'parcel_pricing', 'table' => 'parcel_fares', 'scope' => 'both', 'permission' => 'manage_parcel_pricing', 'detail_permission' => 'manage_parcel_pricing',
            'title' => [], 'detail' => ['base_fare', 'per_km_rate', 'min_fare'], 'fields' => ['base_fare', 'per_km_rate'],
            'group' => 'Parcel pricing', 'icon' => 'bi-cash-coin', 'aliases' => 'parcel pricing fare rate',
            'path' => null, 'list_path' => 'parcel_pricing', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'parcel_trip', 'table' => 'parcel_requests', 'scope' => 'both', 'permission' => 'manage_parcel_trips', 'detail_permission' => 'manage_parcel_trips',
            'title' => ['recipient_name'], 'detail' => ['recipient_address', 'task_type'],
            'fields' => ['recipient_name', 'recipient_phone', 'recipient_address', 'parcel_description', 'category', 'task_type'],
            'group' => 'Parcel trips', 'icon' => 'bi-truck', 'aliases' => 'parcel delivery shipment courier',
            'path' => null, 'list_path' => 'parcel_trips', 'company_via' => ['request', 'request_id'], 'updated' => 'updated_at',
        ],

        // ------------------------------------------------------------------
        // Catalogue & configuration
        // ------------------------------------------------------------------
        [
            'type' => 'vehicle_type', 'table' => 'car_type', 'scope' => 'both', 'permission' => 'manage_vehicle_type', 'detail_permission' => 'manage_vehicle_type',
            'title' => ['car_name'], 'detail' => ['description', 'status'], 'fields' => ['car_name', 'description'],
            'group' => 'Vehicle types', 'icon' => 'bi-truck-flatbed', 'aliases' => 'vehicle type category class',
            'path' => null, 'list_path' => 'vehicle_type', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'vehicle_make', 'table' => 'vehicle_make', 'scope' => 'both', 'permission' => 'view_vehicle_make', 'detail_permission' => 'update_vehicle_make',
            'title' => ['make_vehicle_name'], 'detail' => ['status'], 'fields' => ['make_vehicle_name'],
            'group' => 'Vehicle makes', 'icon' => 'bi-tags', 'aliases' => 'vehicle make manufacturer brand',
            'path' => 'edit-vehicle-make/{id}', 'list_path' => 'vehicle_make', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'vehicle_model', 'table' => 'vehicle_model', 'scope' => 'both', 'permission' => 'view_vehicle_model', 'detail_permission' => 'update_vehicle_model',
            'title' => ['model_name'], 'detail' => ['status'], 'fields' => ['model_name'],
            'group' => 'Vehicle models', 'icon' => 'bi-tags', 'aliases' => 'vehicle model variant',
            'path' => 'edit-vehicle_model/{id}', 'list_path' => 'vehicle_model', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'location', 'table' => 'locations', 'scope' => 'both', 'permission' => 'manage_locations', 'detail_permission' => 'manage_locations',
            'title' => ['name'], 'detail' => ['extra_fare_reason', 'status'], 'fields' => ['name', 'extra_fare_reason'],
            'group' => 'Locations', 'icon' => 'bi-geo-alt', 'aliases' => 'location zone coverage area',
            'path' => 'edit_location/{id}', 'list_path' => 'locations', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'peak_fare', 'table' => 'manage_fare', 'scope' => 'both', 'permission' => 'manage_peak_based_fare', 'detail_permission' => 'manage_peak_based_fare',
            'title' => [], 'detail' => ['base_fare', 'min_fare', 'currency_code'], 'fields' => ['currency_code'],
            'group' => 'Peak fares', 'icon' => 'bi-graph-up-arrow', 'aliases' => 'peak fare surge pricing rate',
            'path' => 'edit_manage_fare/{id}', 'list_path' => 'manage_fare', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'toll_reason', 'table' => 'toll_reasons', 'scope' => 'both', 'permission' => 'view_additional_reason', 'detail_permission' => 'update_additional_reason',
            'title' => ['reason'], 'detail' => ['status'], 'fields' => ['reason'],
            'group' => 'Toll reasons', 'icon' => 'bi-signpost', 'aliases' => 'toll additional reason extra charge',
            'path' => 'edit-additional-reason/{id}', 'list_path' => 'additional-reasons', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'cancel_reason', 'table' => 'cancel_reasons', 'scope' => 'both', 'permission' => 'view_manage_reason', 'detail_permission' => 'update_manage_reason',
            'title' => ['reason'], 'detail' => ['cancelled_by', 'status'], 'fields' => ['reason', 'cancelled_by'],
            'group' => 'Cancel reasons', 'icon' => 'bi-x-octagon', 'aliases' => 'cancel cancellation reason',
            'path' => 'edit-cancel-reason/{id}', 'list_path' => 'cancel-reason', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'fee', 'table' => 'fees', 'scope' => 'both', 'permission' => 'manage_fees', 'detail_permission' => 'manage_fees',
            'title' => ['name'], 'detail' => ['value'], 'fields' => ['name', 'value'],
            'group' => 'Fees', 'icon' => 'bi-cash-coin', 'aliases' => 'fee charge commission',
            'path' => null, 'list_path' => 'fees', 'company_column' => null, 'updated' => null,
        ],

        // ------------------------------------------------------------------
        // Content & support
        // ------------------------------------------------------------------
        [
            'type' => 'help_article', 'table' => 'help', 'scope' => 'both', 'permission' => 'manage_help', 'detail_permission' => 'manage_help',
            'title' => ['question'], 'detail' => ['status'], 'fields' => ['question', 'answer'],
            'group' => 'Help articles', 'icon' => 'bi-question-circle', 'aliases' => 'help faq article knowledge',
            'path' => 'edit_help/{id}', 'list_path' => 'help', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'help_category', 'table' => 'help_category', 'scope' => 'both', 'permission' => 'manage_help', 'detail_permission' => 'manage_help',
            'title' => ['name'], 'detail' => ['description', 'status'], 'fields' => ['name', 'description'],
            'group' => 'Help categories', 'icon' => 'bi-collection', 'aliases' => 'help faq category',
            'path' => null, 'list_path' => 'help_category', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'help_subcategory', 'table' => 'help_subcategory', 'scope' => 'both', 'permission' => 'manage_help', 'detail_permission' => 'manage_help',
            'title' => ['name'], 'detail' => ['description', 'status'], 'fields' => ['name', 'description'],
            'group' => 'Help subcategories', 'icon' => 'bi-collection', 'aliases' => 'help faq subcategory',
            'path' => null, 'list_path' => 'help_subcategory', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'support', 'table' => 'supports', 'scope' => 'both', 'permission' => 'manage_support', 'detail_permission' => 'manage_support',
            'title' => ['name'], 'detail' => ['link', 'status'], 'fields' => ['name', 'link'],
            'group' => 'Support links', 'icon' => 'bi-life-preserver', 'aliases' => 'support help link',
            'path' => null, 'list_path' => 'support', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'support_issue', 'table' => 'support_issues', 'scope' => 'both', 'permission' => 'manage_support', 'detail_permission' => 'manage_support',
            'title' => ['subject'], 'detail' => ['category', 'priority', 'status'], 'fields' => ['subject', 'description', 'category', 'type'],
            'group' => 'Support issues', 'icon' => 'bi-life-preserver', 'aliases' => 'support issue ticket problem',
            'path' => 'support_issues/{id}', 'list_path' => 'support_issues', 'company_via' => ['users', 'user_id'], 'updated' => 'updated_at',
        ],
        [
            'type' => 'document', 'table' => 'documents', 'scope' => 'both', 'permission' => 'view_documents', 'detail_permission' => 'update_documents',
            'title' => ['document_name'], 'detail' => ['type', 'status'], 'fields' => ['document_name', 'type'],
            'group' => 'Documents', 'icon' => 'bi-file-earmark-text', 'aliases' => 'document file compliance upload',
            'path' => 'edit_document/{id}', 'list_path' => 'documents', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'notification_campaign', 'table' => 'notification_campaigns', 'scope' => 'both', 'permission' => 'manage_send_message',
            'title' => ['title'], 'detail' => ['channel', 'status'], 'fields' => ['title', 'message', 'channel'],
            'group' => 'Notification campaigns', 'icon' => 'bi-megaphone', 'aliases' => 'notification campaign message push',
            'path' => null, 'list_path' => 'send_message', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'promo_code', 'table' => 'promo_code', 'scope' => 'both', 'permission' => 'manage_promo_code', 'detail_permission' => 'manage_promo_code',
            'title' => ['code'], 'detail' => ['amount', 'currency_code', 'status'], 'fields' => ['code'],
            'group' => 'Promo codes', 'icon' => 'bi-ticket-perforated', 'aliases' => 'promo promotion coupon voucher discount',
            'path' => 'edit_promo_code/{id}', 'list_path' => 'promo_code', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'page', 'table' => 'pages', 'scope' => 'both', 'permission' => 'manage_static_pages', 'detail_permission' => 'manage_static_pages',
            'title' => ['name'], 'detail' => ['url', 'status'], 'fields' => ['name', 'url'],
            'group' => 'Pages', 'icon' => 'bi-file-earmark', 'aliases' => 'page static content cms',
            'path' => 'edit_page/{id}', 'list_path' => 'pages', 'company_column' => null, 'updated' => 'updated_at',
        ],

        // ------------------------------------------------------------------
        // Platform administration (admin scope only)
        // ------------------------------------------------------------------
        [
            'type' => 'company', 'table' => 'companies', 'scope' => 'admin', 'permission' => 'view_company', 'detail_permission' => 'update_company',
            'title' => ['name'], 'detail' => ['email', 'status'], 'fields' => ['name', 'legal_name', 'email', 'mobile_number', 'registration_reference'],
            'group' => 'Companies', 'icon' => 'fa-building', 'aliases' => 'company business tenant fleet',
            'path' => 'edit_company/{id}', 'list_path' => 'company', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'admin_user', 'table' => 'admins', 'scope' => 'admin', 'permission' => 'manage_admin',
            'title' => ['username'], 'detail' => ['email'], 'fields' => ['username', 'email'],
            'group' => 'Administrators', 'icon' => 'bi-person-badge', 'aliases' => 'admin administrator',
            'path' => null, 'list_path' => 'admin_user', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'role', 'table' => 'roles', 'scope' => 'admin', 'permission' => 'manage_admin',
            'title' => ['display_name', 'name'], 'detail' => ['description'], 'fields' => ['name', 'display_name', 'description'],
            'group' => 'Roles', 'icon' => 'bi-shield-check', 'aliases' => 'role permission access',
            'path' => null, 'list_path' => 'roles', 'company_column' => null, 'updated' => null,
        ],
        [
            'type' => 'global_vehicle_category', 'table' => 'global_vehicle_categories', 'scope' => 'admin', 'permission' => 'manage_global_vehicle_categories',
            'title' => ['name'], 'detail' => ['slug', 'status'], 'fields' => ['name', 'slug', 'description'],
            'group' => 'Global vehicle categories', 'icon' => 'bi-grid-3x3-gap', 'aliases' => 'global vehicle category',
            'path' => null, 'list_path' => 'saas-dispatch/global-vehicle-categories', 'company_column' => null, 'updated' => 'updated_at',
        ],
        [
            'type' => 'saas_support_ticket', 'table' => 'saas_support_tickets', 'scope' => 'admin', 'permission' => 'manage_saas_module',
            'title' => ['subject'], 'detail' => ['category', 'priority', 'status'], 'fields' => ['subject', 'description', 'category'],
            'group' => 'SaaS support tickets', 'icon' => 'bi-life-preserver', 'aliases' => 'support ticket saas',
            'path' => null, 'list_path' => 'saas-dispatch/support-system-status', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],

        // ------------------------------------------------------------------
        // Company (SaaS) workspace
        // ------------------------------------------------------------------
        [
            'type' => 'staff_member', 'table' => 'operation_team_members', 'scope' => 'company', 'permission' => 'manage_operation_team_members',
            'title' => ['first_name', 'last_name'], 'detail' => ['email', 'mobile_number', 'status'],
            'fields' => ['first_name', 'last_name', 'email', 'mobile_number'],
            'group' => 'Staff members', 'icon' => 'bi-people', 'aliases' => 'staff member team employee',
            'path' => 'operation-team/members/{id}/edit', 'list_path' => 'operation-team/members', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'staff_role', 'table' => 'operation_team_roles', 'scope' => 'company', 'permission' => 'manage_operation_team_roles',
            'title' => ['name'], 'detail' => ['status'], 'fields' => ['name'],
            'group' => 'Staff roles', 'icon' => 'bi-shield-check', 'aliases' => 'staff role permission',
            'path' => 'operation-team/roles/{id}/edit', 'list_path' => 'operation-team/roles', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'service_area', 'table' => 'company_service_areas', 'scope' => 'company', 'permission' => 'manage_company_service_areas',
            'feature' => 'saas', 'title' => ['name'], 'detail' => ['city', 'zone_name', 'status'],
            'fields' => ['name', 'city', 'zone_name', 'country'],
            'group' => 'Service areas', 'icon' => 'bi-geo-alt', 'aliases' => 'service area zone coverage',
            'path' => 'saas-dispatch/service-areas/{id}/edit', 'list_path' => 'saas-dispatch/service-areas', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'company_vehicle_type', 'table' => 'company_vehicle_types', 'scope' => 'company', 'permission' => 'manage_company_vehicle_types',
            'feature' => 'saas', 'title' => ['name'], 'detail' => ['description', 'status'],
            'fields' => ['name', 'description', 'slug'],
            'group' => 'Vehicle types', 'icon' => 'bi-truck-flatbed', 'aliases' => 'vehicle type category fleet',
            'path' => null, 'list_path' => 'saas-dispatch/vehicle-types', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'company_fee_setting', 'table' => 'company_fee_settings', 'scope' => 'company', 'permission' => 'manage_company_fees',
            'feature' => 'saas', 'title' => ['fee_name'], 'detail' => ['fee_type', 'status'],
            'fields' => ['fee_name', 'fee_type', 'applies_to'],
            'group' => 'Fees', 'icon' => 'bi-cash-coin', 'aliases' => 'fee setting charge',
            'path' => null, 'list_path' => 'saas-dispatch/fee-settings', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'company_fare_rule', 'table' => 'company_fare_rules', 'scope' => 'company', 'permission' => 'manage_company_fares',
            'feature' => 'saas', 'title' => [], 'detail' => ['currency_code', 'base_fare', 'status'],
            'fields' => ['currency_code'],
            'group' => 'Fare rules', 'icon' => 'bi-cash-stack', 'aliases' => 'fare rule pricing rate',
            'path' => 'saas-dispatch/fare-rules/{id}/edit', 'list_path' => 'saas-dispatch/fare-rules', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'message_thread', 'table' => 'company_message_threads', 'scope' => 'company', 'permission' => 'manage_company_messages',
            'feature' => 'messages', 'title' => ['subject'], 'detail' => ['status'], 'fields' => ['subject'],
            'group' => 'Messages', 'icon' => 'bi-chat-dots', 'aliases' => 'message thread conversation',
            'path' => null, 'list_path' => 'saas-dispatch/messages', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'corporate_account', 'table' => 'company_corporate_accounts', 'scope' => 'both', 'permission' => 'manage_company_accounts_invoicing',
            'feature' => 'accounts_invoicing', 'title' => ['account_name'], 'detail' => ['contact_email', 'status'],
            'fields' => ['account_name', 'contact_name', 'contact_email', 'contact_phone'],
            'group' => 'Corporate accounts', 'icon' => 'fa-building', 'aliases' => 'corporate employer account billing',
            'path' => 'saas-dispatch/accounts/accounts/{id}', 'path_admin' => 'companies/{company_id}/corporate-accounts/accounts/{id}',
            'list_path' => 'saas-dispatch/accounts', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
        [
            'type' => 'corporate_invoice', 'table' => 'company_corporate_invoices', 'scope' => 'both', 'permission' => 'manage_company_accounts_invoicing',
            'feature' => 'accounts_invoicing', 'title' => ['invoice_number'], 'detail' => ['status'],
            'fields' => ['invoice_number', 'status'],
            'group' => 'Invoices', 'icon' => 'bi-receipt', 'aliases' => 'invoice billing corporate',
            'path' => 'saas-dispatch/accounts/invoices/{id}', 'path_admin' => 'companies/{company_id}/corporate-accounts/invoices/{id}',
            'list_path' => 'saas-dispatch/accounts', 'company_column' => 'company_id', 'updated' => 'updated_at',
        ],
    ],
];
