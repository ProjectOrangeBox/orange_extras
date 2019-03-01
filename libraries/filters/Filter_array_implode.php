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
class Filter_array_implode extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$options = ($options) ? $options : ' ';

		if (is_array($field)) {
			$field = implode($options, $field);
		}
	}
} /* end class */
