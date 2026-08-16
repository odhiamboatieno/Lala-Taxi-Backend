<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyPaymentGatewaysTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '0', 'site' => 'Paypal', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 2, 'company_id' => 4, 'name' => 'paypal_id', 'value' => '#', 'site' => 'Paypal', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 3, 'company_id' => 4, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Paypal', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 4, 'company_id' => 4, 'name' => 'client', 'value' => 'AbZqxwGM87-fRHI-HnG_plBoz-Z_j2OgcAKRFQzgdR4qd5dszhQXS5nk6FTPd9sw0vSSLMadISBc2_lA', 'site' => 'Paypal', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 5, 'company_id' => 4, 'name' => 'secret', 'value' => 'EDFYQf8itbqoWi-9BIzgzrNvGWLI62UEliT1i8f_APi_MAJkteZLwnXGmTvBkBIRAVy-jCBi-PmYyNUa', 'site' => 'Paypal', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 6, 'company_id' => 4, 'name' => 'access_token', 'value' => 'access_token$sandbox$d7852qvvw6wj277m$71ad22f88418c3a7f17d824ad0786ffc', 'site' => 'Paypal', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 7, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Stripe', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 8, 'company_id' => 4, 'name' => 'publish', 'value' => 'pk_test_lQctuc2tx2IVDCSYIjiFodaz00n0TNteiG', 'site' => 'Stripe', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 9, 'company_id' => 4, 'name' => 'secret', 'value' => 'sk_test_1tiewAwj00VlKzL7uwMPZcTN003Vk0kWl6', 'site' => 'Stripe', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 10, 'company_id' => 4, 'name' => 'api_version', 'value' => '2019-12-03', 'site' => 'Stripe', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 11, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Paytm', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 12, 'company_id' => 4, 'name' => 'paytm_merchant', 'value' => 'LkWJXg58223806923015', 'site' => 'Paytm', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 13, 'company_id' => 4, 'name' => 'paytm_secret', 'value' => 'EJETB%2pZaHB3Ra%', 'site' => 'Paytm', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 14, 'company_id' => 4, 'name' => 'mode', 'value' => 'live', 'site' => 'Paytm', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 15, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Flutterwave', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 16, 'company_id' => 4, 'name' => 'mode', 'value' => 'live', 'site' => 'Flutterwave', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 17, 'company_id' => 4, 'name' => 'public_key', 'value' => 'FLWPUBK_TEST-f2a7a1ca6cdf04f4ab00d9006073ff6d-X', 'site' => 'Flutterwave', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 18, 'company_id' => 4, 'name' => 'secret_key', 'value' => 'FLWSECK_TEST-f356353d77c4637026991ae1db0dc608-X', 'site' => 'Flutterwave', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 19, 'company_id' => 4, 'name' => 'encryption_key', 'value' => 'FLWSECK_TEST636841ac21f2', 'site' => 'Flutterwave', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 20, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Mpesa', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 21, 'company_id' => 4, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Mpesa', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 22, 'company_id' => 4, 'name' => 'consumer_key', 'value' => 'sMpgnYW62glBlxPXbyTBEGdPib8eJLOL', 'site' => 'Mpesa', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 23, 'company_id' => 4, 'name' => 'consumer_secret', 'value' => 'IcK2PkAFArVVVffU', 'site' => 'Mpesa', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 24, 'company_id' => 4, 'name' => 'callback_url', 'value' => 'https://new.newtaxi.co/api/mpesa_callback', 'site' => 'Mpesa', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 25, 'company_id' => 4, 'name' => 'business_shortcode', 'value' => '174379', 'site' => 'Mpesa', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 26, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '0', 'site' => 'Mollie', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 27, 'company_id' => 4, 'name' => 'api_key', 'value' => '', 'site' => 'Mollie', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 28, 'company_id' => 4, 'name' => 'partner_id', 'value' => '', 'site' => 'Mollie', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 29, 'company_id' => 4, 'name' => 'profile_id', 'value' => '', 'site' => 'Mollie', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 30, 'company_id' => 4, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Mollie', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 31, 'company_id' => 4, 'name' => 'is_enabled', 'value' => '0', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 32, 'company_id' => 4, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 33, 'company_id' => 4, 'name' => 'merchant_id', 'value' => 'g3dprd7kyfs7f3jr', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 34, 'company_id' => 4, 'name' => 'public_key', 'value' => 'prwd98qgnqkdptkp', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 35, 'company_id' => 4, 'name' => 'private_key', 'value' => 'fe3e98760ba97b6b2e01fe28379cd477', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 36, 'company_id' => 4, 'name' => 'tokenization_key', 'value' => 'sandbox_jy9mwggn_q8v7ynjw9fssn4hy', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 37, 'company_id' => 4, 'name' => 'merchant_account_id', 'value' => '', 'site' => 'Braintree', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 38, 'company_id' => 4, 'name' => 'is_web_payment', 'value' => '0', 'site' => 'Common', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
            ['id' => 39, 'company_id' => 4, 'name' => 'payout_methods', 'value' => 'bank_transfer,stripe', 'site' => 'Common', 'created_at' => '2026-06-07 11:27:42', 'updated_at' => '2026-06-07 11:27:42'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('company_payment_gateways')->insert($chunk);
        }
    }
}
