<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('facke_user/{count}/{user_type}', 'BulKUserController@createUser');
Route::get('add_trip_details','BulKUserController@tripDetails');
Route::get('company_details/{count}','BulKUserController@companyDetails');
Route::get('document_details/{count}/{type}','BulKUserController@documentDetails');
Route::get('help_category_pages/{count}','BulKUserController@helpCategoryPages');
Route::get('help_subcategory_pages/{count}/{category}','BulKUserController@helpSubCategoryPages');
Route::get('help_bulk/{count}/{category}/{sub_category}','BulKUserController@helpPages');
// Route::get('help/{count}/{category}/{sub_category}','BulKUserController@helpSubCategoryPages');

Route::group(['prefix' => 'admin', 'middleware' =>'admin_auth'], function () {
	Route::get('login', 'AdminController@login')->name('admin_login');
});

Route::match(['get', 'post'],'admin/authenticate', 'AdminController@authenticate');

Route::group(['prefix' => (LOGIN_USER_TYPE=='company')?'company':'admin', 'middleware' => ['admin_guest','company_approval']], function () {

	Route::redirect('/',LOGIN_USER_TYPE.'/dashboard');
	Route::get('dashboard', 'AdminController@index');
	Route::get('lang/{lang}', ['as' => 'switch.locale', 'uses' => 'LanguageController@changeLocale']);

	if (LOGIN_USER_TYPE == 'admin') {
		Route::get('logout', 'AdminController@logout');
	}

	// Modules management (admin only)
	if (LOGIN_USER_TYPE == 'admin') {
		Route::get('modules', 'ModuleController@index')->name('admin.modules.index');
		Route::get('modules/upload', 'ModuleController@create')->name('admin.modules.create');
		Route::post('modules', 'ModuleController@store')->name('admin.modules.store');
		Route::post('modules/{module}/enable', 'ModuleController@enable')->name('admin.modules.enable');
		Route::post('modules/{module}/disable', 'ModuleController@disable')->name('admin.modules.disable');
		Route::post('modules/{module}/migrate', 'ModuleController@migrate')->name('admin.modules.migrate');
		Route::post('modules/rediscover', 'ModuleController@rediscover')->name('admin.modules.rediscover');
		Route::delete('modules/{module}', 'ModuleController@destroy')->name('admin.modules.destroy');
	}

	// Admin Users and permission routes
	Route::group(['middleware' => 'admin_can:manage_admin'], function() {
        Route::get('admin_user', 'AdminController@view');
        Route::match(['GET', 'POST'],'add_admin_user', 'AdminController@add');
        Route::match(['GET', 'POST'],'edit_admin_users/{id}', 'AdminController@update');
        Route::match(['GET', 'POST'],'delete_admin_user/{id}', 'AdminController@delete');

        Route::get('roles', 'RolesController@index');
        Route::match(['GET', 'POST'], 'add_role', 'RolesController@add');
        Route::match(['GET', 'POST'], 'edit_role/{id}', 'RolesController@update')->where('id', '[0-9]+');
        Route::get('delete_role/{id}', 'RolesController@delete')->where('id', '[0-9]+');
    });

    // Manage Help Routes
    Route::group(['middleware' => 'admin_can:manage_help'],function () {
        Route::get('help_category', 'HelpCategoryController@index');
        Route::match(['GET', 'POST'], 'add_help_category', 'HelpCategoryController@add');
        Route::match(['GET', 'POST'], 'edit_help_category/{id}', 'HelpCategoryController@update')->where('id', '[0-9]+');
        Route::get('delete_help_category/{id}', 'HelpCategoryController@delete')->where('id', '[0-9]+');
        Route::get('help_subcategory', 'HelpSubCategoryController@index');
        Route::match(['GET', 'POST'], 'add_help_subcategory', 'HelpSubCategoryController@add');
        Route::match(['GET', 'POST'], 'edit_help_subcategory/{id}', 'HelpSubCategoryController@update')->where('id', '[0-9]+');
        Route::get('delete_help_subcategory/{id}', 'HelpSubCategoryController@delete')->where('id', '[0-9]+');
        Route::get('help', 'HelpController@index');
        Route::match(['GET', 'POST'], 'add_help', 'HelpController@add');
        Route::match(['GET', 'POST'], 'edit_help/{id}', 'HelpController@update')->where('id', '[0-9]+');
        Route::get('delete_help/{id}', 'HelpController@delete')->where('id', '[0-9]+');
        Route::post('ajax_help_subcategory/{id}', 'HelpController@ajax_help_subcategory')->where('id', '[0-9]+');
    });

	// Send message
	Route::group(['middleware' => 'admin_can:manage_send_message'], function() {
		Route::get('notifications', 'NotificationManagementController@index')->name('admin.notifications');
		Route::post('notifications', 'NotificationManagementController@update')->name('admin.notifications.update');
		Route::match(['GET', 'POST'], 'send_message', 'SendmessageController@index')->name('admin.send_message');
		Route::post('get_send_users', 'SendmessageController@get_send_users');
		Route::post('send_message/{id}/resend', 'SendmessageController@resend')->where('id', '[0-9]+')->name('admin.send_message.resend');
		Route::post('send_message/{id}/toggle', 'SendmessageController@toggle')->where('id', '[0-9]+')->name('admin.send_message.toggle');
		Route::post('send_message/{id}/delete', 'SendmessageController@destroy')->where('id', '[0-9]+')->name('admin.send_message.delete');
	});
	
	// Manage Rider
	Route::get('rider', 'RiderController@index')->middleware('admin_can:view_rider');
	Route::match(['GET', 'POST'], 'add_rider', 'RiderController@add')->middleware('admin_can:create_rider');
	Route::match(['GET', 'POST'], 'edit_rider/{id}', 'RiderController@update')->middleware('admin_can:update_rider');
	Route::match(['GET', 'POST'], 'delete_rider/{id}', 'RiderController@delete')->middleware('admin_can:delete_rider');

	// Manage Driver
	Route::get('driver', 'DriverController@index')->middleware('admin_can:view_driver');
	Route::match(['GET', 'POST'], 'add_driver', 'DriverController@add')->middleware('admin_can:create_driver');
	Route::match(['GET', 'POST'], 'edit_driver/{id}', 'DriverController@update')->middleware('admin_can:update_driver');
	Route::match(['GET', 'POST'], 'delete_driver/{id}', 'DriverController@delete')->middleware('admin_can:delete_driver');
	Route::post('get_documents', 'DriverController@get_documents');
	Route::post('get_documents_html', 'DriverController@get_documents_html');
	Route::get('document-reviews/driver-documents', 'DocumentReviewController@driverDocuments')->middleware('admin_can:view_driver');
	Route::post('document-reviews/driver-documents/{document}', 'DocumentReviewController@updateDriverDocument')->middleware('admin_can:update_driver');
	Route::get('document-reviews/profile-images', 'DocumentReviewController@profileImages')->middleware('admin_can:view_driver');
	Route::post('document-reviews/profile-images/{userId}', 'DocumentReviewController@updateProfileImage')->middleware('admin_can:update_driver');

	// Manage Company
	Route::get('company', 'CompanyController@index')->middleware('admin_can:view_company');
	Route::match(['GET', 'POST'], 'add_company', 'CompanyController@add')->middleware('admin_can:create_company');
	Route::match(['GET', 'POST'], 'edit_company/{id}', 'CompanyController@update')->middleware('admin_can:update_company');
	Route::match(['GET', 'POST'], 'delete_company/{id}', 'CompanyController@delete')->middleware('admin_can:delete_company');
	Route::get('document-reviews/company-documents', 'DocumentReviewController@companyDocuments')->middleware('admin_can:view_company');
	Route::post('document-reviews/company-documents/{document}', 'DocumentReviewController@updateCompanyDocument')->middleware('admin_can:update_company');
	Route::get('company-onboarding', 'CompanyOnboardingController@index')->middleware('admin_can:view_company');
	Route::get('company-onboarding/{company}', 'CompanyOnboardingController@show')->middleware('admin_can:view_company');
	Route::post('company-onboarding/{company}/approve', 'CompanyOnboardingController@approve')->middleware('admin_can:update_company');
	Route::post('company-onboarding/{company}/reject', 'CompanyOnboardingController@reject')->middleware('admin_can:update_company');
	Route::post('company-onboarding/{company}/request-resubmission', 'CompanyOnboardingController@requestResubmission')->middleware('admin_can:update_company');
	Route::post('company-onboarding/{company}/suspend', 'CompanyOnboardingController@suspend')->middleware('admin_can:update_company');

	// Manage Statements
	Route::group(['middleware' =>  'admin_can:manage_statements'], function() {
		Route::post('get_statement_counts', 'StatementController@get_statement_counts');
		Route::get('statements/{type}', 'StatementController@index');
		Route::get('view_driver_statement/{driver_id}', 'StatementController@view_driver_statement');
		Route::post('driver_statement', 'StatementController@driver_statement');
		Route::post('statement_all', 'StatementController@custom_statement');
	});

	// Manage Location
	Route::group(['middleware' => 'admin_can:manage_locations'], function() {
		Route::get('locations', 'LocationsController@index');
	    Route::match(['GET', 'POST'],'add_location', 'LocationsController@add')->name('admin.add_location');
	    Route::match(['GET', 'POST'],'edit_location/{id}', 'LocationsController@update')->name('admin.edit_location');
	    Route::match(['GET', 'POST'],'location_extra_fare/{id}', 'LocationsController@extraFare')->name('admin.location_extra_fare');
	    Route::post('locations/bulk-extra-fare', 'LocationsController@bulkExtraFare')->name('admin.locations.bulk_extra_fare');
	    Route::post('locations/toggle-extra-fare/{id}', 'LocationsController@toggleExtraFare')->name('admin.locations.toggle_extra_fare');
	    Route::post('locations/toggle-status/{id}', 'LocationsController@toggleStatus')->name('admin.locations.toggle_status');
	    Route::get('delete_location/{id}', 'LocationsController@delete');
	});

	/* Manage Manage Mobile App Version Route Start*/

		Route::group(['middleware' => 'admin_can:manage_mobile_app_version'], function() {
			Route::get('mobile_app_version', 'AppVersionController@index');
		    Route::match(['GET', 'POST'],'add_app_version', 'AppVersionController@add')->name('admin.add_app_version');
		    Route::match(['GET', 'POST'],'edit_app_version/{id}', 'AppVersionController@update')->name('admin.edit_app_version');
		    Route::get('delete_app_version/{id}', 'AppVersionController@delete');
		});
		
	/* Manage Manage Mobile App Version Route End*/

    // Manage Peak Based Fare Details
	Route::group(['middleware' => 'admin_can:manage_peak_based_fare'], function() {
		Route::get('manage_fare', 'ManageFareController@index');
	    Route::match(['GET', 'POST'],'add_manage_fare', 'ManageFareController@add')->name('admin.add_manage_fare');
	    Route::match(['GET', 'POST'],'edit_manage_fare/{id}', 'ManageFareController@update')->name('admin.edit_manage_fare');
	    Route::get('delete_manage_fare/{id}', 'ManageFareController@delete');
	});

	// Manage Toll fare Details
	Route::get('additional-reasons', 'TollReasonController@index')->middleware('admin_can:view_additional_reason');
    Route::match(['GET', 'POST'],'add-additional-reason', 'TollReasonController@add')->middleware('admin_can:create_additional_reason');
    Route::match(['GET', 'POST'],'edit-additional-reason/{id}', 'TollReasonController@update')->middleware('admin_can:update_additional_reason');
    Route::get('delete-additional-reason/{id}', 'TollReasonController@delete')->middleware('admin_can:delete_additional_reason');

	// Map
	Route::group(['middleware' =>  'admin_can:manage_map'], function() {
		Route::get('map', 'MapController@drivers');
		Route::get('map/on-trip', 'MapController@onTrip');
		Route::get('map/idle', 'MapController@idle');
		Route::get('map/customers', 'MapController@customers');
		Route::get('map/alerts', 'MapController@alerts');
		Route::post('map/alerts/{id}/resolve', 'MapController@resolveAlert')->where('id', '[0-9]+');
		Route::get('map/live-data/{mode?}', 'MapController@liveData');
		Route::match(['GET', 'POST'], 'mapdata', 'MapController@mapdata');
	});
	Route::group(['middleware' =>  'admin_can:manage_heat_map|manage_admin'], function() {
		Route::match(['GET', 'POST'], 'heat-map', 'MapController@heat_map');
		Route::match(['GET', 'POST'], 'heat-map/compare', 'MapController@heat_map_compare');
		Route::match(['GET', 'POST'], 'heat-map-data', 'MapController@heat_map_data');
		Route::match(['GET', 'POST'], 'heat-map-compare-data', 'MapController@heat_map_compare_data');
	});

	// Manage Vehicle Type
	Route::group(['middleware' =>  'admin_can:manage_vehicle_type'], function() {
		Route::get('vehicle_type', 'VehicleTypeController@index');
		Route::match(['GET', 'POST'], 'add_vehicle_type', 'VehicleTypeController@add');
		Route::match(['GET', 'POST'], 'edit_vehicle_type/{id}', 'VehicleTypeController@update');
		Route::match(['GET', 'POST'], 'delete_vehicle_type/{id}', 'VehicleTypeController@delete');
	});

	// Manage Referrals Routes
	Route::group(['prefix' => 'referrals'], function() {
		Route::get('rider', 'ReferralsController@index')->middleware('admin_can:manage_rider_referrals');
		Route::get('driver', 'ReferralsController@index')->middleware('admin_can:manage_driver_referrals');
		Route::get('{id}', 'ReferralsController@referral_details');
	});

	// Manage Vehicle
	Route::group(['middleware' =>  'admin_can:manage_vehicle'], function() {
		Route::get('vehicle', 'VehicleController@index');
		Route::match(['GET', 'POST'], 'add_vehicle', 'VehicleController@add');
		Route::get('document-reviews/vehicle-documents', 'DocumentReviewController@vehicleDocuments');
		Route::post('document-reviews/vehicle-documents/{document}', 'DocumentReviewController@updateDriverDocument');
		Route::post('manage_vehicle/{company_id}/get_driver', 'VehicleController@get_driver')->name('admin.get_driver');
		Route::match(['GET', 'POST'], 'edit_vehicle/{id}', 'VehicleController@update');
		Route::post('vehicle/toggle-status/{id}', 'VehicleController@toggleStatus')->where('id', '[0-9]+');
		Route::match(['GET', 'POST'], 'delete_vehicle/{id}', 'VehicleController@delete');
		Route::match(['GET', 'POST'], 'validate_vehicle_number','VehicleController@validate_vehicle_number');
		Route::match(['GET', 'POST'], 'check_default','VehicleController@check_default');
	});

	// Trips
	Route::group(['middleware' =>  'admin_can:manage_trips'], function() {
		Route::match(['GET', 'POST'], 'trips', 'TripsController@index');
		Route::get('view_trips/{id}', 'TripsController@view');
		Route::post('trips/payout/{id}', 'TripsController@payout');
		Route::get('trips/export/{from}/{to}', 'TripsController@export');
	});

	// Manage Company Payout Routes
	Route::group(['middleware' =>  'admin_can:manage_company_payment'], function() {
		Route::get('payout/company/overall', 'CompanyPayoutController@overall_payout');
		Route::get('weekly_payout/company/{company_id}', 'CompanyPayoutController@weekly_payout');
		Route::get('per_week_report/company/{company_id}/{start_date}/{end_date}', 'CompanyPayoutController@payout_per_week_report');
		Route::get('per_day_report/company/{company_id}/{date}', 'CompanyPayoutController@payout_per_day_report');
		Route::post('make_payout/company', 'CompanyPayoutController@payout_to_company');
	});

	// Manage Driver Payout Routes
	Route::group(['middleware' =>  'admin_can:manage_driver_payments'], function() {
		Route::get('payout/overall', 'PayoutController@overall_payout');
		Route::get('weekly_payout/{driver_id}', 'PayoutController@weekly_payout');
		Route::get('per_week_report/{driver_id}/{start_date}/{end_date}', 'PayoutController@payout_per_week_report');
		Route::get('per_day_report/{driver_id}/{date}', 'PayoutController@payout_per_day_report');
		Route::post('make_payout', 'PayoutController@payout_to_driver');
	});

	Route::match(['GET', 'POST'], 'company-driver-fees', 'CompanyDriverFeeSettingsController@index')->middleware('admin_can:manage_company_driver_fees');

	// Manage Wallet
	Route::group(['prefix' => 'wallet', 'middleware' =>  'admin_can:manage_wallet'], function() {
		Route::get('{user_type}', 'WalletController@index')->name('wallet');
		Route::match(['GET', 'POST'], 'add/{user_type}', 'WalletController@add')->name('add_wallet');
		Route::match(['GET', 'POST'], 'edit/{user_type}/{id}', 'WalletController@update')->where('id', '[0-9]+')->name('edit_wallet');
		Route::get('delete/{user_type}/{id}', 'WalletController@delete')->where('id', '[0-9]+')->name('delete_wallet');
	});
	
	// Owe Amount
	Route::group(['middleware' =>  'admin_can:manage_owe_amount'], function() {
		Route::match(['GET', 'POST'], 'owe', 'OweController@index')->name('owe');
		Route::match(['GET', 'POST'], 'company_owe/{id}', 'OweController@company_index')->name('owe');
		Route::get('details/{type}', 'OweController@owe_details')->name('owe_details');
		Route::get('update_driver_payment', 'OweController@update_payment')->name('update_payment');
		Route::post('update_owe_payment', 'OweController@updateOwePayment')->name('update_owe_payment');
		Route::post('update_company_payment', 'OweController@update_company_payment')->name('update_company_payment');
	});

	// Company Owe amount
	Route::get('driver_payment', 'OweController@driver_payment')->name('driver_payment');

	// Manage Promo Code
	Route::group(['middleware' =>  'admin_can:manage_promo_code'], function() {
		Route::get('promo_code', 'PromocodeController@index');
		Route::match(['GET', 'POST'], 'add_promo_code', 'PromocodeController@add');		
		Route::match(['GET', 'POST'], 'edit_promo_code/{id}', 'PromocodeController@update')->where('id', '[0-9]+');
		Route::get('delete_promo_code/{id}', 'PromocodeController@delete');
	});

	// Payments
	Route::group(['middleware' =>  'admin_can:manage_payments'], function() {
		Route::match(['GET', 'POST'], 'payments', 'PaymentsController@index');
		Route::get('view_payments/{id}', 'PaymentsController@view');
		Route::get('view_payments/{id}/receipt/print', 'PaymentsController@printReceipt');
		Route::get('view_payments/{id}/receipt/download', 'PaymentsController@downloadReceipt');
		Route::post('view_payments/{id}/receipt/send', 'PaymentsController@sendReceipt');
		Route::get('payments/export/{from}/{to}', 'PaymentsController@export');
	});

	// Cancelled Trips
	Route::group(['middleware' =>  'admin_can:manage_cancel_trips'], function() {
		Route::get('cancel_trips', 'TripsController@cancel_trips');
	});

	// Manage Cancel reasons
	Route::get('cancel-reason', 'CancelReasonController@index')->middleware('admin_can:view_manage_reason');
	Route::match(['GET', 'POST'], 'add-cancel-reason', 'CancelReasonController@add')->middleware('admin_can:create_manage_reason');
	Route::match(['GET', 'POST'], 'edit-cancel-reason/{id}', 'CancelReasonController@update')->where('id', '[0-9]+')->middleware('admin_can:update_manage_reason');
	Route::get('delete-cancel-reason/{id}', 'CancelReasonController@delete')->middleware('admin_can:delete_manage_reason');

	// Manage Rating
	Route::group(['middleware' =>  'admin_can:manage_rating'], function() {
		Route::get('rating', 'RatingController@index');
		Route::get('delete_rating/{id}', 'RatingController@delete');
	});

	Route::group(['prefix' => 'driver-score'], function () {
		Route::get('/', 'DriverScoreController@index');
		Route::get('settings', 'DriverScoreController@settings');
		Route::post('settings', 'DriverScoreController@updateSettings');
		Route::get('appeals', 'DriverScoreAppealController@index');
		Route::get('appeals/{id}', 'DriverScoreAppealController@show')->where('id', '[0-9]+');
		Route::post('appeals/{id}', 'DriverScoreAppealController@update')->where('id', '[0-9]+');
		Route::post('events/{eventId}/exclude', 'DriverScoreController@excludeEvent')->where('eventId', '[0-9]+');
		Route::post('events/{eventId}/restore', 'DriverScoreController@restoreEvent')->where('eventId', '[0-9]+');
		Route::get('{driverId}', 'DriverScoreController@show')->where('driverId', '[0-9]+');
		Route::post('{driverId}/recalculate', 'DriverScoreController@recalculate')->where('driverId', '[0-9]+');
		Route::post('{driverId}/marketplace-access', 'DriverScoreController@overrideMarketplaceAccess')->where('driverId', '[0-9]+');
	});

	// Manage fees
	Route::group(['middleware' =>  'admin_can:manage_fees'], function() {
		Route::match(['GET', 'POST'], 'fees', 'FeesController@index');
	});

	// Manage Referral Settings
	Route::group(['middleware' =>  'admin_can:manage_referral_settings'], function() {
		Route::get('referral_settings', 'ReferralSettingsController@index');
		Route::post('update_referral_settings', 'ReferralSettingsController@update');
	});

	// SiteSetting
	Route::match(['GET', 'POST'], 'site_setting', 'SiteSettingsController@index')->middleware('admin_can:manage_site_settings');
	
	// Manage Api credentials
	Route::match(['GET', 'POST'], 'api_credentials', 'ApiCredentialsController@index')->middleware('admin_can:manage_api_credentials');

	// Manage Payment Gateway
	Route::group(['middleware' =>  'admin_can:manage_payment_gateway'], function() {
		Route::match(['GET', 'POST'], 'payment_gateway', 'PaymentGatewayController@index');
	});

	// Request
	Route::group(['middleware' =>  'admin_can:manage_requests'], function() {
		Route::get('detail_request/{id}', 'RequestController@detail_request');
		Route::match(['GET', 'POST'], 'request', 'RequestController@index');
	});

	// Join us management
	Route::group(['middleware' =>  'admin_can:manage_join_us'], function() {
		Route::match(['GET', 'POST'], 'join_us', 'JoinUsController@index');
	});

	// Manage Static pages
	Route::group(['middleware' =>  'admin_can:manage_static_pages'], function() {
		Route::get('pages', 'PagesController@index');
		Route::match(['GET', 'POST'], 'add_page', 'PagesController@add');
		Route::match(['GET', 'POST'], 'edit_page/{id}', 'PagesController@update')->where('id', '[0-9]+');
		Route::get('delete_page/{id}', 'PagesController@delete')->where('id', '[0-9]+');
	});

	// Manage Meta
	Route::group(['middleware' =>  'admin_can:manage_metas'], function() {
		Route::match(['GET', 'POST'], 'metas', 'MetasController@index');
		Route::match(['GET', 'POST'], 'edit_meta/{id}', 'MetasController@update')->where('id', '[0-9]+');
	});

	// Manage Currency Routes
	Route::group(['middleware' =>  'admin_can:manage_currency'], function() {
		Route::get('currency', 'CurrencyController@index');
		Route::match(['GET', 'POST'], 'add_currency', 'CurrencyController@add');
		Route::match(['GET', 'POST'], 'edit_currency/{id}', 'CurrencyController@update')->where('id', '[0-9]+');
		Route::get('delete_currency/{id}', 'CurrencyController@delete')->where('id', '[0-9]+');
	});

	// Manage Document Routes
	Route::get('documents', 'DocumentsController@index')->middleware('admin_can:view_documents');
	Route::match(['GET', 'POST'], 'add_document', 'DocumentsController@add')->middleware('admin_can:create_documents');
	Route::get('edit_document/{id}', 'DocumentsController@edit')->where('id', '[0-9]+')->middleware('admin_can:update_documents');
	Route::get('delete_document/{id}', 'DocumentsController@delete')->where('id', '[0-9]+')->middleware('admin_can:delete_documents');

	// Manage Language Routes
	Route::group(['middleware' =>  'admin_can:manage_language'], function() {
		Route::get('language', 'LanguageController@index');
		Route::match(['GET', 'POST'], 'add_language', 'LanguageController@add');
		Route::match(['GET', 'POST'], 'edit_language/{id}', 'LanguageController@update')->where('id', '[0-9]+');
		Route::get('delete_language/{id}', 'LanguageController@delete')->where('id', '[0-9]+');
	});

	// Manage Country
	Route::group(['middleware' => 'admin_can:manage_country'],function () {
        Route::get('country', 'CountryController@index');
        Route::match(['GET', 'POST'], 'add_country', 'CountryController@add');
        Route::match(['GET', 'POST'], 'edit_country/{id}', 'CountryController@update')->where('id', '[0-9]+');
        Route::get('delete_country/{id}', 'CountryController@delete')->where('id', '[0-9]+');
    });

    // Manage Support
	Route::group(['middleware' => 'admin_can:manage_support'],function () {
		Route::get('support', 'SupportController@index');
		Route::match(['GET', 'POST'], 'add_support', 'SupportController@add');
		Route::match(['GET', 'POST'], 'edit_support/{id}', 'SupportController@update')->where('id', '[0-9]+')->name('edit');
		Route::get('delete_support/{id}', 'SupportController@delete')->where('id', '[0-9]+')->name('delete');
		Route::get('support_issues', 'SupportIssueController@index');
		Route::get('support_issues/{id}', 'SupportIssueController@show')->where('id', '[0-9]+');
		Route::post('support_issues/{id}/reply', 'SupportIssueController@reply')->where('id', '[0-9]+');
		Route::post('support_issues/{id}/status', 'SupportIssueController@updateStatus')->where('id', '[0-9]+');
	});


    // Manage App Banners
	Route::group(['middleware' => 'admin_can:manage_banner'],function () {
		Route::get('banner', 'BannerController@index');
		Route::match(['GET', 'POST'], 'add_banner', 'BannerController@add');
		Route::match(['GET', 'POST'], 'edit_banner/{id}', 'BannerController@update')->where('id', '[0-9]+')->name('edit');
		Route::get('delete_banner/{id}', 'BannerController@delete')->where('id', '[0-9]+')->name('delete');
	});
	
	// Manage Email Settings Routes
	Route::match(['GET', 'POST'], 'email_settings', 'EmailController@index')->middleware(['admin_can:manage_email_settings']);
    Route::match(['GET', 'POST'], 'send_email', 'EmailController@send_email')->middleware(['admin_can:manage_send_email']);

    // Manage Make  Vehicle reasons
	Route::get('vehicle_make', 'MakeVehicleController@index')->middleware('admin_can:view_vehicle_make');
	Route::match(['GET', 'POST'], 'add-vehicle-make', 'MakeVehicleController@add')->middleware('admin_can:create_vehicle_make');
	Route::match(['GET', 'POST'], 'edit-vehicle-make/{id}', 'MakeVehicleController@update')->where('id', '[0-9]+')->middleware('admin_can:update_vehicle_make');
	Route::get('delete-vehicle_make/{id}', 'MakeVehicleController@delete')->middleware('admin_can:delete_vehicle_make');

	Route::get('vehicle_model', 'VehicleModelController@index')->middleware('admin_can:view_vehicle_model');
	Route::match(['GET', 'POST'], 'add-vehicle_model', 'VehicleModelController@add')->middleware('admin_can:create_vehicle_model');
	Route::match(['GET', 'POST'], 'edit-vehicle_model/{id}', 'VehicleModelController@update')->where('id', '[0-9]+')->middleware('admin_can:update_vehicle_model');
	Route::get('delete_vehicle_model/{id}', 'VehicleModelController@delete')->middleware('admin_can:delete_vehicle_make');
	Route::post('makelist','VehicleModelController@makeListValue');

	// Route::match(['GET','POST'],'get_locale', 'LocaleFileController@get_locale')->name('language.locale');
	// Route::post('update_locale', 'LocaleFileController@update_locale')->name('language.update_locale');

	// Parcel Management Routes
	Route::group(['middleware' => 'admin_can:manage_parcel_categories'], function() {
		Route::get('parcel_categories', 'ParcelCategoryController@index');
		Route::match(['GET', 'POST'], 'add_parcel_category', 'ParcelCategoryController@add');
		Route::match(['GET', 'PUT'], 'edit_parcel_category/{id}', 'ParcelCategoryController@update');
		Route::match(['GET', 'POST'], 'delete_parcel_category/{id}', 'ParcelCategoryController@delete');
		Route::get('toggle_parcel_category_status/{id}', 'ParcelCategoryController@toggle_status');
	});

	Route::group(['middleware' => 'admin_can:manage_parcel_package_sizes'], function() {
		Route::get('parcel_package_sizes', 'ParcelPackageSizeController@index');
		Route::match(['GET', 'POST'], 'add_parcel_package_size', 'ParcelPackageSizeController@add');
		Route::match(['GET', 'PUT'], 'edit_parcel_package_size/{id}', 'ParcelPackageSizeController@update');
		Route::match(['GET', 'POST'], 'delete_parcel_package_size/{id}', 'ParcelPackageSizeController@delete');
		Route::get('toggle_parcel_package_size_status/{id}', 'ParcelPackageSizeController@toggle_status');
	});

	Route::group(['middleware' => 'admin_can:manage_parcel_tasks'], function() {
		Route::get('parcel_tasks', 'ParcelTaskController@index');
		Route::match(['GET', 'POST'], 'add_parcel_task', 'ParcelTaskController@add');
		Route::match(['GET', 'PUT'], 'edit_parcel_task/{id}', 'ParcelTaskController@update');
		Route::match(['GET', 'POST'], 'delete_parcel_task/{id}', 'ParcelTaskController@delete');
		Route::get('toggle_parcel_task_status/{id}', 'ParcelTaskController@toggle_status');
	});

	Route::group(['middleware' => 'admin_can:manage_parcel_pricing'], function() {
		Route::get('parcel_pricing', 'ParcelPricingController@index');
		Route::match(['GET', 'POST'], 'add_parcel_pricing', 'ParcelPricingController@add');
		Route::match(['GET', 'PUT'], 'edit_parcel_pricing/{id}', 'ParcelPricingController@update');
		Route::match(['GET', 'POST'], 'delete_parcel_pricing/{id}', 'ParcelPricingController@delete');
		Route::match(['GET', 'POST'], 'bulk_update_parcel_pricing', 'ParcelPricingController@bulk_update');
	});

	Route::group(['middleware' => 'admin_can:manage_parcel_settings'], function() {
		Route::get('parcel_settings', 'ParcelSettingsController@index');
		Route::put('parcel_settings', 'ParcelSettingsController@update');
	});

	Route::group(['middleware' => 'admin_can:manage_parcel_trips'], function() {
		Route::get('parcel_trips', 'ParcelTripsController@index');
		Route::get('view_parcel_trip/{id}', 'ParcelTripsController@view');
		Route::match(['GET', 'POST'], 'cancel_parcel_trip/{id}', 'ParcelTripsController@cancel');
		Route::get('export_parcel_trips', 'ParcelTripsController@export');
	});
});
