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
class Filter_url_safe extends Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		/* $field pass by ref,options is the length */
		$this->field($field)->human()->length($options)->strip('~`!@$^()* {}[]|\;"\'<>,');
	}
} /* end class */
