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
class Filter_encode_php_tags extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $field);
	}
} /* end class */
