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
class Filter_phone extends Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		/* this needs to be passed by reference */
		$field = preg_replace('/[^0-9x]+/', ' ', $field);
		$field = preg_replace('/ {2,}/', ' ', $field);

		/* $field pass by ref,options is the length */
		$this->field($field)->human()->length($options);
	}
} /* end class */
