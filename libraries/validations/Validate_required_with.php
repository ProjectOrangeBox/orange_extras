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
class Validate_required_with extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is required.';

		$with = $this->field_data[$options];
		$with_filledin = is_array($with) ? (bool) count($with) : (trim($with) !== '');

		/* if it's not filled in then it's not required */
		if (!$with_filledin) {
			return true;
		}

		/* if it is filled in we end up here and it is required */
		return is_array($field) ? (bool) count($field) : (trim($field) !== '');
	}
} /* end class */
