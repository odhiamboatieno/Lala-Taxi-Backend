<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLevelsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'driver', 'sequence' => 1, 'name' => 'Level 1', 'slug' => 'level-1', 'icon' => 'ti ti-building', 'description' => 'Test', 'status' => 'Active', 'is_default' => 1, 'target_config' => '{"minimum_completed_trips":"30","minimum_earnings":"3000","minimum_spend":null,"maximum_cancellation_rate":"60","minimum_rating":"4","minimum_reviews":"60"}', 'reward_config' => '{"reward_type":"badge_only","reward_amount":null}', 'created_at' => '2026-05-21 19:15:23', 'updated_at' => '2026-05-21 19:15:23'],
            ['id' => 2, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'driver', 'sequence' => 2, 'name' => 'Level 2', 'slug' => 'level-2', 'icon' => 'fal fa-bus', 'description' => 'Test', 'status' => 'Active', 'is_default' => 0, 'target_config' => '{"minimum_completed_trips":"60","minimum_earnings":"5000","minimum_spend":null,"maximum_cancellation_rate":"50","minimum_rating":"4","minimum_reviews":"120"}', 'reward_config' => '{"reward_type":"priority_dispatch","reward_amount":null}', 'created_at' => '2026-05-21 19:16:29', 'updated_at' => '2026-05-21 19:16:29'],
            ['id' => 3, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'driver', 'sequence' => 3, 'name' => 'Level 3', 'slug' => 'level-3', 'icon' => 'fal fa-bus', 'description' => 'Test', 'status' => 'Active', 'is_default' => 0, 'target_config' => '{"minimum_completed_trips":"150","minimum_earnings":"10000","minimum_spend":null,"maximum_cancellation_rate":"30","minimum_rating":"4","minimum_reviews":"200"}', 'reward_config' => '{"reward_type":"bonus","reward_amount":"20"}', 'created_at' => '2026-05-21 19:17:39', 'updated_at' => '2026-05-21 19:17:39'],
            ['id' => 4, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'driver', 'sequence' => 4, 'name' => 'Expert', 'slug' => 'expert', 'icon' => 'ri-steering-2-line', 'description' => 'Test', 'status' => 'Active', 'is_default' => 0, 'target_config' => '{"minimum_completed_trips":"300","minimum_earnings":"20000","minimum_spend":null,"maximum_cancellation_rate":"10","minimum_rating":"5","minimum_reviews":"300"}', 'reward_config' => '{"reward_type":"bonus","reward_amount":"300"}', 'created_at' => '2026-05-21 19:18:50', 'updated_at' => '2026-05-21 19:18:50'],
            ['id' => 5, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'rider', 'sequence' => 1, 'name' => 'New User', 'slug' => 'new-user', 'icon' => 'ri-user-smile-line', 'description' => 'New user', 'status' => 'Active', 'is_default' => 0, 'target_config' => '{"minimum_completed_trips":"1","minimum_earnings":null,"minimum_spend":"50","maximum_cancellation_rate":"90","minimum_rating":"4","minimum_reviews":"1"}', 'reward_config' => '{"reward_type":"badge_only","reward_amount":null}', 'created_at' => '2026-05-21 19:23:03', 'updated_at' => '2026-05-21 19:23:03'],
            ['id' => 6, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'rider', 'sequence' => 2, 'name' => 'Level 1', 'slug' => 'level-1', 'icon' => 'ri-steering-2-line', 'description' => 'Level 1 Customer', 'status' => 'Active', 'is_default' => 0, 'target_config' => '{"minimum_completed_trips":"5","minimum_earnings":null,"minimum_spend":"3000","maximum_cancellation_rate":"70","minimum_rating":"4","minimum_reviews":"3"}', 'reward_config' => '{"reward_type":"badge_only","reward_amount":null}', 'created_at' => '2026-05-21 19:24:23', 'updated_at' => '2026-05-21 19:24:23'],
            ['id' => 7, 'company_id' => null, 'inherits_from_id' => null, 'level_type' => 'rider', 'sequence' => 3, 'name' => 'Level 2', 'slug' => 'level-2', 'icon' => 'ri-steering-line', 'description' => 'Level 2', 'status' => 'Active', 'is_default' => 0, 'target_config' => '{"minimum_completed_trips":"10","minimum_earnings":null,"minimum_spend":"5000","maximum_cancellation_rate":"60","minimum_rating":"4","minimum_reviews":"8"}', 'reward_config' => '{"reward_type":"coupon","reward_amount":"10"}', 'created_at' => '2026-05-21 19:25:44', 'updated_at' => '2026-05-21 19:25:44'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('user_levels')->insert($chunk);
        }
    }
}
