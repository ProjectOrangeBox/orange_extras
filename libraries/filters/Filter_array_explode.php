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
class Filter_array_explode extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$options = ($options) ? $options : ' ';

		if (is_string($field)) {
			$field = explode($options, $field);
		}
	}
} /* end class */
