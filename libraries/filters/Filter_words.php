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
class Filter_words extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = ucwords($field);

		/* options is max length */
		$this->field($field)->length($options);
	}
} /* end class */
