<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'user_id' => 10004, 'customer_id' => 'cus_Li8bVOriLsvnwT', 'intent_id' => 'seti_1L0iKfJbZQ9xXEmX60Bq9lP0', 'payment_method_id' => 'pm_1L0iKiJbZQ9xXEmXqdqa7K2T', 'brand' => 'mastercard', 'last4' => '4444', 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'user_id' => 10003, 'customer_id' => 'cus_Lj7WUol47sc4vV', 'intent_id' => 'seti_1L1fH0JbZQ9xXEmXRbxcELj2', 'payment_method_id' => 'pm_1L1fH5JbZQ9xXEmX5VqS2uby', 'brand' => 'mastercard', 'last4' => '4444', 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'user_id' => 10010, 'customer_id' => 'cus_T38l6gXCyDjmJP', 'intent_id' => 'seti_1S72ULJbZQ9xXEmXKm5SlBBC', 'payment_method_id' => 'pm_1S72UNJbZQ9xXEmXbAt1ydbn', 'brand' => 'visa', 'last4' => '4242', 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'user_id' => 10018, 'customer_id' => 'cus_URBQlqkE2dPksC', 'intent_id' => null, 'payment_method_id' => null, 'brand' => null, 'last4' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => 5, 'user_id' => 10017, 'customer_id' => 'cus_UUpxp9aXvuJJHz', 'intent_id' => null, 'payment_method_id' => null, 'brand' => null, 'last4' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => 6, 'user_id' => 10019, 'customer_id' => 'cus_UWJvHoU675QcI1', 'intent_id' => 'seti_1TZGwMJbZQ9xXEmXzBXxNX10', 'payment_method_id' => 'pm_1TZGx8JbZQ9xXEmXfth5mPmA', 'brand' => 'visa', 'last4' => '4242', 'created_at' => null, 'updated_at' => null],
            ['id' => 7, 'user_id' => 10021, 'customer_id' => 'cus_UadsAb2CKTfyJw', 'intent_id' => null, 'payment_method_id' => null, 'brand' => null, 'last4' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => 8, 'user_id' => 10025, 'customer_id' => 'cus_UeD5Lx1odpCvDg', 'intent_id' => null, 'payment_method_id' => null, 'brand' => null, 'last4' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => 9, 'user_id' => 10026, 'customer_id' => 'cus_UeFipvttk58kDL', 'intent_id' => null, 'payment_method_id' => null, 'brand' => null, 'last4' => null, 'created_at' => null, 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('payment_method')->insert($chunk);
        }
    }
}
