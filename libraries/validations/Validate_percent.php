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
class Validate_percent extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s Out of Range.';

		return (bool) preg_match('#^\s*(\d{0,2})(\.?(\d*))?\s*\%?$#', $field);
	}
} /* end class */
