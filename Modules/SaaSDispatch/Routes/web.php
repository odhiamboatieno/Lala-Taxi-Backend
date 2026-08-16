<?php

use Illuminate\Support\Facades\Route;
use Modules\SaaSDispatch\Http\Controllers\CompanyAccountsController;
use Modules\SaaSDispatch\Http\Controllers\CompanyDispatchSettingController;
use Modules\SaaSDispatch\Http\Controllers\CompanyDocumentsController;
use Modules\SaaSDispatch\Http\Controllers\CompanyFareRuleController;
use Modules\SaaSDispatch\Http\Controllers\CompanyFeeSettingController;
use Modules\SaaSDispatch\Http\Controllers\CompanyMessagesController;
use Modules\SaaSDispatch\Http\Controllers\CompanyNetworkingController;
use Modules\SaaSDispatch\Http\Controllers\CompanyPaymentGatewayController;
use Modules\SaaSDispatch\Http\Controllers\CompanySaaSController;
use Modules\SaaSDispatch\Http\Controllers\CompanyServiceAreaController;
use Modules\SaaSDispatch\Http\Controllers\CompanySupportTicketController;
use Modules\SaaSDispatch\Http\Controllers\CompanyVehicleTypeController;
use Modules\SaaSDispatch\Http\Controllers\CompanyDataSuiteController;
use Modules\SaaSDispatch\Http\Controllers\GlobalVehicleCategoryController;
use Modules\SaaSDispatch\Http\Controllers\SaaSFeatureModuleController;
use Modules\SaaSDispatch\Http\Controllers\SaaSDispatchDashboardController;

