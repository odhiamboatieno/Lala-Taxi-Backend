<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationCampaignsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 0, 'created_by_type' => 'admin', 'created_by_id' => 1, 'title' => 'Test Message', 'message' => 'Test Message', 'channel' => 'push', 'target_mode' => 'all', 'audience_type' => 'Rider', 'recipient_ids' => '[]', 'image_path' => null, 'status' => 'completed_with_errors', 'recurrence' => 'none', 'scheduled_for' => '2026-05-25 23:56:07', 'next_run_at' => null, 'last_sent_at' => '2026-05-25 23:56:07', 'last_recipient_count' => 10, 'successful_count' => 7, 'failed_count' => 3, 'is_active' => 0, 'created_at' => '2026-05-25 21:56:07', 'updated_at' => '2026-05-25 21:56:07'],
            ['id' => 3, 'company_id' => 0, 'created_by_type' => 'admin', 'created_by_id' => 1, 'title' => 'Welcome To LalaTaxi', 'message' => 'LalaTaxi bridge the gap between Taxi Operators and the customers', 'channel' => 'push', 'target_mode' => 'all', 'audience_type' => 'all', 'recipient_ids' => '[]', 'image_path' => 'images/notification_campaigns/campaign_1779754127.png', 'status' => 'completed_with_errors', 'recurrence' => 'none', 'scheduled_for' => '2026-05-26 00:10:12', 'next_run_at' => null, 'last_sent_at' => '2026-05-26 00:10:13', 'last_recipient_count' => 17, 'successful_count' => 13, 'failed_count' => 4, 'is_active' => 0, 'created_at' => '2026-05-25 22:10:12', 'updated_at' => '2026-05-25 22:10:13'],
            ['id' => 4, 'company_id' => 0, 'created_by_type' => 'admin', 'created_by_id' => 1, 'title' => 'Welcome To LalaTaxi', 'message' => 'LalaTaxi bridge the gap between Taxi Operators and the customers', 'channel' => 'push', 'target_mode' => 'all', 'audience_type' => 'all', 'recipient_ids' => '[]', 'image_path' => 'images/notification_campaigns/campaign_1779754127.png', 'status' => 'active', 'recurrence' => 'none', 'scheduled_for' => '2026-05-26 00:12:39', 'next_run_at' => null, 'last_sent_at' => '2026-05-26 00:12:49', 'last_recipient_count' => 17, 'successful_count' => 13, 'failed_count' => 4, 'is_active' => 1, 'created_at' => '2026-05-25 22:12:39', 'updated_at' => '2026-05-25 22:17:27'],
            ['id' => 5, 'company_id' => 0, 'created_by_type' => 'admin', 'created_by_id' => 1, 'title' => 'Welcome To LalaTaxi', 'message' => 'LalaTaxi bridge the gap between Taxi Operators and the customers', 'channel' => 'push', 'target_mode' => 'all', 'audience_type' => 'all', 'recipient_ids' => '[]', 'image_path' => 'images/notification_campaigns/campaign_1779754127.png', 'status' => 'completed_with_errors', 'recurrence' => 'none', 'scheduled_for' => '2026-05-26 00:17:41', 'next_run_at' => null, 'last_sent_at' => '2026-05-26 00:17:44', 'last_recipient_count' => 17, 'successful_count' => 4, 'failed_count' => 13, 'is_active' => 0, 'created_at' => '2026-05-25 22:17:41', 'updated_at' => '2026-05-25 22:17:44'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('notification_campaigns')->insert($chunk);
        }
    }
}
