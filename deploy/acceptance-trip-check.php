<?php

/** Real HTTP trips in the guarded local stack. Financial records are retained. */
require __DIR__.'/acceptance-http-check.php';

use App\Models\Trips;
use Illuminate\Support\Facades\DB;

$call = function (string $phone, string $method, string $path, array $payload = []) use ($json, $tokens, $expect): array {
    [$status, $body] = $json($method, $path, ['headers' => ['Authorization' => 'Bearer '.$tokens[$phone]], 'json' => $payload]);
    $expect($status === 200 && ($body['status_code'] ?? null) === '1', $method.' '.$path.' succeeds (HTTP '.$status.', '.($body['status_message'] ?? 'no status message').')');
    return $body;
};
$car = DB::table('car_type')->where('car_name', 'Lala Standard QA')->value('id');
$gatewayCount = DB::table('payment_transactions')->count();
$corporateTrips = [];
foreach ([['700000130', '700000140', 'personal'], ['700000111', '700000140', 'personal'],
    ['700000110', '700000140', 'corporate'], ['700000120', '700000141', 'corporate']] as [$rider, $driver, $mode]) {
    $call($driver, 'POST', '/api/driver/online', ['lat' => -1.2676, 'lng' => 36.8108]);
    [$currentStatus, $current] = $json('GET', '/api/trips/current', ['headers' => ['Authorization' => 'Bearer '.$tokens[$rider]]]);
    if ($currentStatus === 200 && !empty($current['trip']['id'])) {
        $id = (int) $current['trip']['id'];
        $existing = Trips::findOrFail($id);
        $expect($existing->driver->mobile_number === $driver && $existing->billing_mode === $mode, 'Interrupted acceptance resumes only its assigned QA driver and payer');
    } else {
    $booking = $call($rider, 'POST', '/api/trips/request', [
        'pickup_latitude' => -1.2676, 'pickup_longitude' => 36.8108, 'drop_latitude' => -1.2615, 'drop_longitude' => 36.8028,
        'pickup_location' => 'Westlands QA pickup', 'drop_location' => 'Sarit QA drop-off',
        'car_id' => $car, 'billing_mode' => $mode, 'payment_mode' => $mode === 'personal' ? 'Cash' : 'Corporate',
        'estimated_distance' => '2', 'estimated_duration' => '5', 'estimated_fare' => '250',
        'operator_type' => 'company', 'operator_id' => $mode === 'corporate' && $rider === '700000120' ? 2 : 1,
    ]);
    $offer = $call($driver, 'GET', '/api/driver/current-request')['trip_request'];
    $expect((int) $offer['request_id'] === (int) $booking['request_id'], 'Assigned driver receives the requested offer');
    if ($mode === 'corporate') {
        $expect(($offer['billing']['mode'] ?? null) === 'corporate' && ($offer['billing']['collection_required'] ?? true) === false, 'Corporate offer requires no driver collection');
    }
    $accepted = $call($driver, 'POST', '/api/trip-requests/'.$offer['id'].'/accept');
    $id = (int) $accepted['trip']['id'];
    }
    if (!in_array(Trips::findOrFail($id)->status, ['started', 'Payment', 'Rating', 'Completed'], true)) {
    $call($driver, 'POST', '/api/trips/'.$id.'/arrived');
    $call($driver, 'POST', '/api/trips/'.$id.'/start');
    }
    foreach (Trips::findOrFail($id)->status === 'started' ? [[-1.266, 36.809], [-1.264, 36.806], [-1.2615, 36.8028]] : [] as [$lat, $lng]) {
        $call($driver, 'POST', '/api/driver/location/update', ['trip_id' => $id, 'lat' => $lat, 'lng' => $lng, 'accuracy' => 5, 'heading' => 180]);
    }
    if (!in_array(Trips::findOrFail($id)->status, ['Payment', 'Rating', 'Completed'], true)) {
        $finished = $call($driver, 'POST', '/api/trips/'.$id.'/complete')['trip'];
    } else {
        $finished = $call($driver, 'GET', '/api/trips/'.$id)['trip'];
    }
    $saved = Trips::findOrFail($id);
    $expect((float) $saved->total_km > 0, 'Uploaded GPS contributes to actual trip distance');
    if ($mode === 'corporate') {
        $expect($saved->billing_status === 'unbilled' && $saved->payment_status === 'Deferred' && $saved->billing_finalized_at !== null, 'Physical completion freezes Deferred employer billing');
        $expect(($finished['billing']['collection_required'] ?? true) === false, 'Completed corporate trip still requires no collection');
        $snapshot = $saved->corporate_financial_snapshot;
        $call($driver, 'POST', '/api/trips/'.$id.'/complete');
        $expect($snapshot === $saved->fresh()->corporate_financial_snapshot, 'Repeated completion preserves immutable fare');
        $expect(DB::table('payment_transactions')->count() === $gatewayCount, 'Corporate completion creates no gateway transaction');
        $corporateTrips[] = $id;
        $current = $json('GET', '/api/trips/current', ['headers' => ['Authorization' => 'Bearer '.$tokens[$driver]]]);
        $expect(($current[1]['status_code'] ?? null) === '0', 'Deferred corporate completion releases driver');
    } else {
        $expect($saved->billing_mode === 'personal' && $saved->corporate_account_id === null && $saved->payment_mode === 'Cash', 'Unaffiliated and explicit-personal rides retain personal cash billing');
        [$status, $rated] = $json('GET', '/api/trip_rating', ['headers' => ['Authorization' => 'Bearer '.$tokens[$rider]],
            'query' => ['trip_id' => $id, 'user_type' => 'Rider', 'rating' => 5, 'rating_comments' => 'Isolated acceptance ride']]);
        $expect($status === 200 && ($rated['status_code'] ?? null) === '1', 'Rider rates the completed personal cash trip');
        [$status, $cash] = $json('GET', '/api/cash_collected', ['headers' => ['Authorization' => 'Bearer '.$tokens[$driver]], 'query' => ['trip_id' => $id]]);
        $expect($status === 200 && ($cash['status_code'] ?? null) === '1' && $saved->fresh()->payment_status === 'Completed', 'Personal cash collection completes without a corporate obligation');
    }
    $call($driver, 'POST', '/api/driver/offline');
}
echo 'Observed '.$checks.' authentication and trip HTTP checks; retained corporate trip IDs: '.implode(', ', $corporateTrips).PHP_EOL;
