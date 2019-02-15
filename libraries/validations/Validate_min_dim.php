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
class Validate_min_dim extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$dim = explode(',', $options);
		$this->error_string = 'The width & height cannot be less than ' . $dim[0] . 'x' . $dim[1];

		if (!$file = $this->locate_file($field)) {
			return false;
		}

		$size = getimagesize($file);

		return (bool) ($size[0] > $dim[0] && $size[1] > $dim[1]);
	}
} /* end class */
