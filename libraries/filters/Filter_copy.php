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
class Filter_copy extends \Filter_base
{
	/* copy[field] */
	public function filter(&$field, string $options = '') : void
	{
		$field = $this->field_data[$options];
	}
} /* end class */
