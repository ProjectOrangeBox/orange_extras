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
class Validate_valid_date extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is a invalid date.';

		if (empty($field)) {
			return true;
		}

		/* basic format check */
		if (!preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4}$/', $field)) {
			return false;
		}

		list($d, $m, $y) = explode('/', $field);

		return checkdate($d, $m, $y);
	}
} /* end class */
