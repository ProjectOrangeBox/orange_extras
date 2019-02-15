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
class Validate_is_closure extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is not a closure.';

		return is_object($field) && ($field instanceof Closure);
	}
} /* end class */
