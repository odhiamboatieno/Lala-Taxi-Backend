<?php

/**
 * Image Handler Interface
 *
 * @package     Witaxi
 * @subpackage  Contracts
 * @category    Image Handler
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Contracts;

interface ImageHandlerInterface
{
	public function upload($image, $options);
	public function delete($image, $options);
	public function getImage($file_name, $options);
}