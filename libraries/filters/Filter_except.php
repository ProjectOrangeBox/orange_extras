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
class Filter_except extends Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		if (!empty($options)) {
			/* options is what is stripped "except" */
			$field = preg_replace("/[^" . preg_quote($options, "/") . "]/", '', $field);
		}
	}
} /* end class */
