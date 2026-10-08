<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Payment Gateways
Route::match(['post', 'get'], 'web_payment', 'PaymentController@payment')->name('paypal.view');
Route::post('payment/success', 'PaymentController@success')->name('payment.success');
Route::get('payment/cancel', 'PaymentController@cancel')->name('payment.cancel');

// Flutter Wave
Route::match(['post','get'],'flutterwave_web_payment', 'FlutterwaveController@flutterwave');
Route::match(['post','get'],'flutterwave_payment', 'FlutterwaveController@flutterwave_payment')->name('payment.flutterwave');
Route::match(['post','get'],'flutterwave_callback', 'FlutterwaveController@flutterwave_callback');

// Mpesa
Route::match(['post', 'get'], 'mpesa_web_payment', 'MpesaController@mpesa');
Route::match(['post', 'get'], 'mpesa_payment', 'MpesaController@customerMpesaSTKPush')->name('payment.mpesa');
Route::match(['post', 'get'], 'mpesa_callback', 'MpesaController@mpesaConfirmation');

// Paytm
Route::match(['post', 'get'], 'paytm_web_payment', 'PaymentController@paytm_payment')->name('paytm.view');
Route::match(['post', 'get'], 'paytm_callback', 'PaymentController@paytm_callback');

// Mollie
Route::match(['post', 'get'], 'mollie', 'MollieController@mollie');
Route::match(['post', 'get'], 'mollie_callback', 'MollieController@mollie_callback')->name('mollie.callback');

// Cron Jobs
Route::get('cron_request_car', 'CronController@requestCars');
Route::get('cron_offline', 'CronController@updateOfflineUsers');
Route::get('currency_cron', 'CronController@updateCurrency');
Route::get('update_referral_cron', 'CronController@updateReferralStatus');
Route::match(['get', 'post'], 'paypal_payout', 'CronController@updatePaypalPayouts');

// Version Check
Route::get('check_version', 'RiderController@check_version');

// Authentication Routes
Route::get('register', 'TokenAuthController@register');
Route::get('socialsignup', 'TokenAuthController@socialsignup');
Route::match(['get', 'post'], 'apple_callback', 'TokenAuthController@apple_callback');
Route::get('login', 'TokenAuthController@login');
Route::get('otp_verification', 'TokenAuthController@otp_verification');
Route::get('numbervalidation', 'TokenAuthController@numbervalidation');
Route::get('emailvalidation', 'TokenAuthController@emailvalidation');
Route::get('forgotpassword', 'TokenAuthController@forgotpassword');