Route::group([
    'prefix' => (defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE == 'company') ? 'company' : 'admin',
    'middleware' => ['web', 'admin_guest'],
], function () {
    Route::get('saas-dispatch', [SaaSDispatchDashboardController::class, 'index'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.dashboard');

    Route::post('saas-dispatch/module-toggle', [SaaSDispatchDashboardController::class, 'toggleModule'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.module.toggle');

    Route::get('saas-dispatch/modules', [SaaSFeatureModuleController::class, 'index'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.modules.index');
    Route::put('saas-dispatch/modules/{module}', [SaaSFeatureModuleController::class, 'update'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.modules.update');

    Route::get('saas-dispatch/support-system-status', [CompanySupportTicketController::class, 'adminIndex'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.admin.support.index');
    Route::post('saas-dispatch/support-system-status', [CompanySupportTicketController::class, 'adminStore'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.admin.support.store');
    Route::post('saas-dispatch/support-system-status/{ticket}/reply', [CompanySupportTicketController::class, 'adminReply'])
        ->middleware('admin_can:manage_saas_module')
        ->name('saasdispatch.admin.support.reply');

    Route::get('saas-dispatch/companies', [CompanySaaSController::class, 'index'])
        ->middleware('admin_can:manage_saas_companies|manage_company_service_areas|manage_company_vehicle_types|manage_company_fares|manage_company_fees|manage_company_dispatch_settings|manage_company_branding|manage_company_domains')
        ->name('saasdispatch.companies.index');

    Route::get('saas-dispatch/settings', [CompanySaaSController::class, 'editSelf'])
        ->middleware('admin_can:manage_saas_module|manage_company_branding|manage_company_domains')
        ->name('saasdispatch.self.settings.edit');
    Route::put('saas-dispatch/settings', [CompanySaaSController::class, 'updateSelf'])
        ->middleware('admin_can:manage_saas_module|manage_company_branding|manage_company_domains')
        ->name('saasdispatch.self.settings.update');

    Route::get('saas-dispatch/global-vehicle-categories', [GlobalVehicleCategoryController::class, 'index'])
        ->middleware('admin_can:manage_global_vehicle_categories')
        ->name('saasdispatch.global-vehicle-categories.index');
    Route::post('saas-dispatch/global-vehicle-categories', [GlobalVehicleCategoryController::class, 'store'])
        ->middleware('admin_can:manage_global_vehicle_categories')
        ->name('saasdispatch.global-vehicle-categories.store');
    Route::put('saas-dispatch/global-vehicle-categories/{globalVehicleCategory}', [GlobalVehicleCategoryController::class, 'update'])
        ->middleware('admin_can:manage_global_vehicle_categories')
        ->name('saasdispatch.global-vehicle-categories.update');
    Route::delete('saas-dispatch/global-vehicle-categories/{globalVehicleCategory}', [GlobalVehicleCategoryController::class, 'destroy'])
        ->middleware('admin_can:manage_global_vehicle_categories')
        ->name('saasdispatch.global-vehicle-categories.destroy');

    Route::get('saas-dispatch/companies/{company}/settings', [CompanySaaSController::class, 'edit'])
        ->middleware('admin_can:manage_saas_companies|manage_company_branding|manage_company_domains')
        ->name('saasdispatch.companies.settings.edit');
    Route::put('saas-dispatch/companies/{company}/settings', [CompanySaaSController::class, 'update'])
        ->middleware('admin_can:manage_saas_companies|manage_company_branding|manage_company_domains')
        ->name('saasdispatch.companies.settings.update');

    Route::get('saas-dispatch/service-areas', [CompanyServiceAreaController::class, 'indexSelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.index');
    Route::get('saas-dispatch/service-areas/create', [CompanyServiceAreaController::class, 'createSelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.create');
    Route::post('saas-dispatch/service-areas', [CompanyServiceAreaController::class, 'storeSelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.store');
    Route::get('saas-dispatch/service-areas/{companyServiceArea}/edit', [CompanyServiceAreaController::class, 'editSelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.edit');
    Route::put('saas-dispatch/service-areas/{companyServiceArea}', [CompanyServiceAreaController::class, 'updateSelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.update');
    Route::delete('saas-dispatch/service-areas/{companyServiceArea}', [CompanyServiceAreaController::class, 'destroySelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.destroy');
    Route::post('saas-dispatch/service-areas/bulk-extra-fare', [CompanyServiceAreaController::class, 'bulkExtraFare'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.bulk-extra-fare');
    Route::post('saas-dispatch/service-areas/toggle-extra-fare/{companyServiceArea}', [CompanyServiceAreaController::class, 'toggleExtraFare'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.toggle-extra-fare');
    Route::post('saas-dispatch/service-areas/toggle-status/{companyServiceArea}', [CompanyServiceAreaController::class, 'toggleStatus'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.service-areas.toggle-status');

    Route::get('saas-dispatch/manage-zones', [CompanyServiceAreaController::class, 'indexSelf'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.self.manage-zones.index');

    Route::get('saas-dispatch/vehicle-types', [CompanyVehicleTypeController::class, 'indexSelf'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.self.vehicle-types.index');
    Route::get('saas-dispatch/vehicle-types/create', [CompanyVehicleTypeController::class, 'createSelf'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.self.vehicle-types.create');
    Route::post('saas-dispatch/vehicle-types', [CompanyVehicleTypeController::class, 'storeSelf'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.self.vehicle-types.store');
    Route::get('saas-dispatch/vehicle-types/{companyVehicleType}/edit', [CompanyVehicleTypeController::class, 'editSelf'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.self.vehicle-types.edit');
    Route::put('saas-dispatch/vehicle-types/{companyVehicleType}', [CompanyVehicleTypeController::class, 'updateSelf'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.self.vehicle-types.update');
    Route::delete('saas-dispatch/vehicle-types/{companyVehicleType}', [CompanyVehicleTypeController::class, 'destroySelf'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.self.vehicle-types.destroy');

    Route::get('saas-dispatch/fare-rules', [CompanyFareRuleController::class, 'indexSelf'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.self.fare-rules.index');
    Route::get('saas-dispatch/fare-rules/create', [CompanyFareRuleController::class, 'createSelf'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.self.fare-rules.create');
    Route::post('saas-dispatch/fare-rules', [CompanyFareRuleController::class, 'storeSelf'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.self.fare-rules.store');
    Route::get('saas-dispatch/fare-rules/{companyFareRule}/edit', [CompanyFareRuleController::class, 'editSelf'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.self.fare-rules.edit');
    Route::put('saas-dispatch/fare-rules/{companyFareRule}', [CompanyFareRuleController::class, 'updateSelf'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.self.fare-rules.update');
    Route::delete('saas-dispatch/fare-rules/{companyFareRule}', [CompanyFareRuleController::class, 'destroySelf'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.self.fare-rules.destroy');

    Route::get('saas-dispatch/fee-settings', [CompanyFeeSettingController::class, 'indexSelf'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.self.fee-settings.index');
    Route::post('saas-dispatch/fee-settings', [CompanyFeeSettingController::class, 'storeSelf'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.self.fee-settings.store');
    Route::put('saas-dispatch/fee-settings/{companyFeeSetting}', [CompanyFeeSettingController::class, 'updateSelf'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.self.fee-settings.update');
    Route::delete('saas-dispatch/fee-settings/{companyFeeSetting}', [CompanyFeeSettingController::class, 'destroySelf'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.self.fee-settings.destroy');

    Route::get('saas-dispatch/dispatch-settings', [CompanyDispatchSettingController::class, 'editSelf'])
        ->middleware('admin_can:manage_company_dispatch_settings')
        ->name('saasdispatch.self.dispatch-settings.edit');
    Route::put('saas-dispatch/dispatch-settings', [CompanyDispatchSettingController::class, 'updateSelf'])
        ->middleware('admin_can:manage_company_dispatch_settings')
        ->name('saasdispatch.self.dispatch-settings.update');

    Route::get('saas-dispatch/payment-gateways', [CompanyPaymentGatewayController::class, 'editSelf'])
        ->middleware('admin_can:manage_company_payment_gateways')
        ->name('saasdispatch.self.payment-gateways.edit');
    Route::match(['POST', 'PUT'], 'saas-dispatch/payment-gateways', [CompanyPaymentGatewayController::class, 'updateSelf'])
        ->middleware('admin_can:manage_company_payment_gateways')
        ->name('saasdispatch.self.payment-gateways.update');

    Route::get('saas-dispatch/documents', [CompanyDocumentsController::class, 'index'])
        ->middleware('admin_can:manage_company_documents')
        ->name('saasdispatch.self.documents.index');

    Route::get('saas-dispatch/networking', [CompanyNetworkingController::class, 'index'])
        ->middleware('admin_can:manage_company_networking')
        ->name('saasdispatch.self.networking.index');
    Route::post('saas-dispatch/networking/partners', [CompanyNetworkingController::class, 'storePartner'])
        ->middleware('admin_can:manage_company_networking')
        ->name('saasdispatch.self.networking.partners.store');
    Route::post('saas-dispatch/networking/shares', [CompanyNetworkingController::class, 'share'])
        ->middleware('admin_can:manage_company_networking')
        ->name('saasdispatch.self.networking.share');
    Route::post('saas-dispatch/networking/shares/{share}/respond', [CompanyNetworkingController::class, 'respond'])
        ->middleware('admin_can:manage_company_networking')
        ->name('saasdispatch.self.networking.respond');

    Route::get('saas-dispatch/accounts', [CompanyAccountsController::class, 'index'])
        ->middleware('admin_can:manage_company_accounts_invoicing')
        ->name('saasdispatch.self.accounts.index');
    Route::post('saas-dispatch/accounts/accounts', [CompanyAccountsController::class, 'storeAccount'])
        ->middleware('admin_can:manage_company_accounts_invoicing')
        ->name('saasdispatch.self.accounts.accounts.store');
    Route::post('saas-dispatch/accounts/invoices', [CompanyAccountsController::class, 'storeInvoice'])
        ->middleware('admin_can:manage_company_accounts_invoicing')
        ->name('saasdispatch.self.accounts.invoices.store');

    Route::get('saas-dispatch/data-suite', [CompanyDataSuiteController::class, 'index'])
        ->middleware('admin_can:manage_company_data_suite')
        ->name('saasdispatch.self.data-suite.index');

    Route::get('saas-dispatch/messages', [CompanyMessagesController::class, 'index'])
        ->middleware('admin_can:manage_company_messages')
        ->name('saasdispatch.self.messages.index');
    Route::post('saas-dispatch/messages/threads', [CompanyMessagesController::class, 'storeThread'])
        ->middleware('admin_can:manage_company_messages')
        ->name('saasdispatch.self.messages.threads.store');
    Route::post('saas-dispatch/messages/threads/{thread}/reply', [CompanyMessagesController::class, 'reply'])
        ->middleware('admin_can:manage_company_messages')
        ->name('saasdispatch.self.messages.reply');

    Route::get('saas-dispatch/support-system-status', [CompanySupportTicketController::class, 'index'])
        ->middleware('admin_can:manage_company_support_tickets')
        ->name('saasdispatch.self.support.index');
    Route::post('saas-dispatch/support-system-status', [CompanySupportTicketController::class, 'store'])
        ->middleware('admin_can:manage_company_support_tickets')
        ->name('saasdispatch.self.support.store');
    Route::post('saas-dispatch/support-system-status/{ticket}/reply', [CompanySupportTicketController::class, 'reply'])
        ->middleware('admin_can:manage_company_support_tickets')
        ->name('saasdispatch.self.support.reply');

    Route::get('saas-dispatch/companies/{company}/service-areas', [CompanyServiceAreaController::class, 'index'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.index');
    Route::get('saas-dispatch/companies/{company}/service-areas/create', [CompanyServiceAreaController::class, 'create'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.create');
    Route::post('saas-dispatch/companies/{company}/service-areas', [CompanyServiceAreaController::class, 'store'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.store');
    Route::get('saas-dispatch/companies/{company}/service-areas/{companyServiceArea}/edit', [CompanyServiceAreaController::class, 'edit'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.edit');
    Route::put('saas-dispatch/companies/{company}/service-areas/{companyServiceArea}', [CompanyServiceAreaController::class, 'update'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.update');
    Route::delete('saas-dispatch/companies/{company}/service-areas/{companyServiceArea}', [CompanyServiceAreaController::class, 'destroy'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.destroy');
    Route::post('saas-dispatch/companies/{company}/service-areas/bulk-extra-fare', [CompanyServiceAreaController::class, 'bulkExtraFare'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.bulk-extra-fare');
    Route::post('saas-dispatch/companies/{company}/service-areas/toggle-extra-fare/{companyServiceArea}', [CompanyServiceAreaController::class, 'toggleExtraFare'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.toggle-extra-fare');
    Route::post('saas-dispatch/companies/{company}/service-areas/toggle-status/{companyServiceArea}', [CompanyServiceAreaController::class, 'toggleStatus'])
        ->middleware('admin_can:manage_company_service_areas')
        ->name('saasdispatch.companies.service-areas.toggle-status');

    Route::get('saas-dispatch/companies/{company}/vehicle-types', [CompanyVehicleTypeController::class, 'index'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.companies.vehicle-types.index');
    Route::get('saas-dispatch/companies/{company}/vehicle-types/create', [CompanyVehicleTypeController::class, 'create'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.companies.vehicle-types.create');
    Route::post('saas-dispatch/companies/{company}/vehicle-types', [CompanyVehicleTypeController::class, 'store'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.companies.vehicle-types.store');
    Route::get('saas-dispatch/companies/{company}/vehicle-types/{companyVehicleType}/edit', [CompanyVehicleTypeController::class, 'edit'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.companies.vehicle-types.edit');
    Route::put('saas-dispatch/companies/{company}/vehicle-types/{companyVehicleType}', [CompanyVehicleTypeController::class, 'update'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.companies.vehicle-types.update');
    Route::delete('saas-dispatch/companies/{company}/vehicle-types/{companyVehicleType}', [CompanyVehicleTypeController::class, 'destroy'])
        ->middleware('admin_can:manage_company_vehicle_types')
        ->name('saasdispatch.companies.vehicle-types.destroy');

    Route::get('saas-dispatch/companies/{company}/fare-rules', [CompanyFareRuleController::class, 'index'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.companies.fare-rules.index');
    Route::get('saas-dispatch/companies/{company}/fare-rules/create', [CompanyFareRuleController::class, 'create'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.companies.fare-rules.create');
    Route::post('saas-dispatch/companies/{company}/fare-rules', [CompanyFareRuleController::class, 'store'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.companies.fare-rules.store');
    Route::get('saas-dispatch/companies/{company}/fare-rules/{companyFareRule}/edit', [CompanyFareRuleController::class, 'edit'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.companies.fare-rules.edit');
    Route::put('saas-dispatch/companies/{company}/fare-rules/{companyFareRule}', [CompanyFareRuleController::class, 'update'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.companies.fare-rules.update');
    Route::delete('saas-dispatch/companies/{company}/fare-rules/{companyFareRule}', [CompanyFareRuleController::class, 'destroy'])
        ->middleware('admin_can:manage_company_fares')
        ->name('saasdispatch.companies.fare-rules.destroy');

    Route::get('saas-dispatch/companies/{company}/fee-settings', [CompanyFeeSettingController::class, 'index'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.companies.fee-settings.index');
    Route::post('saas-dispatch/companies/{company}/fee-settings', [CompanyFeeSettingController::class, 'store'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.companies.fee-settings.store');
    Route::put('saas-dispatch/companies/{company}/fee-settings/{companyFeeSetting}', [CompanyFeeSettingController::class, 'update'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.companies.fee-settings.update');
    Route::delete('saas-dispatch/companies/{company}/fee-settings/{companyFeeSetting}', [CompanyFeeSettingController::class, 'destroy'])
        ->middleware('admin_can:manage_company_fees')
        ->name('saasdispatch.companies.fee-settings.destroy');

    Route::get('saas-dispatch/companies/{company}/dispatch-settings', [CompanyDispatchSettingController::class, 'edit'])
        ->middleware('admin_can:manage_company_dispatch_settings')
        ->name('saasdispatch.companies.dispatch-settings.edit');
    Route::put('saas-dispatch/companies/{company}/dispatch-settings', [CompanyDispatchSettingController::class, 'update'])
        ->middleware('admin_can:manage_company_dispatch_settings')
        ->name('saasdispatch.companies.dispatch-settings.update');
});
