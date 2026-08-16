<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTransactionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'pay_for' => 'Wallet TopUp', 'amount' => 120.0, 'transaction_id' => 'pi_3S72UcJbZQ9xXEmX1riV0ksp', 'currency_code' => 'USD', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:120', 'user_id' => 10010, 'created_at' => '2025-09-14 00:09:51', 'updated_at' => '2025-09-13 23:09:57'],
            ['id' => 2, 'pay_for' => 'Wallet TopUp', 'amount' => 500.0, 'transaction_id' => 'pi_3TWbEUJbZQ9xXEmX1c1or5jQ', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:16340', 'user_id' => 10010, 'created_at' => '2026-05-13 11:45:47', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 3, 'pay_for' => 'Wallet TopUp', 'amount' => 660.0, 'transaction_id' => 'pi_3TWbEgJbZQ9xXEmX1ePzmYyR', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:17000', 'user_id' => 10010, 'created_at' => '2026-05-13 11:45:59', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 4, 'pay_for' => 'Wallet TopUp', 'amount' => 500.0, 'transaction_id' => 'pi_3TWieMJbZQ9xXEmX1w4fGXl0', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:17500', 'user_id' => 10010, 'created_at' => '2026-05-13 19:40:59', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 5, 'pay_for' => 'Trip Payment', 'amount' => 1099.99, 'transaction_id' => 'pi_3TWxV1JbZQ9xXEmX0HUfi4UW', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 11:32:21', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 6, 'pay_for' => 'Trip Payment', 'amount' => 999.99, 'transaction_id' => 'pi_3TWzAGJbZQ9xXEmX0FLmc0Rc', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 13:19:01', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 7, 'pay_for' => 'Trip Payment', 'amount' => 1499.99, 'transaction_id' => 'pi_3TWzSNJbZQ9xXEmX06JI5YCf', 'currency_code' => 'KES', 'description' => 'Travel From Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 13:37:45', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 8, 'pay_for' => 'Trip Payment', 'amount' => 1099.99, 'transaction_id' => 'pi_3TWzjNJbZQ9xXEmX0AqIOhkZ', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 13:55:18', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 9, 'pay_for' => 'Trip Payment', 'amount' => 1199.99, 'transaction_id' => 'pi_3TX10yJbZQ9xXEmX1TEoq7RJ', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 15:17:33', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 10, 'pay_for' => 'Trip Payment', 'amount' => 1499.99, 'transaction_id' => 'pi_3TX1cVJbZQ9xXEmX1Hnf4eT5', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 15:56:20', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 11, 'pay_for' => 'Trip Payment', 'amount' => 999.99, 'transaction_id' => 'pi_3TX3pEJbZQ9xXEmX1E6XWsdB', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 18:17:37', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 12, 'pay_for' => 'Trip Payment', 'amount' => 1499.99, 'transaction_id' => 'pi_3TX64RJbZQ9xXEmX10JLgnJZ', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-14 20:41:29', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 13, 'pay_for' => 'Trip Payment', 'amount' => 999.99, 'transaction_id' => 'pi_3TXIN6JbZQ9xXEmX0gOBvsU7', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-15 09:49:34', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 14, 'pay_for' => 'Trip Payment', 'amount' => 1099.99, 'transaction_id' => 'pi_3TXVx2JbZQ9xXEmX07Hzgqgo', 'currency_code' => 'KES', 'description' => 'Travel From GX54+3PC, Kitengela, Kenya  to GX54+3PC, Kitengela, Kenya', 'user_id' => 10010, 'created_at' => '2026-05-16 00:19:33', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 15, 'pay_for' => 'Wallet TopUp', 'amount' => 2000.0, 'transaction_id' => 'pi_3TZGxmJbZQ9xXEmX0xJr5Cq7', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:2000', 'user_id' => 10019, 'created_at' => '2026-05-20 20:43:36', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 16, 'pay_for' => 'Wallet TopUp', 'amount' => 10000.0, 'transaction_id' => 'pi_3TZGyCJbZQ9xXEmX1tIDfMFQ', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:12000', 'user_id' => 10019, 'created_at' => '2026-05-20 20:44:01', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 17, 'pay_for' => 'Wallet TopUp', 'amount' => 1000.0, 'transaction_id' => 'pi_3TeHdBJbZQ9xXEmX1KqB9VsJ', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:100999.99', 'user_id' => 10019, 'created_at' => '2026-06-03 16:27:02', 'updated_at' => '0000-00-00 00:00:00'],
            ['id' => 18, 'pay_for' => 'Wallet TopUp', 'amount' => 300.0, 'transaction_id' => 'pi_3TezB2JbZQ9xXEmX0ZRiW34H', 'currency_code' => 'KES', 'description' => 'Your app wallet has been successfully topped up. Current Balance Amount is:17800', 'user_id' => 10010, 'created_at' => '2026-06-05 14:56:53', 'updated_at' => '0000-00-00 00:00:00'],
        ];

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('payment_transactions')->insert($chunk);
        }
    }
}
