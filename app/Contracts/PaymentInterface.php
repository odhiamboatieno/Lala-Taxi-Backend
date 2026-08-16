<?php

/**
 * Payment Interface
 *
 * @package     Witaxi
 * @subpackage  Contracts
 * @category    Payment Interface
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Contracts;

interface PaymentInterface
{
	function makePayment($payment_data,$nonce);
}