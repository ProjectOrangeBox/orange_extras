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
class Validate_ip extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		/* *.*.*.*,10.1.1.*,10.*.*.*,etc... */
		$this->error_string = '%s is a invalid ip.';

		$sections = explode('.', $field);
		$match    = ($options) ? explode('.', $options) : ['*', '*', '*', '*'];
		if (count($sections) != 4 || count($match) != 4) {
			return false;
		}
		for ($idx = 0; $idx <= 3; $idx++) {
			if ($match[$idx] != '*' && $sections[$idx] != $match[$idx]) {
				return false;
			}
		}
		return true;
	}
} /* end class */
