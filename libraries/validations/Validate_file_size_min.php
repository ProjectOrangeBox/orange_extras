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
class Validate_file_size_min extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = 'File %s size is less than ' . $options . ' bytes';

		if (!$file = $this->locate_file($field)) {
			return false;
		}

		$size = filesize($file);

		return (bool) ($size > $options);
	}
} /* end class */
