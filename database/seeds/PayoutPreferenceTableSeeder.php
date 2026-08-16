<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayoutPreferenceTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10002, 'address1' => '', 'address2' => '', 'city' => null, 'state' => null, 'postal_code' => null, 'country' => null, 'payout_method' => 'BankTransfer', 'paypal_email' => '123456789', 'currency_code' => '', 'routing_number' => '', 'account_number' => '123456789', 'holder_name' => 'SMR IT SOLUTIONS', 'holder_type' => 'Company', 'document_id' => null, 'document_image' => null, 'additional_document_id' => null, 'additional_document_image' => null, 'phone_number' => '', 'address_kanji' => '[]', 'bank_name' => 'Demo', 'bank_location' => 'Demo', 'branch_name' => '', 'branch_code' => 'Demo', 'ssn_last_4' => '', 'created_at' => '2021-12-18 16:13:31', 'updated_at' => '2021-12-18 16:15:00'],
            ['id' => 2, 'user_id' => 10003, 'address1' => '10444', 'address2' => '', 'city' => 'Nairobi', 'state' => 'Nairobi', 'postal_code' => '2333', 'country' => 'KE', 'payout_method' => 'Paypal', 'paypal_email' => 'admin@admin.com', 'currency_code' => 'USD', 'routing_number' => '', 'account_number' => '', 'holder_name' => '', 'holder_type' => 'Company', 'document_id' => null, 'document_image' => null, 'additional_document_id' => null, 'additional_document_image' => null, 'phone_number' => '', 'address_kanji' => '[]', 'bank_name' => '', 'bank_location' => '', 'branch_name' => '', 'branch_code' => '', 'ssn_last_4' => '', 'created_at' => '2022-11-12 07:07:08', 'updated_at' => '2022-11-12 07:07:08'],
            ['id' => 3, 'user_id' => 10003, 'address1' => '', 'address2' => '', 'city' => null, 'state' => null, 'postal_code' => null, 'country' => null, 'payout_method' => 'BankTransfer', 'paypal_email' => '127262626', 'currency_code' => '', 'routing_number' => '', 'account_number' => '127262626', 'holder_name' => 'Gordon', 'holder_type' => 'Company', 'document_id' => null, 'document_image' => null, 'additional_document_id' => null, 'additional_document_image' => null, 'phone_number' => '', 'address_kanji' => '[]', 'bank_name' => 'KCB', 'bank_location' => 'nairobi', 'branch_name' => '', 'branch_code' => 'KCab', 'ssn_last_4' => '', 'created_at' => '2026-05-10 11:03:24', 'updated_at' => '2026-05-10 11:03:24'],
            ['id' => 4, 'user_id' => 10024, 'address1' => '', 'address2' => null, 'city' => null, 'state' => null, 'postal_code' => null, 'country' => 'IN', 'payout_method' => 'BankTransfer', 'paypal_email' => '23332333', 'currency_code' => '', 'routing_number' => null, 'account_number' => '23332333', 'holder_name' => 'GORDON OTIENO ONONO', 'holder_type' => 'Company', 'document_id' => null, 'document_image' => null, 'additional_document_id' => null, 'additional_document_image' => null, 'phone_number' => '700781344', 'address_kanji' => '[]', 'bank_name' => 'GORDON OTIENO ONONO', 'bank_location' => '5th Floor, London Fruit and Wool Exchange, 1 Duval Square, London, E1 6PW', 'branch_name' => null, 'branch_code' => 'LDNB', 'ssn_last_4' => null, 'created_at' => '2026-05-26 22:45:51', 'updated_at' => '2026-05-26 22:45:51'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('payout_preference')->insert($chunk);
        }
    }
}
