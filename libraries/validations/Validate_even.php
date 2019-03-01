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
class Validate_even extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is not a even number.';

		return ((int) $field % 2 === 0);
	}
} /* end class */
