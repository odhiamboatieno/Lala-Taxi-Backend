<?php

/**
 * SMS Interface
 *
 * @package     Witaxi
 * @subpackage  Contracts
 * @category    SMS Interface
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Contracts;

interface SMSInterface
{
	function initialize();
	function send($to,$text);
}