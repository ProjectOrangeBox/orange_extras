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
class Validate_exact_width extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		if (!function_exists('getimagesize')) {
			throw new \Exception('Get Image Size Function Not Supported');
		}

		$this->error_string = 'Width must be %s.';

		if (!$file = $this->locate_file($field)) {
			return false;
		}

		$size = getimagesize($file);

		return (bool) ($size[0] == $options);
	}
} /* end class */
