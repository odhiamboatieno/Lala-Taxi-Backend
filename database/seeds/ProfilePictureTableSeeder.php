<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilePictureTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['user_id' => 10001, 'src' => 'https://newtaxi.seentechs.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10002, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10003, 'src' => 'https://newtaxi.seentechs.com/images/users/10003/profile_pic_1652863875.jpg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10004, 'src' => 'https://newtaxi.seentechs.com/images/users/10004/profile_pic_1652863838.jpg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10005, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10006, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10007, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10008, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10009, 'src' => 'https://jijitaxi.querysofts.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10010, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10011, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10012, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10013, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10014, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10015, 'src' => 'https://jijitaxi.querysofts.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10016, 'src' => 'https://lh3.googleusercontent.com/a/ACg8ocKfNUbL6h9OidD1iDaRYDcWZpC5FzU2kwJWiMkxxNobCaIdbw=s400-c', 'pending_src' => null, 'photo_source' => 'Facebook', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10017, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10018, 'src' => 'http://lalataxi.wilaso.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10019, 'src' => 'https://lalataxi.wilaso.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10020, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10021, 'src' => 'https://lalataxi.wilaso.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10022, 'src' => 'https://lalataxi.wilaso.com/images/users/10022/profile_pic_1779834695.jpg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10023, 'src' => 'https://lalataxi.wilaso.com/images/user.jpeg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10024, 'src' => 'https://lalataxi.wilaso.com/images/users/10024/profile_pic_1779842496.png', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => 'approved', 'submitted_at' => '2026-05-26 22:41:36', 'reviewed_at' => '2026-05-26 22:41:36', 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10025, 'src' => '', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => null, 'submitted_at' => null, 'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null],
            ['user_id' => 10026, 'src' => 'https://lalataxi.wilaso.com/images/users/10026/profile_pic_1780659959.jpg', 'pending_src' => null, 'photo_source' => 'Local', 'approval_status' => 'approved', 'submitted_at' => '2026-06-05 09:45:59', 'reviewed_at' => '2026-06-05 09:45:59', 'reviewed_by' => null, 'rejection_reason' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('profile_picture')->insert($chunk);
        }
    }
}
