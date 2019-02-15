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
class Validate_hmac extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$success = true;

		/* if it dosn't start with out HMAC prefix then just return TRUE and don't modify it */
		if (substr($field, 0, 3) === '$H$') {
			$key                = ci()->config->item('encryption_key');
			list($value, $hmac) = explode(chr(0), base64_decode(substr($field, 3)), 2);
			if (md5($value . $key) === $hmac) {
				$field = $value;
			} else {
				$field   = null;
				$success = false;
			}
		}

		return $success;
	}
} /* end class */