// Miscellaneous
Route::get('company_list', 'TokenAuthController@company_list');
Route::get('language_list', 'TokenAuthController@language_list');
Route::get('currency_list', 'TokenAuthController@currency_list');
Route::get('static-pages/{slug}', 'StaticPageController@show');
Route::get('help/articles', 'HelpController@articles');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (JWT Middleware)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'jwt.verify'], function () {
    Route::prefix('corporate')->name('corporate.')->group(function () {
        $controller = \App\Http\Controllers\Api\CorporateController::class;
        Route::get('profile', [$controller, 'profile'])->name('profile');
        Route::get('dashboard', [$controller, 'dashboard'])->name('dashboard');
        Route::get('trips', [$controller, 'trips'])->name('trips');
        Route::get('trips/{trip}', [$controller, 'trip'])->name('trips.show');
        Route::get('users', [$controller, 'users'])->name('users');
        Route::get('invoices', [$controller, 'invoices'])->name('invoices');
        Route::get('invoices/{invoice}', [$controller, 'invoice'])->name('invoices.show');
        Route::get('invoices/{invoice}/download', [$controller, 'download'])->name('invoices.download');
    });
    // Common Data APIs
    Route::match(['get', 'post'], 'common_data', 'HomeController@commonData');
    Route::match(['get', 'post'], 'get_payment_list', 'HomeController@getPaymentList');
    Route::match(['get', 'post'], 'get_transaction_list', 'HomeController@getTransactionList');
    Route::match(['get', 'post'], 'get_outgoing_transaction', 'HomeController@getOutTransactionList');

    // Authentication
    Route::get('logout', 'TokenAuthController@logout');
    Route::get('language', 'TokenAuthController@language');
    Route::match(['get', 'post'], 'currency_conversion', 'TokenAuthController@currency_conversion');

    // Device and Location Updates
    Route::get('update_device', 'TokenAuthController@updateDevice');
    Route::get('updatelocation', 'DriverController@updateLocation');
    Route::post('update_rider_position', 'RiderController@updateRiderPosition');
    Route::get('updateriderlocation', 'RiderController@updateriderlocation');
    Route::get('update_rider_location', 'ProfileController@update_rider_location');

    // Driver-Specific APIs
    Route::get('check_status', 'DriverController@checkStatus');
    Route::post('update_document', 'DriverController@update_document');
    Route::post('update_vehicle', 'DriverController@updateVehicle');
    Route::post('delete_vehicle', 'DriverController@deleteVehicle');
    Route::get('update_default_vehicle', 'DriverController@updateDefaultVehicle');
    Route::get('cash_collected', 'DriverController@cash_collected');

    // Rider-Specific APIs
    Route::get('get_nearest_vehicles', 'RiderController@get_nearest_vehicles');
    Route::get('search_cars', 'RiderController@searchCars');
    Route::post('request_cars', 'RiderController@requestCars');
    Route::get('track_driver', 'RiderController@track_driver');
    Route::get('promo_details', 'RiderController@promo_details');
    Route::get('sos', 'RiderController@sos');
    Route::get('sosalert', 'RiderController@sosalert');
    Route::post('save_schedule_ride', 'RiderController@save_schedule_ride');
    Route::get('schedule_ride_cancel', 'RiderController@schedule_ride_cancel');
    Route::post('save_location', 'RiderController@save_location');
    Route::get('get_request_history', 'RiderController@get_request_history');
    Route::get('place_autocomplete', 'RiderController@place_autocomplete');
    Route::get('place_details', 'RiderController@place_details');
    Route::get('reverse_geocode', 'RiderController@reverse_geocode');
    Route::get('parcel_delivery_options', 'RiderController@parcel_delivery_options');
    Route::post('parcel_delivery_request', 'RiderController@parcel_delivery_request');
    Route::post('parcel_fare_estimate', 'RiderController@parcel_fare_estimate');
    Route::get('parcel/request/{requestId}', 'RiderController@getParcelRequestDetails');
    Route::get('parcel/trip/{tripId}', 'RiderController@getParcelTripDetails');
    Route::post('parcel/proof_of_delivery', 'RiderController@submitParcelProofOfDelivery');
    Route::get('favorite_drivers', 'FavoriteDriverController@index');
    Route::get('favorite_driver_status', 'FavoriteDriverController@status');
    Route::post('favorite_driver_toggle', 'FavoriteDriverController@toggle');

    // tawk.to Secure Mode Hash Generation
    Route::post('tawk-hash', 'RiderController@generateTawkHash');
    Route::get('support/issues', 'SupportIssueController@index');
    Route::post('support/issues', 'SupportIssueController@store');
    Route::get('support/issues/{issue}', 'SupportIssueController@show');
    Route::post('support/issues/{issue}/messages', 'SupportIssueController@message');

    // Trip Management
    Route::get('get_trip_details', 'TripController@get_trip_details');
    Route::get('get_pending_trip_details', 'TripController@get_pending_trip_details');
    Route::get('route_polyline', 'TripController@route_polyline');
    Route::get('routePolyline', 'TripController@routePolyline');
    Route::get('toll_reasons', 'TripController@toll_reasons');
    Route::get('cancel_reasons', 'TripController@cancel_reasons');
    Route::get('send_message', 'TripController@send_message');
    Route::get('get_past_trips', 'TripController@get_past_trips');
    Route::get('get_upcoming_trips', 'TripController@get_upcoming_trips');
    Route::get('get_pending_trips', 'TripController@get_pending_trips');
    Route::get('get_completed_trips', 'TripController@get_completed_trips');
    Route::get('arive_now', 'TripController@arriveNow');
    Route::get('begin_trip', 'TripController@beginTrip');
    Route::get('accept_request', 'TripController@acceptTrip');
    Route::get('cancel_trip_request', 'TripController@cancelTripRequest');
    Route::get('cancel_trip', 'TripController@cancel_trip');
    Route::get('cancel_pending_trip', 'TripController@cancel_pending_trip');
    Route::match(['get', 'post'], 'map_upload', 'TripController@map_upload');
    Route::match(['get', 'post'], 'end_trip', 'TripController@end_trip');

    // Profile Management
    Route::match(['get', 'post'], 'upload_profile_image', 'ProfileController@upload_profile_image');
    Route::get('get_rider_profile', 'ProfileController@get_rider_profile');
    Route::get('update_rider_profile', 'ProfileController@update_rider_profile');
    Route::get('get_driver_profile', 'ProfileController@get_driver_profile');
    Route::get('update_driver_profile', 'ProfileController@update_driver_profile');
    Route::get('update_vehicle_details', 'ProfileController@updateVehicleDetails');
    Route::get('vehicle_details', 'ProfileController@vehicleDetails');
    Route::get('update_user_currency', 'ProfileController@update_user_currency');
    Route::get('get_caller_detail', 'ProfileController@get_caller_detail');
    Route::get('vehicle_descriptions', 'ProfileController@vehicleDescriptions');
    Route::get('driver/company-options', 'DriverAccountController@companyOptions');
    Route::post('driver/company-assignment', 'DriverAccountController@updateCompany');
    Route::get('driver/account-delete-status', 'DriverAccountController@deleteEligibility');
    Route::post('driver/account-delete', 'DriverAccountController@deleteAccount');

    // Payment and Payout
    Route::post('add_wallet', 'AppPaymentController@appPaymentSuccess');
    Route::post('after_payment', 'AppPaymentController@appPaymentSuccess');
    Route::get('add_card_details', 'AppPaymentController@add_card_details');
    Route::get('get_card_details', 'AppPaymentController@get_card_details');
    Route::get('delete_card_details', 'AppPaymentController@delete_card_details');
    Route::get('default_card_details', 'AppPaymentController@default_card_details');
    Route::post('pay_to_admin', 'AppPaymentController@appPaymentSuccess');

    // Payout Management
    Route::get('stripe_supported_country_list', 'PayoutDetailController@stripeSupportedCountryList');
    Route::post('update_payout_preference', 'PayoutDetailController@updatePayoutPreference');
    Route::get('get_payout_list', 'PayoutDetailController@getPayoutPreference');
    Route::get('earning_list', 'PayoutDetailController@earningList');
    Route::get('weekly_trip', 'PayoutDetailController@weeklyTrip');
    Route::get('weekly_statement', 'PayoutDetailController@weeklyStatement');
    Route::get('daily_statement', 'PayoutDetailController@dailyStatement');
    Route::get('driver/withdrawal-requests', 'PayoutDetailController@withdrawalRequests');
    Route::post('driver/withdrawal-requests', 'PayoutDetailController@submitWithdrawalRequest');


    // Referrals
    Route::get('get_referral_details', 'ReferralsController@get_referral_details');

    // Earning
    Route::get('earning_chart', 'EarningController@earning_chart');
    Route::get('add_promo_code', 'EarningController@add_promo_code');

    // Rating
    Route::get('driver_rating', 'RatingController@driver_rating');
    Route::get('rider_feedback', 'RatingController@rider_feedback');
    Route::get('trip_rating', 'RatingController@trip_rating');
    Route::get('trips/{trip_id}/rating-status', 'RatingController@ratingStatus');
    Route::post('trips/{trip_id}/rate', 'RatingController@rateTrip');
    Route::get('get_invoice', 'RatingController@getinvoice');
    Route::get('get_driver_checkout_summary', 'RatingController@getDriverCheckoutSummary');

    // Map
    Route::get('heat_map', 'MapController@heat_map');

    // Country List
    Route::get('country_list', 'DriverController@country_list');

    // Reverb dispatch endpoints
    Route::post('trips/request', 'RealtimeTripController@requestTrip');
    Route::post('trips/request/{rideRequest}/cancel', 'RealtimeTripController@cancelRequest');
    Route::post('trips/request/{rideRequest}/timeout', 'RealtimeTripController@timeoutRequest');
    Route::post('trips/{trip}/cancel', 'RealtimeTripController@cancel');
    Route::get('trips/current', 'RealtimeTripController@current');
    Route::get('trips/{trip}', 'RealtimeTripController@show');

    Route::post('driver/online', 'RealtimeDriverController@online');
    Route::post('driver/offline', 'RealtimeDriverController@offline');
    Route::get('driver/home', 'RealtimeDriverController@home');
    Route::get('driver/session/status', 'RealtimeDriverController@sessionStatus');
    Route::get('driver/session/history', 'RealtimeDriverController@sessionHistory');
    Route::post('driver/location/update', 'RealtimeDriverController@updateLocation')->middleware('throttle:60,1');
    Route::get('driver/current-request', 'RealtimeDriverController@currentRequest');
    Route::get('driver/score', 'DriverScoreController@show');
    Route::get('driver/score/history', 'DriverScoreController@history');
    Route::get('driver/score/settings', 'DriverScoreController@settings');
    Route::post('driver/score/appeal', 'DriverScoreController@appeal');

    Route::post('trip-requests/{tripRequest}/accept', 'RealtimeDriverController@accept');
    Route::post('trip-requests/{tripRequest}/reject', 'RealtimeDriverController@reject');
    Route::post('trips/{trip}/arrived', 'RealtimeTripController@arrived');
    Route::post('trips/{trip}/start', 'RealtimeTripController@start');
    Route::post('trips/{trip}/complete', 'RealtimeTripController@complete');
    Route::post('trips/{trip}/cancel-by-driver', 'RealtimeTripController@cancel');

    Route::post('driver/parcel/{trip_id}/pickup', 'DriverController@confirmParcelPickup');
    Route::post('driver/parcel/{trip_id}/delivery', 'DriverController@confirmParcelDelivery');
    Route::post('driver/parcel/proof_of_delivery', 'DriverController@submitParcelProofOfDelivery');
});

?>
