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
class Validate_is_before_date extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$format = 'F j,Y,g:ia';
		$time   = strtotime('now');
		$error  = 'now';
		if (strpos($options, '@') !== false) {
			list($time, $format) = explode('@', $options, 2);
			$time                = strtotime($time);
			$error               = date($format, $time);
		}
		$this->error_string = '%s must be before ' . $error . '.';

		return (!strtotime($field)) ? false : (strtotime($field) < $time);
	}
} /* end class */
