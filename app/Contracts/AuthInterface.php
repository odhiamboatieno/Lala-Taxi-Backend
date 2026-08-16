<?php

/**
 * Auth Interface
 *
 * @package     Witaxi
 * @subpackage  Contracts
 * @category    Auth Interface
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Contracts;

use Illuminate\Http\Request;

interface AuthInterface
{
	public function create(Request $request);
	public function login($credentials);
}