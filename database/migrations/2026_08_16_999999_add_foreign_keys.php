<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applied_referrals', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'applied_referrals_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('applied_referrals', function (Blueprint $table) {
            $table->foreign(['user_id'], 'applied_referrals_user_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('cancel', function (Blueprint $table) {
            $table->foreign(['cancel_reason_id'], 'cancel_cancel_reason_id_foreign')->references(['id'])->on('cancel_reasons')->onDelete('CASCADE');
        });
        Schema::table('cancel', function (Blueprint $table) {
            $table->foreign(['trip_id'], 'cancel_trip_id_foreign')->references(['id'])->on('trips')->onDelete('CASCADE');
        });
        Schema::table('cancel', function (Blueprint $table) {
            $table->foreign(['user_id'], 'cancel_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('cancel_reason_translations', function (Blueprint $table) {
            $table->foreign(['cancel_reason_id'], 'cancel_reason_translations_cancel_reason_id_foreign')->references(['id'])->on('cancel_reasons')->onDelete('CASCADE');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->foreign(['country_id'], 'companies_country_id_foreign')->references(['id'])->on('country')->onDelete('CASCADE');
        });
        Schema::table('company_dispatch_settings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_cds_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_documents', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_documents_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_driver_fee_settings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_driver_fee_settings_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_fare_rules', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_cfr_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_fare_rules', function (Blueprint $table) {
            $table->foreign(['company_service_area_id'], 'sd_cfr_csa_fk')->references(['id'])->on('company_service_areas')->onDelete('SET NULL');
        });
        Schema::table('company_fare_rules', function (Blueprint $table) {
            $table->foreign(['company_vehicle_type_id'], 'sd_cfr_cvt_fk')->references(['id'])->on('company_vehicle_types')->onDelete('CASCADE');
        });
        Schema::table('company_fee_settings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_cfs_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_payout_credentials', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_payout_credentials_company_id_foreign')->references(['id'])->on('companies');
        });
        Schema::table('company_payout_preference', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_payout_preference_company_id_foreign')->references(['id'])->on('companies');
        });
        Schema::table('company_saas_settings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_saas_settings_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_saas_settings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_csset_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_service_areas', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_service_areas_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_service_areas', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_csa_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_vehicle_category_mappings_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_cvcm_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->foreign(['company_vehicle_type_id'], 'sd_cvcm_cvt_fk')->references(['id'])->on('company_vehicle_types')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->foreign(['global_vehicle_category_id'], 'sd_cvcm_gvc_fk')->references(['id'])->on('global_vehicle_categories')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_vehicle_types_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->foreign(['global_vehicle_category_id'], 'company_vehicle_types_global_vehicle_category_id_foreign')->references(['id'])->on('global_vehicle_categories')->onDelete('SET NULL');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'sd_cvt_company_fk')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->foreign(['global_vehicle_category_id'], 'sd_cvt_gvc_fk')->references(['id'])->on('global_vehicle_categories')->onDelete('SET NULL');
        });
        Schema::table('company_verification_otps', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_verification_otps_company_id_foreign')->references(['id'])->on('companies')->onDelete('CASCADE');
        });
        Schema::table('documents_langs', function (Blueprint $table) {
            $table->foreign(['documents_id'], 'documents_langs_documents_id_foreign')->references(['id'])->on('documents')->onDelete('CASCADE');
        });
        Schema::table('driver_address', function (Blueprint $table) {
            $table->foreign(['user_id'], 'driver_address_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('driver_location', function (Blueprint $table) {
            $table->foreign(['car_id'], 'driver_location_car_id_foreign')->references(['id'])->on('car_type');
        });
        Schema::table('driver_location', function (Blueprint $table) {
            $table->foreign(['user_id'], 'driver_location_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'driver_locations_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->foreign(['trip_id'], 'driver_locations_trip_id_foreign')->references(['id'])->on('trips')->onDelete('CASCADE');
        });
        Schema::table('driver_owe_amounts', function (Blueprint $table) {
            $table->foreign(['user_id'], 'driver_owe_amounts_user_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('driver_owe_amount_payments', function (Blueprint $table) {
            $table->foreign(['user_id'], 'driver_owe_amount_payments_user_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('driver_payment', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'driver_payment_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('driver_scores', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'driver_scores_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('driver_score_appeals', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'driver_score_appeals_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('driver_score_events', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'driver_score_events_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('emergency_sos', function (Blueprint $table) {
            $table->foreign(['country_id'], 'emergency_sos_country_id_foreign')->references(['id'])->on('country')->onDelete('CASCADE');
        });
        Schema::table('emergency_sos', function (Blueprint $table) {
            $table->foreign(['user_id'], 'emergency_sos_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('favorite_drivers', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'favorite_drivers_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('favorite_drivers', function (Blueprint $table) {
            $table->foreign(['rider_id'], 'favorite_drivers_rider_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('filter_objects', function (Blueprint $table) {
            $table->foreign(['filter_id'], 'filter_objects_filter_id_foreign')->references(['id'])->on('filter_options')->onDelete('CASCADE');
        });
        Schema::table('filter_options_translations', function (Blueprint $table) {
            $table->foreign(['filter_option_id'], 'filter_options_translations_filter_option_id_foreign')->references(['id'])->on('filter_options')->onDelete('CASCADE');
        });
        Schema::table('help', function (Blueprint $table) {
            $table->foreign(['category_id'], 'help_category_id_foreign')->references(['id'])->on('help_category');
        });
        Schema::table('help_category_lang', function (Blueprint $table) {
            $table->foreign(['category_id'], 'help_category_lang_category_id_foreign')->references(['id'])->on('help_category')->onDelete('CASCADE');
        });
        Schema::table('help_subcategory', function (Blueprint $table) {
            $table->foreign(['category_id'], 'help_subcategory_category_id_foreign')->references(['id'])->on('help_category');
        });
        Schema::table('help_sub_category_lang', function (Blueprint $table) {
            $table->foreign(['sub_category_id'], 'help_sub_category_lang_sub_category_id_foreign')->references(['id'])->on('help_subcategory')->onDelete('CASCADE');
        });
        Schema::table('help_translations', function (Blueprint $table) {
            $table->foreign(['help_id'], 'help_translations_help_id_foreign')->references(['id'])->on('help')->onDelete('CASCADE');
        });
        Schema::table('manage_fare', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'manage_fare_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('parcel_fares', function (Blueprint $table) {
            $table->foreign(['car_type_id'], 'parcel_fares_car_type_id_foreign')->references(['id'])->on('car_type')->onDelete('CASCADE');
        });
        Schema::table('parcel_fares', function (Blueprint $table) {
            $table->foreign(['location_id'], 'parcel_fares_location_id_foreign')->references(['id'])->on('locations')->onDelete('SET NULL');
        });
        Schema::table('parcel_fares', function (Blueprint $table) {
            $table->foreign(['parcel_category_id'], 'parcel_fares_parcel_category_id_foreign')->references(['id'])->on('parcel_categories')->onDelete('CASCADE');
        });
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->foreign(['parcel_category_id'], 'parcel_requests_parcel_category_id_foreign')->references(['id'])->on('parcel_categories')->onDelete('SET NULL');
        });
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->foreign(['parcel_task_id'], 'parcel_requests_parcel_task_id_foreign')->references(['id'])->on('parcel_tasks')->onDelete('SET NULL');
        });
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->foreign(['request_id'], 'parcel_requests_request_id_foreign')->references(['id'])->on('request')->onDelete('CASCADE');
        });
        Schema::table('payment', function (Blueprint $table) {
            $table->foreign(['trip_id'], 'payment_trip_id_foreign')->references(['id'])->on('trips')->onDelete('CASCADE');
        });
        Schema::table('payment_method', function (Blueprint $table) {
            $table->foreign(['user_id'], 'payment_method_user_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('peak_fare_details', function (Blueprint $table) {
            $table->foreign(['fare_id'], 'peak_fare_details_fare_id_foreign')->references(['id'])->on('manage_fare');
        });
        Schema::table('permission_role', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'permission_role_permission_id_foreign')->references(['id'])->on('permissions')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
        Schema::table('permission_role', function (Blueprint $table) {
            $table->foreign(['role_id'], 'permission_role_role_id_foreign')->references(['id'])->on('roles')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
        Schema::table('pool_trips', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'pool_trips_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('pool_trips', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'pool_trips_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('pool_trips', function (Blueprint $table) {
            $table->foreign(['toll_reason_id'], 'pool_trips_toll_reason_id_foreign')->references(['id'])->on('toll_reasons')->onDelete('CASCADE');
        });
        Schema::table('profile_picture', function (Blueprint $table) {
            $table->foreign(['user_id'], 'profile_picture_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('promo_code', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'promo_code_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('rating', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'rating_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('rating', function (Blueprint $table) {
            $table->foreign(['trip_id'], 'rating_trip_id_foreign')->references(['id'])->on('trips')->onDelete('CASCADE');
        });
        Schema::table('rating', function (Blueprint $table) {
            $table->foreign(['user_id'], 'rating_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('referral_users', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'referral_users_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('referral_users', function (Blueprint $table) {
            $table->foreign(['referral_id'], 'referral_users_referral_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('referral_users', function (Blueprint $table) {
            $table->foreign(['user_id'], 'referral_users_user_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('request', function (Blueprint $table) {
            $table->foreign(['car_id'], 'request_car_id_foreign')->references(['id'])->on('car_type')->onDelete('CASCADE');
        });
        Schema::table('request', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'request_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('request', function (Blueprint $table) {
            $table->foreign(['user_id'], 'request_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('rider_location', function (Blueprint $table) {
            $table->foreign(['user_id'], 'rider_location_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('role_user', function (Blueprint $table) {
            $table->foreign(['role_id'], 'role_user_role_id_foreign')->references(['id'])->on('roles')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
        Schema::table('role_user', function (Blueprint $table) {
            $table->foreign(['user_id'], 'role_user_user_id_foreign')->references(['id'])->on('admins')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
        Schema::table('schedule_cancel', function (Blueprint $table) {
            $table->foreign(['cancel_reason_id'], 'schedule_cancel_cancel_reason_id_foreign')->references(['id'])->on('cancel_reasons')->onDelete('CASCADE');
        });
        Schema::table('schedule_cancel', function (Blueprint $table) {
            $table->foreign(['schedule_ride_id'], 'schedule_cancel_schedule_ride_id_foreign')->references(['id'])->on('schedule_ride')->onDelete('CASCADE');
        });
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->foreign(['car_id'], 'schedule_ride_car_id_foreign')->references(['id'])->on('car_type')->onDelete('CASCADE');
        });
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->foreign(['company_id'], 'schedule_ride_company_id_foreign')->references(['id'])->on('companies');
        });
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->foreign(['user_id'], 'schedule_ride_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('support_issue_messages', function (Blueprint $table) {
            $table->foreign(['support_issue_id'], 'fk_support_issue_messages_issue_id')->references(['id'])->on('support_issues')->onDelete('CASCADE');
        });
        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->foreign(['entry_uuid'], 'telescope_entries_tags_entry_uuid_foreign')->references(['uuid'])->on('telescope_entries')->onDelete('CASCADE');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['car_id'], 'trips_car_id_foreign')->references(['id'])->on('car_type')->onDelete('CASCADE');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'trips_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'trips_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['parcel_category_id'], 'trips_parcel_category_id_foreign')->references(['id'])->on('parcel_categories')->onDelete('SET NULL');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['parcel_task_id'], 'trips_parcel_task_id_foreign')->references(['id'])->on('parcel_tasks')->onDelete('SET NULL');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['request_id'], 'trips_request_id_foreign')->references(['id'])->on('request')->onDelete('CASCADE');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['toll_reason_id'], 'trips_toll_reason_id_foreign')->references(['id'])->on('toll_reasons')->onDelete('CASCADE');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign(['user_id'], 'trips_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->foreign(['driver_id'], 'trip_requests_driver_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->foreign(['rider_id'], 'trip_requests_rider_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->foreign(['trip_id'], 'trip_requests_trip_id_foreign')->references(['id'])->on('trips')->onDelete('CASCADE');
        });
        Schema::table('trip_toll_reasons', function (Blueprint $table) {
            $table->foreign(['trip_id'], 'trip_toll_reasons_trip_id_foreign')->references(['id'])->on('trips')->onDelete('CASCADE');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['company_id'], 'users_company_id_foreign')->references(['id'])->on('companies');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['country_id'], 'users_country_id_foreign')->references(['id'])->on('country')->onDelete('CASCADE');
        });
        Schema::table('users_promo_code', function (Blueprint $table) {
            $table->foreign(['promo_code_id'], 'users_promo_code_promo_code_id_foreign')->references(['id'])->on('promo_code');
        });
        Schema::table('users_promo_code', function (Blueprint $table) {
            $table->foreign(['user_id'], 'users_promo_code_user_id_foreign')->references(['id'])->on('users');
        });
        Schema::table('user_levels', function (Blueprint $table) {
            $table->foreign(['inherits_from_id'], 'user_levels_inherits_from_id_foreign')->references(['id'])->on('user_levels')->onDelete('SET NULL');
        });
        Schema::table('vehicle', function (Blueprint $table) {
            $table->foreign(['company_id'], 'vehicle_company_id_foreign')->references(['id'])->on('companies');
        });
        Schema::table('vehicle', function (Blueprint $table) {
            $table->foreign(['user_id'], 'vehicle_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
        Schema::table('wallet', function (Blueprint $table) {
            $table->foreign(['currency_code'], 'wallet_currency_code_foreign')->references(['code'])->on('currency');
        });
        Schema::table('wallet', function (Blueprint $table) {
            $table->foreign(['user_id'], 'wallet_user_id_foreign')->references(['id'])->on('users')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::table('applied_referrals', function (Blueprint $table) {
            $table->dropForeign('applied_referrals_currency_code_foreign');
        });
        Schema::table('applied_referrals', function (Blueprint $table) {
            $table->dropForeign('applied_referrals_user_id_foreign');
        });
        Schema::table('cancel', function (Blueprint $table) {
            $table->dropForeign('cancel_cancel_reason_id_foreign');
        });
        Schema::table('cancel', function (Blueprint $table) {
            $table->dropForeign('cancel_trip_id_foreign');
        });
        Schema::table('cancel', function (Blueprint $table) {
            $table->dropForeign('cancel_user_id_foreign');
        });
        Schema::table('cancel_reason_translations', function (Blueprint $table) {
            $table->dropForeign('cancel_reason_translations_cancel_reason_id_foreign');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign('companies_country_id_foreign');
        });
        Schema::table('company_dispatch_settings', function (Blueprint $table) {
            $table->dropForeign('sd_cds_company_fk');
        });
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropForeign('company_documents_company_id_foreign');
        });
        Schema::table('company_driver_fee_settings', function (Blueprint $table) {
            $table->dropForeign('company_driver_fee_settings_company_id_foreign');
        });
        Schema::table('company_fare_rules', function (Blueprint $table) {
            $table->dropForeign('sd_cfr_company_fk');
        });
        Schema::table('company_fare_rules', function (Blueprint $table) {
            $table->dropForeign('sd_cfr_csa_fk');
        });
        Schema::table('company_fare_rules', function (Blueprint $table) {
            $table->dropForeign('sd_cfr_cvt_fk');
        });
        Schema::table('company_fee_settings', function (Blueprint $table) {
            $table->dropForeign('sd_cfs_company_fk');
        });
        Schema::table('company_payout_credentials', function (Blueprint $table) {
            $table->dropForeign('company_payout_credentials_company_id_foreign');
        });
        Schema::table('company_payout_preference', function (Blueprint $table) {
            $table->dropForeign('company_payout_preference_company_id_foreign');
        });
        Schema::table('company_saas_settings', function (Blueprint $table) {
            $table->dropForeign('company_saas_settings_company_id_foreign');
        });
        Schema::table('company_saas_settings', function (Blueprint $table) {
            $table->dropForeign('sd_csset_company_fk');
        });
        Schema::table('company_service_areas', function (Blueprint $table) {
            $table->dropForeign('company_service_areas_company_id_foreign');
        });
        Schema::table('company_service_areas', function (Blueprint $table) {
            $table->dropForeign('sd_csa_company_fk');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->dropForeign('company_vehicle_category_mappings_company_id_foreign');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->dropForeign('sd_cvcm_company_fk');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->dropForeign('sd_cvcm_cvt_fk');
        });
        Schema::table('company_vehicle_category_mappings', function (Blueprint $table) {
            $table->dropForeign('sd_cvcm_gvc_fk');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->dropForeign('company_vehicle_types_company_id_foreign');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->dropForeign('company_vehicle_types_global_vehicle_category_id_foreign');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->dropForeign('sd_cvt_company_fk');
        });
        Schema::table('company_vehicle_types', function (Blueprint $table) {
            $table->dropForeign('sd_cvt_gvc_fk');
        });
        Schema::table('company_verification_otps', function (Blueprint $table) {
            $table->dropForeign('company_verification_otps_company_id_foreign');
        });
        Schema::table('documents_langs', function (Blueprint $table) {
            $table->dropForeign('documents_langs_documents_id_foreign');
        });
        Schema::table('driver_address', function (Blueprint $table) {
            $table->dropForeign('driver_address_user_id_foreign');
        });
        Schema::table('driver_location', function (Blueprint $table) {
            $table->dropForeign('driver_location_car_id_foreign');
        });
        Schema::table('driver_location', function (Blueprint $table) {
            $table->dropForeign('driver_location_user_id_foreign');
        });
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropForeign('driver_locations_driver_id_foreign');
        });
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropForeign('driver_locations_trip_id_foreign');
        });
        Schema::table('driver_owe_amounts', function (Blueprint $table) {
            $table->dropForeign('driver_owe_amounts_user_id_foreign');
        });
        Schema::table('driver_owe_amount_payments', function (Blueprint $table) {
            $table->dropForeign('driver_owe_amount_payments_user_id_foreign');
        });
        Schema::table('driver_payment', function (Blueprint $table) {
            $table->dropForeign('driver_payment_driver_id_foreign');
        });
        Schema::table('driver_scores', function (Blueprint $table) {
            $table->dropForeign('driver_scores_driver_id_foreign');
        });
        Schema::table('driver_score_appeals', function (Blueprint $table) {
            $table->dropForeign('driver_score_appeals_driver_id_foreign');
        });
        Schema::table('driver_score_events', function (Blueprint $table) {
            $table->dropForeign('driver_score_events_driver_id_foreign');
        });
        Schema::table('emergency_sos', function (Blueprint $table) {
            $table->dropForeign('emergency_sos_country_id_foreign');
        });
        Schema::table('emergency_sos', function (Blueprint $table) {
            $table->dropForeign('emergency_sos_user_id_foreign');
        });
        Schema::table('favorite_drivers', function (Blueprint $table) {
            $table->dropForeign('favorite_drivers_driver_id_foreign');
        });
        Schema::table('favorite_drivers', function (Blueprint $table) {
            $table->dropForeign('favorite_drivers_rider_id_foreign');
        });
        Schema::table('filter_objects', function (Blueprint $table) {
            $table->dropForeign('filter_objects_filter_id_foreign');
        });
        Schema::table('filter_options_translations', function (Blueprint $table) {
            $table->dropForeign('filter_options_translations_filter_option_id_foreign');
        });
        Schema::table('help', function (Blueprint $table) {
            $table->dropForeign('help_category_id_foreign');
        });
        Schema::table('help_category_lang', function (Blueprint $table) {
            $table->dropForeign('help_category_lang_category_id_foreign');
        });
        Schema::table('help_subcategory', function (Blueprint $table) {
            $table->dropForeign('help_subcategory_category_id_foreign');
        });
        Schema::table('help_sub_category_lang', function (Blueprint $table) {
            $table->dropForeign('help_sub_category_lang_sub_category_id_foreign');
        });
        Schema::table('help_translations', function (Blueprint $table) {
            $table->dropForeign('help_translations_help_id_foreign');
        });
        Schema::table('manage_fare', function (Blueprint $table) {
            $table->dropForeign('manage_fare_currency_code_foreign');
        });
        Schema::table('parcel_fares', function (Blueprint $table) {
            $table->dropForeign('parcel_fares_car_type_id_foreign');
        });
        Schema::table('parcel_fares', function (Blueprint $table) {
            $table->dropForeign('parcel_fares_location_id_foreign');
        });
        Schema::table('parcel_fares', function (Blueprint $table) {
            $table->dropForeign('parcel_fares_parcel_category_id_foreign');
        });
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->dropForeign('parcel_requests_parcel_category_id_foreign');
        });
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->dropForeign('parcel_requests_parcel_task_id_foreign');
        });
        Schema::table('parcel_requests', function (Blueprint $table) {
            $table->dropForeign('parcel_requests_request_id_foreign');
        });
        Schema::table('payment', function (Blueprint $table) {
            $table->dropForeign('payment_trip_id_foreign');
        });
        Schema::table('payment_method', function (Blueprint $table) {
            $table->dropForeign('payment_method_user_id_foreign');
        });
        Schema::table('peak_fare_details', function (Blueprint $table) {
            $table->dropForeign('peak_fare_details_fare_id_foreign');
        });
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign('permission_role_permission_id_foreign');
        });
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign('permission_role_role_id_foreign');
        });
        Schema::table('pool_trips', function (Blueprint $table) {
            $table->dropForeign('pool_trips_currency_code_foreign');
        });
        Schema::table('pool_trips', function (Blueprint $table) {
            $table->dropForeign('pool_trips_driver_id_foreign');
        });
        Schema::table('pool_trips', function (Blueprint $table) {
            $table->dropForeign('pool_trips_toll_reason_id_foreign');
        });
        Schema::table('profile_picture', function (Blueprint $table) {
            $table->dropForeign('profile_picture_user_id_foreign');
        });
        Schema::table('promo_code', function (Blueprint $table) {
            $table->dropForeign('promo_code_currency_code_foreign');
        });
        Schema::table('rating', function (Blueprint $table) {
            $table->dropForeign('rating_driver_id_foreign');
        });
        Schema::table('rating', function (Blueprint $table) {
            $table->dropForeign('rating_trip_id_foreign');
        });
        Schema::table('rating', function (Blueprint $table) {
            $table->dropForeign('rating_user_id_foreign');
        });
        Schema::table('referral_users', function (Blueprint $table) {
            $table->dropForeign('referral_users_currency_code_foreign');
        });
        Schema::table('referral_users', function (Blueprint $table) {
            $table->dropForeign('referral_users_referral_id_foreign');
        });
        Schema::table('referral_users', function (Blueprint $table) {
            $table->dropForeign('referral_users_user_id_foreign');
        });
        Schema::table('request', function (Blueprint $table) {
            $table->dropForeign('request_car_id_foreign');
        });
        Schema::table('request', function (Blueprint $table) {
            $table->dropForeign('request_driver_id_foreign');
        });
        Schema::table('request', function (Blueprint $table) {
            $table->dropForeign('request_user_id_foreign');
        });
        Schema::table('rider_location', function (Blueprint $table) {
            $table->dropForeign('rider_location_user_id_foreign');
        });
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign('role_user_role_id_foreign');
        });
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign('role_user_user_id_foreign');
        });
        Schema::table('schedule_cancel', function (Blueprint $table) {
            $table->dropForeign('schedule_cancel_cancel_reason_id_foreign');
        });
        Schema::table('schedule_cancel', function (Blueprint $table) {
            $table->dropForeign('schedule_cancel_schedule_ride_id_foreign');
        });
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->dropForeign('schedule_ride_car_id_foreign');
        });
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->dropForeign('schedule_ride_company_id_foreign');
        });
        Schema::table('schedule_ride', function (Blueprint $table) {
            $table->dropForeign('schedule_ride_user_id_foreign');
        });
        Schema::table('support_issue_messages', function (Blueprint $table) {
            $table->dropForeign('fk_support_issue_messages_issue_id');
        });
        Schema::table('telescope_entries_tags', function (Blueprint $table) {
            $table->dropForeign('telescope_entries_tags_entry_uuid_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_car_id_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_currency_code_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_driver_id_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_parcel_category_id_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_parcel_task_id_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_request_id_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_toll_reason_id_foreign');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign('trips_user_id_foreign');
        });
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropForeign('trip_requests_driver_id_foreign');
        });
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropForeign('trip_requests_rider_id_foreign');
        });
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropForeign('trip_requests_trip_id_foreign');
        });
        Schema::table('trip_toll_reasons', function (Blueprint $table) {
            $table->dropForeign('trip_toll_reasons_trip_id_foreign');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_company_id_foreign');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_country_id_foreign');
        });
        Schema::table('users_promo_code', function (Blueprint $table) {
            $table->dropForeign('users_promo_code_promo_code_id_foreign');
        });
        Schema::table('users_promo_code', function (Blueprint $table) {
            $table->dropForeign('users_promo_code_user_id_foreign');
        });
        Schema::table('user_levels', function (Blueprint $table) {
            $table->dropForeign('user_levels_inherits_from_id_foreign');
        });
        Schema::table('vehicle', function (Blueprint $table) {
            $table->dropForeign('vehicle_company_id_foreign');
        });
        Schema::table('vehicle', function (Blueprint $table) {
            $table->dropForeign('vehicle_user_id_foreign');
        });
        Schema::table('wallet', function (Blueprint $table) {
            $table->dropForeign('wallet_currency_code_foreign');
        });
        Schema::table('wallet', function (Blueprint $table) {
            $table->dropForeign('wallet_user_id_foreign');
        });
    }
};
