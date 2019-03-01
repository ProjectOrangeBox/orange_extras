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
class Filter_uri extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = '/' . trim(trim(strtolower($field)), '/');
		$field = preg_replace("#^/^[0-9a-z_*/]*$#", '', $field);

		/* options is max length */
		$this->field($field)->length($options);
	}
} /* end class */
