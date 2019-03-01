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
class Filter_md5 extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = md5($field);
	}
} /* end class */
