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
class Validate_allowed_types extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s must contain one of the allowed file extensions.';

		/* allowed_type[png,gif,jpg,jpeg] */
		$types = (array) explode(',', $options);

		return (in_array(pathinfo($field, PATHINFO_EXTENSION), $types, true));
	}
} /* end class */
