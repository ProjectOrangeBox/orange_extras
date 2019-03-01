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
class Validate_alpha_extra extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		// Alpha-numeric with periods,underscores,spaces and dashes
		$this->error_string = '%s may only contain alpha-numeric characters,spaces,periods,underscores,and dashes.';

		return (bool) preg_match("/^([\.\s-a-z0-9_-])+$/i", $field);
	}
} /* end class */
