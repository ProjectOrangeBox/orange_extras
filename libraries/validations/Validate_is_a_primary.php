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
class Validate_is_a_primary extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is not a primary id.';

		$field = trim($field);
		/* is it empty? */
		if ($field == '') {
			return false;
		}
		/* is it a sql primary id? */
		if (is_numeric($field)) {
			return true;
		}
		/* is it a mongoid */
		if ((bool) preg_match('/^([a-fA-F0-9]{24})$/', $field)) {
			return true;
		}
		return false;
	}
} /* end class */
