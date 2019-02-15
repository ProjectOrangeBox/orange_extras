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
class Validate_is_image_file extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is not a valid file.';

		if (!$file = $this->locate_file($field)) {
			return false;
		}

		return (bool) (preg_match("/(.)+\\.(jp(e) {0,1}g$|gif$|png$)/i", $file));
	}
} /* end class */
