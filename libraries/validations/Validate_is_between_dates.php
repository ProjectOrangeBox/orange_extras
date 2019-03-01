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
class Validate_is_between_dates extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		list($after, $before) = explode(',', $options);
		$this->error_string   = '%s must be between ' . date('F j,Y', strtotime($after)) . ' and ' . date('F j,Y', strtotime($before)) . '.';

		/* are either of these not valid times? */
		if (!strtotime($after) || !strtotime($before)) {
			return false;
		}
		$is_after  = (strtotime($field) > strtotime($after)) ? true : false;
		$is_before = (strtotime($field) < strtotime($before)) ? true : false;
		return (bool) ($is_after && $is_before);
	}
} /* end class */
