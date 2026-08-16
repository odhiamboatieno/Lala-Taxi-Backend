<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyVerificationOtpsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'type' => 'phone', 'otp' => '305122', 'expires_at' => '2026-05-26 21:15:58', 'verified_at' => null, 'attempts' => 1, 'created_at' => '2026-05-26 21:05:58', 'updated_at' => '2026-05-26 21:06:08'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_verification_otps')->insert($chunk);
        }
    }
}
