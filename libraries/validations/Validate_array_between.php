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
class Validate_array_between extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s should contain between ' . $min . ' and ' . $max . ' items.';

		list($min, $max) = explode(',', $options, 2);

		if (!is_numeric($min) || !is_numeric($max)) {
			return false;
		}

		$count = count($field);

		return ($count > $min && $count < $max);
	}
} /* end class */
