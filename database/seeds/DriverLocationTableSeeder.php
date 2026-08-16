<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverLocationTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 5, 'user_id' => 10003, 'latitude' => '-1.4850535', 'longitude' => '36.9510573', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2022-05-18 04:19:43', 'updated_at' => '2026-05-29 10:33:24'],
            ['id' => 6, 'user_id' => 10003, 'latitude' => '-1.4850535', 'longitude' => '36.9510573', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2022-05-18 04:24:03', 'updated_at' => '2026-05-27 04:42:18'],
            ['id' => 7, 'user_id' => 10003, 'latitude' => '-1.4850535', 'longitude' => '36.9510573', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2022-05-18 04:24:03', 'updated_at' => '2026-05-27 04:42:18'],
            ['id' => 8, 'user_id' => 10003, 'latitude' => '-1.4850535', 'longitude' => '36.9510573', 'heading' => null, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2022-05-18 04:24:03', 'updated_at' => '2026-05-27 04:42:18'],
            ['id' => 9, 'user_id' => 10002, 'latitude' => '-1.4850386', 'longitude' => '36.95101', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2023-11-30 17:18:45', 'updated_at' => '2023-12-03 22:30:37'],
            ['id' => 10, 'user_id' => 10002, 'latitude' => '-1.4850386', 'longitude' => '36.95101', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:18:45', 'updated_at' => '2023-11-30 17:20:33'],
            ['id' => 11, 'user_id' => 10002, 'latitude' => '-1.4850386', 'longitude' => '36.95101', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:18:45', 'updated_at' => '2023-11-30 17:20:33'],
            ['id' => 12, 'user_id' => 10002, 'latitude' => '-1.4850386', 'longitude' => '36.95101', 'heading' => null, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:18:45', 'updated_at' => '2023-11-30 17:20:33'],
            ['id' => 14, 'user_id' => 10005, 'latitude' => '-1.4850304', 'longitude' => '36.951013', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:26:43', 'updated_at' => '2023-12-03 22:29:21'],
            ['id' => 17, 'user_id' => 10005, 'latitude' => '-1.4850304', 'longitude' => '36.951013', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:56:39', 'updated_at' => '2023-12-03 22:29:21'],
            ['id' => 18, 'user_id' => 10005, 'latitude' => '-1.4850304', 'longitude' => '36.951013', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:56:39', 'updated_at' => '2023-12-03 22:29:21'],
            ['id' => 19, 'user_id' => 10005, 'latitude' => '-1.4850304', 'longitude' => '36.951013', 'heading' => null, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2023-11-30 17:56:39', 'updated_at' => '2023-12-03 22:29:21'],
            ['id' => 26, 'user_id' => 10012, 'latitude' => '-26.270179988782115', 'longitude' => '27.76613425106321', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-14 11:52:10', 'updated_at' => '2025-09-21 16:32:54'],
            ['id' => 27, 'user_id' => 10012, 'latitude' => '-26.27017949180359', 'longitude' => '27.766135148811852', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-14 11:52:10', 'updated_at' => '2025-09-21 16:32:54'],
            ['id' => 28, 'user_id' => 10012, 'latitude' => '-26.27017949180359', 'longitude' => '27.766135148811852', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-14 11:52:10', 'updated_at' => '2025-09-21 16:32:54'],
            ['id' => 29, 'user_id' => 10012, 'latitude' => '-26.27017949180359', 'longitude' => '27.766135148811852', 'heading' => null, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-14 11:52:10', 'updated_at' => '2025-09-21 16:32:54'],
            ['id' => 30, 'user_id' => 10012, 'latitude' => '-26.27017949180359', 'longitude' => '27.766135148811852', 'heading' => null, 'car_id' => 5, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-14 11:52:10', 'updated_at' => '2025-09-21 16:32:54'],
            ['id' => 32, 'user_id' => 10014, 'latitude' => '-26.27016', 'longitude' => '27.7661267', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-15 08:05:23', 'updated_at' => '2025-09-24 18:03:29'],
            ['id' => 33, 'user_id' => 10014, 'latitude' => '-26.27016', 'longitude' => '27.7661267', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-15 08:05:23', 'updated_at' => '2025-09-24 18:03:29'],
            ['id' => 34, 'user_id' => 10014, 'latitude' => '-26.27016', 'longitude' => '27.7661267', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-15 08:05:23', 'updated_at' => '2025-09-24 18:03:29'],
            ['id' => 35, 'user_id' => 10014, 'latitude' => '-26.27016', 'longitude' => '27.7661267', 'heading' => null, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-15 08:05:23', 'updated_at' => '2025-09-24 18:03:29'],
            ['id' => 36, 'user_id' => 10014, 'latitude' => '-26.27016', 'longitude' => '27.7661267', 'heading' => null, 'car_id' => 5, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2025-09-15 08:05:23', 'updated_at' => '2025-09-24 18:03:29'],
            ['id' => 37, 'user_id' => 10009, 'latitude' => '', 'longitude' => '', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2026-05-08 00:01:48', 'updated_at' => '2026-05-08 00:01:48'],
            ['id' => 38, 'user_id' => 10019, 'latitude' => '-1.4922258', 'longitude' => '36.9568813', 'heading' => 0.0, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2026-05-14 16:06:56', 'updated_at' => '2026-06-05 10:00:42'],
            ['id' => 39, 'user_id' => 10021, 'latitude' => '-1.4850153', 'longitude' => '36.9510745', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-05-26 19:32:51', 'updated_at' => '2026-05-26 20:29:46'],
            ['id' => 40, 'user_id' => 10021, 'latitude' => '-1.4850153', 'longitude' => '36.9510745', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-05-26 19:32:51', 'updated_at' => '2026-05-26 20:29:46'],
            ['id' => 41, 'user_id' => 10021, 'latitude' => '-1.4850153', 'longitude' => '36.9510745', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-05-26 19:32:51', 'updated_at' => '2026-05-26 20:29:46'],
            ['id' => 42, 'user_id' => 10023, 'latitude' => '-1.4850368', 'longitude' => '36.9510599', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-05-26 21:43:46', 'updated_at' => '2026-05-26 22:34:28'],
            ['id' => 43, 'user_id' => 10024, 'latitude' => '-1.4850443', 'longitude' => '36.9510474', 'heading' => null, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2026-05-26 22:55:29', 'updated_at' => '2026-05-30 08:24:35'],
            ['id' => 44, 'user_id' => 10024, 'latitude' => '-1.4850443', 'longitude' => '36.9510474', 'heading' => null, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2026-05-26 22:55:29', 'updated_at' => '2026-05-30 08:24:35'],
            ['id' => 45, 'user_id' => 10024, 'latitude' => '-1.4850443', 'longitude' => '36.9510474', 'heading' => null, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2026-05-26 22:55:29', 'updated_at' => '2026-05-30 08:24:35'],
            ['id' => 46, 'user_id' => 10024, 'latitude' => '-1.4850443', 'longitude' => '36.9510474', 'heading' => null, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Offline', 'created_at' => '2026-05-26 22:55:29', 'updated_at' => '2026-05-30 08:24:35'],
            ['id' => 47, 'user_id' => 10026, 'latitude' => '-1.4850306', 'longitude' => '36.9510692', 'heading' => 81.75, 'car_id' => 1, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-06-05 09:56:38', 'updated_at' => '2026-06-08 14:33:34'],
            ['id' => 48, 'user_id' => 10026, 'latitude' => '-1.4850306', 'longitude' => '36.9510692', 'heading' => 81.75, 'car_id' => 2, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-06-05 09:56:38', 'updated_at' => '2026-06-08 14:33:34'],
            ['id' => 49, 'user_id' => 10026, 'latitude' => '-1.4850306', 'longitude' => '36.9510692', 'heading' => 81.75, 'car_id' => 3, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-06-05 09:56:38', 'updated_at' => '2026-06-08 14:33:34'],
            ['id' => 50, 'user_id' => 10026, 'latitude' => '-1.4850306', 'longitude' => '36.9510692', 'heading' => 81.75, 'car_id' => 4, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-06-05 09:56:38', 'updated_at' => '2026-06-08 14:33:34'],
            ['id' => 51, 'user_id' => 10026, 'latitude' => '-1.4850306', 'longitude' => '36.9510692', 'heading' => 81.75, 'car_id' => 5, 'pool_trip_id' => null, 'status' => 'Online', 'created_at' => '2026-06-05 09:56:38', 'updated_at' => '2026-06-08 14:33:34'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('driver_location')->insert($chunk);
        }
    }
}
