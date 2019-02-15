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
 * @help Pear::mask_string('1234567812345678', '************0000') ************5678
 * @help Pear::mask_string('1234567812345678', '**** **** **** 0000') **** **** **** 5678
 * @help Pear::mask_string('1234567812345678', '**** - **** - **** - 0000', ' -') **** - **** - **** - 5678
 *
 */
class Pear_mask_string extends Pear_plugin
{
	public function render($string=null, $format=null, $ignore=' ')
	{
		$result = '';
		$fpos = 0;
		$spos = 0;

		while ((strlen($format) - 1) >= $fpos) {
			if (ctype_alnum(substr($format, $fpos, 1))) {
				$result .= substr($string, $spos, 1);
				$spos++;
			} else {
				$result .= substr($format, $fpos, 1);

				if (strpos($ignore, substr($format, $fpos, 1)) === false) {
					++$spos;
				}
			}

			++$fpos;
		}

		return $result;
	}
}
