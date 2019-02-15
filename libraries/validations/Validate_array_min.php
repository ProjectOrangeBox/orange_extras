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
class Validate_array_min extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		if (!is_numeric($options)) {
			return false;
		}

		$this->error_string = '%s should contain more than ' . $options . ' items.';

		return (count($field) > $options);
	}
} /* end class */
