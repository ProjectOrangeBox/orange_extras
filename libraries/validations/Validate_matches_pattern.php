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
class Validate_matches_pattern extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		if (empty($options)) {
			$this->error_string = '%s pattern match option is empty.';
		
			return false;
		}
		
		$this->error_string = '%s does not match the required pattern.';

		return (bool)preg_match($options, $field);
	}
} /* end class */
