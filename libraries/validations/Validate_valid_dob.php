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
class Validate_valid_dob extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$yrs                = ($options) ? $options : 18;
		$this->error_string = '%s must be more than ' . $yrs . ' years.';

		/* is this a valid date? strtotime */
		if (!strtotime($field)) {
			return false;
		}

		/* less than the time */
		if (strtotime($field) > strtotime('-' . $yrs . ' year', time())) {
			return false;
		}

		/* greater than a super old person */
		if (strtotime($field) < strtotime('-127 year', time())) {
			return false;
		}

		return true;
	}
} /* end class */
