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
class Validate_is_outside extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		list($lo, $hi) = explode(',', $options, 2);

		$this->error_string = '%s must not be between ' . $lo . ' &amp; ' . $hi;

		return (bool) ($field > $hi || $field < $lo);
	}
} /* end class */
