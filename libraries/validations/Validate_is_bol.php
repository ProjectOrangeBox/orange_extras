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
class Validate_is_bol extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is invalid.';

		/* PHP's built in function */
		if (is_bool($field)) {
			return true;
		}
		/* our tests */
		return (in_array(strtolower($field), array_merge($this->true_array, $this->false_array), true)) ? true : false;
	}
} /* end class */
