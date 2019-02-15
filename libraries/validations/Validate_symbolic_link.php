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
class Validate_symbolic_link extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is not a symbolic link.';

		return (is_string($field) && is_link($field));
	}
} /* end class */
