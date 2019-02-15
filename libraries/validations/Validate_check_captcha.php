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
class Validate_check_captcha extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		// !todo -- captcha
		$this->error_string = 'Captcha is incorrect.';

		return true;
	}
} /* end class */
