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
 * @help cleans email doesn't validate it is a email
 *
 */
class Filter_email extends Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = filter_var($field, FILTER_SANITIZE_EMAIL);

		/* options is max length */
		$this->field($field)->length($options);
	}
} /* end class */
