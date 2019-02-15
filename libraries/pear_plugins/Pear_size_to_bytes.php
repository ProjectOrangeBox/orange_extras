<?php
/**
 * Orange Framework validation rule
 *
 * This content is released under the MIT License (MIT)
 *
 * @package	CodeIgniter / Orange
 * @author	Don Myers
 * @license http://opensource.org/licenses/MIT MIT License
 * @link	https://github.com/ProjectOrangeBox
 */
class Pear_size_to_bytes extends Pear_plugin
{
	public function render($format=null)
	{
		$units = ['B'=>0,'K'=>1,'KB'=>1,'M'=>2,'MB'=>2,'GB'=>3,'G'=>3,'TB'=>4,'T'=>4];

		$number = strtoupper(trim(preg_replace("/[^0-9\.]/", '', $format)));
		$letter = strtoupper(trim(substr($format, strlen($number))));

		return $number * pow(1024, $units[$letter]);
	}
}
