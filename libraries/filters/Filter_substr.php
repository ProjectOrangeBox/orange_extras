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
class Filter_substr extends \Filter_base
{
	/**
	 * copy[field]
	 */
	public function filter(&$field, string $options = '') : void
	{
		if (!empty($options)) {
			list($a, $b) = explode($options, 2);

			$field = substr($field, $a, $b);
		}
	}
} /* end class */
