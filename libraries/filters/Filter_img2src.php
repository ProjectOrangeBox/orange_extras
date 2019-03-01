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
 *
 */
class Filter_img2src extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$column = (!empty($options)) ? $options : 'src';

		if (preg_match('#src="([^"]+)"#', $field, $match)) {
			$field = $match[1];
		}
	}
} /* end class */
