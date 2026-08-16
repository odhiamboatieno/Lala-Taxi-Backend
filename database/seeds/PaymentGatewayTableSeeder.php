<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewayTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'trip_default', 'value' => 'cash', 'site' => 'Common'],
            ['id' => 2, 'name' => 'payout_methods', 'value' => 'bank_transfer,stripe', 'site' => 'Common'],
            ['id' => 3, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Cash'],
            ['id' => 4, 'name' => 'is_enabled', 'value' => '0', 'site' => 'Paypal'],
            ['id' => 5, 'name' => 'paypal_id', 'value' => '#', 'site' => 'Paypal'],
            ['id' => 6, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Paypal'],
            ['id' => 7, 'name' => 'client', 'value' => 'AbZqxwGM87-fRHI-HnG_plBoz-Z_j2OgcAKRFQzgdR4qd5dszhQXS5nk6FTPd9sw0vSSLMadISBc2_lA', 'site' => 'Paypal'],
            ['id' => 8, 'name' => 'secret', 'value' => 'EDFYQf8itbqoWi-9BIzgzrNvGWLI62UEliT1i8f_APi_MAJkteZLwnXGmTvBkBIRAVy-jCBi-PmYyNUa', 'site' => 'Paypal'],
            ['id' => 9, 'name' => 'access_token', 'value' => 'access_token$sandbox$d7852qvvw6wj277m$71ad22f88418c3a7f17d824ad0786ffc', 'site' => 'Paypal'],
            ['id' => 10, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Stripe'],
            ['id' => 11, 'name' => 'publish', 'value' => 'pk_test_lQctuc2tx2IVDCSYIjiFodaz00n0TNteiG', 'site' => 'Stripe'],
            ['id' => 12, 'name' => 'secret', 'value' => 'sk_test_1tiewAwj00VlKzL7uwMPZcTN003Vk0kWl6', 'site' => 'Stripe'],
            ['id' => 13, 'name' => 'api_version', 'value' => '2019-12-03', 'site' => 'Stripe'],
            ['id' => 14, 'name' => 'is_enabled', 'value' => '0', 'site' => 'Braintree'],
            ['id' => 15, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Braintree'],
            ['id' => 16, 'name' => 'merchant_id', 'value' => 'g3dprd7kyfs7f3jr', 'site' => 'Braintree'],
            ['id' => 17, 'name' => 'public_key', 'value' => 'prwd98qgnqkdptkp', 'site' => 'Braintree'],
            ['id' => 18, 'name' => 'private_key', 'value' => 'fe3e98760ba97b6b2e01fe28379cd477', 'site' => 'Braintree'],
            ['id' => 19, 'name' => 'tokenization_key', 'value' => 'sandbox_jy9mwggn_q8v7ynjw9fssn4hy', 'site' => 'Braintree'],
            ['id' => 20, 'name' => 'merchant_account_id', 'value' => '', 'site' => 'Braintree'],
            ['id' => 21, 'name' => 'is_web_payment', 'value' => '', 'site' => 'Common'],
            ['id' => 22, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Paytm'],
            ['id' => 23, 'name' => 'paytm_merchant', 'value' => 'LkWJXg58223806923015', 'site' => 'Paytm'],
            ['id' => 24, 'name' => 'paytm_secret', 'value' => 'EJETB%2pZaHB3Ra%', 'site' => 'Paytm'],
            ['id' => 25, 'name' => 'mode', 'value' => 'live', 'site' => 'Paytm'],
            ['id' => 26, 'name' => 'mode', 'value' => 'live', 'site' => 'Flutterwave'],
            ['id' => 27, 'name' => 'public_key', 'value' => 'FLWPUBK_TEST-f2a7a1ca6cdf04f4ab00d9006073ff6d-X', 'site' => 'Flutterwave'],
            ['id' => 28, 'name' => 'secret_key', 'value' => 'FLWSECK_TEST-f356353d77c4637026991ae1db0dc608-X', 'site' => 'Flutterwave'],
            ['id' => 29, 'name' => 'encryption_key', 'value' => 'FLWSECK_TEST636841ac21f2', 'site' => 'Flutterwave'],
            ['id' => 30, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Flutterwave'],
            ['id' => 31, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Mpesa'],
            ['id' => 32, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Mpesa'],
            ['id' => 33, 'name' => 'consumer_key', 'value' => 'sMpgnYW62glBlxPXbyTBEGdPib8eJLOL', 'site' => 'Mpesa'],
            ['id' => 34, 'name' => 'consumer_secret', 'value' => 'IcK2PkAFArVVVffU', 'site' => 'Mpesa'],
            ['id' => 35, 'name' => 'callback_url', 'value' => 'https://new.newtaxi.co/api/mpesa_callback', 'site' => 'Mpesa'],
            ['id' => 36, 'name' => 'business_shortcode', 'value' => '174379', 'site' => 'Mpesa'],
            ['id' => 37, 'name' => 'is_enabled', 'value' => '', 'site' => 'Mollie'],
            ['id' => 38, 'name' => 'mode', 'value' => '', 'site' => 'Mollie'],
            ['id' => 39, 'name' => 'api_key', 'value' => '', 'site' => 'Mollie'],
            ['id' => 40, 'name' => 'partner_id', 'value' => '', 'site' => 'Mollie'],
            ['id' => 41, 'name' => 'profile_id', 'value' => '', 'site' => 'Mollie'],
            ['id' => 42, 'name' => 'is_enabled', 'value' => '1', 'site' => 'Paystack'],
            ['id' => 43, 'name' => 'public_key', 'value' => 'pk_test_eb80eb30121eca5eeeca8fc1dbc57cf89b9fc973', 'site' => 'Paystack'],
            ['id' => 44, 'name' => 'paystack_secret', 'value' => 'sk_test_eabc96382b438807c819be7a7fef9bb7caace5df', 'site' => 'Paystack'],
            ['id' => 45, 'name' => 'mode', 'value' => 'sandbox', 'site' => 'Paystack'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('payment_gateway')->insert($chunk);
        }
    }
}
