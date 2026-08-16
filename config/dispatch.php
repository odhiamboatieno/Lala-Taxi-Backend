<?php

return [
    'search_radii_km' => array_map('intval', explode(',', env('DISPATCH_SEARCH_RADII_KM', '3,5,8,12'))),
    'initial_batch_size' => (int) env('DISPATCH_INITIAL_BATCH_SIZE', 3),
    'max_batch_size' => (int) env('DISPATCH_MAX_BATCH_SIZE', 5),
    'request_timeout_seconds' => (int) env('DISPATCH_REQUEST_TIMEOUT_SECONDS', 20),
    'driver_stale_after_seconds' => (int) env('DISPATCH_DRIVER_STALE_AFTER_SECONDS', 90),
    'location_broadcast_distance_meters' => (float) env('DISPATCH_LOCATION_BROADCAST_DISTANCE_METERS', 25),
    'location_history_distance_meters' => (float) env('DISPATCH_LOCATION_HISTORY_DISTANCE_METERS', 50),
    'location_history_interval_seconds' => (int) env('DISPATCH_LOCATION_HISTORY_INTERVAL_SECONDS', 30),
];
