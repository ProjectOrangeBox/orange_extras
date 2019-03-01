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
class Validate_phone extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is invalid.';

		return (bool) preg_match('/^\(?[\d]{3}\)?[\s-]?[\d]{3}[\s-]?[\d]{4}$/', $field);
	}
} /* end class */
