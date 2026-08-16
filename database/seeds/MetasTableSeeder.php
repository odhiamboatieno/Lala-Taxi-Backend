<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetasTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'url' => '/', 'title' => 'Home Page', 'description' => 'Home Page', 'keywords' => ''],
            ['id' => 2, 'url' => 'signin', 'title' => 'Sign In', 'description' => 'Sign In', 'keywords' => ''],
            ['id' => 3, 'url' => 'signin_driver', 'title' => 'Sign In Driver', 'description' => 'Sign In Driver', 'keywords' => ''],
            ['id' => 4, 'url' => 'signin_rider', 'title' => 'Sign In Rider', 'description' => 'Sign In Rider', 'keywords' => ''],
            ['id' => 5, 'url' => 'signup', 'title' => 'Sing Up', 'description' => 'Sing Up', 'keywords' => ''],
            ['id' => 6, 'url' => 'signup_driver', 'title' => 'Sign Up Driver', 'description' => 'Sign Up Driver', 'keywords' => ''],
            ['id' => 7, 'url' => 'signup_rider', 'title' => 'Sign Up Rider', 'description' => 'Sign Up Rider', 'keywords' => ''],
            ['id' => 8, 'url' => 'ride', 'title' => 'Rider Home Page', 'description' => 'Rider Home Page', 'keywords' => ''],
            ['id' => 9, 'url' => 'drive', 'title' => 'Driver Home Page', 'description' => 'Driver Home Page', 'keywords' => ''],
            ['id' => 10, 'url' => 'safety', 'title' => 'Trip safety', 'description' => 'Trip safety', 'keywords' => ''],
            ['id' => 11, 'url' => 'how_it_works', 'title' => 'How its works', 'description' => 'How its works', 'keywords' => ''],
            ['id' => 12, 'url' => 'requirements', 'title' => 'Driver requirements', 'description' => 'Driver requirements', 'keywords' => ''],
            ['id' => 13, 'url' => 'driver_app', 'title' => 'Driver App', 'description' => 'Driver App', 'keywords' => ''],
            ['id' => 14, 'url' => 'drive_safety', 'title' => 'Driver Safety', 'description' => 'Driver Safety', 'keywords' => ''],
            ['id' => 15, 'url' => 'driver_profile', 'title' => 'Driver Profile', 'description' => 'Driver Profile', 'keywords' => ''],
            ['id' => 16, 'url' => 'documents/{id}', 'title' => 'Driver Documents', 'description' => 'Driver Documents', 'keywords' => ''],
            ['id' => 17, 'url' => 'driver_payment', 'title' => 'Driver Payment', 'description' => 'Driver Payment', 'keywords' => ''],
            ['id' => 18, 'url' => 'driver_invoice', 'title' => 'Driver Invoice', 'description' => 'Driver Invoice', 'keywords' => ''],
            ['id' => 19, 'url' => 'driver_trip', 'title' => 'Driver Trips', 'description' => 'Driver Trips', 'keywords' => ''],
            ['id' => 20, 'url' => 'driver_trip_detail/{id}', 'title' => 'Driver Trips Details', 'description' => 'Driver Trips Details', 'keywords' => ''],
            ['id' => 21, 'url' => 'download_invoice/{id}', 'title' => 'Invoice', 'description' => 'Invoice', 'keywords' => ''],
            ['id' => 22, 'url' => 'trip', 'title' => 'Trips', 'description' => 'Trips', 'keywords' => ''],
            ['id' => 23, 'url' => 'profile', 'title' => 'Profile', 'description' => 'Profile', 'keywords' => ''],
            ['id' => 24, 'url' => 'forgot_password_driver', 'title' => 'Forgot Password', 'description' => 'Forgot Password', 'keywords' => ''],
            ['id' => 25, 'url' => 'reset_password', 'title' => 'Reset Password', 'description' => 'Reset Password', 'keywords' => ''],
            ['id' => 26, 'url' => 'forgot_password_rider', 'title' => 'Forgot Password', 'description' => 'Forgot Password', 'keywords' => ''],
            ['id' => 27, 'url' => 'forgot_password_link/{id}', 'title' => 'Forgot Password Link', 'description' => 'Forgot Password Link', 'keywords' => ''],
            ['id' => 28, 'url' => 'payout_preferences', 'title' => 'Payout Preferences', 'description' => 'Payout Preferences', 'keywords' => ''],
            ['id' => 29, 'url' => 'help', 'title' => 'Help Center', 'description' => 'Help Center', 'keywords' => ''],
            ['id' => 30, 'url' => 'help/topic/{id}/{category}', 'title' => 'Help Center', 'description' => 'Help Center', 'keywords' => ''],
            ['id' => 31, 'url' => 'help/article/{id}/{question}', 'title' => 'Help Center', 'description' => 'Help Center', 'keywords' => ''],
            ['id' => 32, 'url' => 'signin_company', 'title' => 'Sign In Company', 'description' => 'Sign In Company', 'keywords' => ''],
            ['id' => 33, 'url' => 'signup_company', 'title' => 'Sign Up Company', 'description' => 'Sign Up Company', 'keywords' => ''],
            ['id' => 34, 'url' => 'forgot_password_company', 'title' => 'Forgot Password', 'description' => 'Forgot Password', 'keywords' => ''],
            ['id' => 35, 'url' => 'company/reset_password', 'title' => 'Reset Password', 'description' => 'Reset Password', 'keywords' => ''],
            ['id' => 36, 'url' => 'admin', 'title' => 'Admin Panel', 'description' => 'Admin Panel', 'keywords' => ''],
            ['id' => 37, 'url' => 'company', 'title' => 'Company Panel', 'description' => 'Company Panel', 'keywords' => ''],
            ['id' => 38, 'url' => 'app/driver', 'title' => 'Diver App', 'description' => 'Diver App', 'keywords' => ''],
            ['id' => 39, 'url' => 'app/rider', 'title' => 'Rider App', 'description' => 'Rider App', 'keywords' => ''],
            ['id' => 40, 'url' => 'rider_delete', 'title' => 'Delete Rider', 'description' => 'Delete Rider', 'keywords' => ''],
            ['id' => 41, 'url' => 'rider_deletion', 'title' => 'Delete Rider', 'description' => 'Delete Rider', 'keywords' => ''],
            ['id' => 42, 'url' => 'driver_deletion', 'title' => 'Delete Driver', 'description' => 'Delete Driver', 'keywords' => ''],
            ['id' => 43, 'url' => 'login', 'title' => 'Deletion Login', 'description' => 'Deletion Login', 'keywords' => ''],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('metas')->insert($chunk);
        }
    }
}
