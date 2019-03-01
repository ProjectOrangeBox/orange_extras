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
class Filter_url extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		if ($field === 'http://' or $field === '') {
			$field = '';
		}

		if (strpos($field, 'http://') !== 0 && strpos($field, 'https://') !== 0) {
			$field = 'http://' . $field;
		}
	}
} /* end class */
