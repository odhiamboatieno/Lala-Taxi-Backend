<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportIssueMessagesTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'support_issue_id' => 1, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'ppp', 'read_at' => null, 'created_at' => '2026-05-13 16:28:09', 'updated_at' => '2026-05-13 16:28:09'],
            ['id' => 2, 'support_issue_id' => 1, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Dude. Help me.', 'read_at' => null, 'created_at' => '2026-05-13 16:33:42', 'updated_at' => '2026-05-13 16:33:42'],
            ['id' => 3, 'support_issue_id' => 2, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 16:49:39', 'updated_at' => '2026-05-13 16:49:39'],
            ['id' => 4, 'support_issue_id' => 3, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 16:49:45', 'updated_at' => '2026-05-13 16:49:45'],
            ['id' => 5, 'support_issue_id' => 4, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 16:51:31', 'updated_at' => '2026-05-13 16:51:31'],
            ['id' => 6, 'support_issue_id' => 5, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 17:35:44', 'updated_at' => '2026-05-13 17:35:44'],
            ['id' => 7, 'support_issue_id' => 6, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 17:35:57', 'updated_at' => '2026-05-13 17:35:57'],
            ['id' => 8, 'support_issue_id' => 7, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 17:36:18', 'updated_at' => '2026-05-13 17:36:18'],
            ['id' => 9, 'support_issue_id' => 8, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 17:36:34', 'updated_at' => '2026-05-13 17:36:34'],
            ['id' => 10, 'support_issue_id' => 9, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 17:56:28', 'updated_at' => '2026-05-13 17:56:28'],
            ['id' => 11, 'support_issue_id' => 10, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-13 17:59:16', 'updated_at' => '2026-05-13 17:59:16'],
            ['id' => 12, 'support_issue_id' => 11, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 09:10:00', 'updated_at' => '2026-05-14 09:10:00'],
            ['id' => 13, 'support_issue_id' => 11, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Hello', 'read_at' => null, 'created_at' => '2026-05-14 09:10:10', 'updated_at' => '2026-05-14 09:10:10'],
            ['id' => 14, 'support_issue_id' => 12, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 09:10:15', 'updated_at' => '2026-05-14 09:10:15'],
            ['id' => 15, 'support_issue_id' => 13, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 11:49:15', 'updated_at' => '2026-05-14 11:49:15'],
            ['id' => 16, 'support_issue_id' => 14, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 12:18:18', 'updated_at' => '2026-05-14 12:18:18'],
            ['id' => 17, 'support_issue_id' => 15, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 14:15:30', 'updated_at' => '2026-05-14 14:15:30'],
            ['id' => 18, 'support_issue_id' => 16, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 15:27:45', 'updated_at' => '2026-05-14 15:27:45'],
            ['id' => 19, 'support_issue_id' => 16, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Hello', 'read_at' => null, 'created_at' => '2026-05-14 15:27:54', 'updated_at' => '2026-05-14 15:27:54'],
            ['id' => 20, 'support_issue_id' => 17, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 15:27:57', 'updated_at' => '2026-05-14 15:27:57'],
            ['id' => 21, 'support_issue_id' => 18, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 16:51:08', 'updated_at' => '2026-05-14 16:51:08'],
            ['id' => 22, 'support_issue_id' => 19, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-14 17:25:54', 'updated_at' => '2026-05-14 17:25:54'],
            ['id' => 23, 'support_issue_id' => 20, 'sender_id' => 10010, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-05-15 07:57:12', 'updated_at' => '2026-05-15 07:57:12'],
            ['id' => 24, 'support_issue_id' => 21, 'sender_id' => 10019, 'sender_type' => 'rider', 'message' => 'Testing', 'read_at' => null, 'created_at' => '2026-05-20 11:58:47', 'updated_at' => '2026-05-20 11:58:47'],
            ['id' => 25, 'support_issue_id' => 22, 'sender_id' => 10025, 'sender_type' => 'rider', 'message' => 'Support chat opened.', 'read_at' => null, 'created_at' => '2026-06-05 08:12:09', 'updated_at' => '2026-06-05 08:12:09'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('support_issue_messages')->insert($chunk);
        }
    }
}
