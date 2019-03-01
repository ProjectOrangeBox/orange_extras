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
class Validate_md5 extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$options            = ($options) ? $options : 32;
		$this->error_string = '%s is not a valid hash.';

		/* default message */
		return (bool) preg_match('/^([a-fA-F0-9]{' . (int) $options . '})$/', $field);
	}
} /* end class */
