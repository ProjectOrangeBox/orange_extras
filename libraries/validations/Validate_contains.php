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
class Validate_contains extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s must contain ' . $options;
		
		/* you didn't provide anything? */
		if (empty($options)) {
			$this->error_string = '%s must contain option is empty';
			
			return false;
		}
		
		return (bool) (strpos($field, $options) !== false) ? true : false;
	}
} /* end class */
