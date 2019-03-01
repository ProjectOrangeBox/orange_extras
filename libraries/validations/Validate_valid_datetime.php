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
class Validate_valid_datetime extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s must contain a valid date & time.';

		/*
		optionally we are saying 0000-00-00 00:00:00 is valid
		this could be helpful as a "default" or "empty" value
		 */

		return ($field == '0000-00-00 00:00:00') ? true : (strtotime($field) > 1);
	}
} /* end class */
