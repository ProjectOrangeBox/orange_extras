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
class Validate_not_one_of extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		// not_in_list[1,2,3,4]
		$types              = ($options) ? $options : '';
		$this->error_string = '%s must not contain one of the available selections.';

		return (!in_array($field, explode(',', $types)));
	}
} /* end class */
