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
class Filter_xss_clean extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = ci()->security->xss_clean($field);
	}
} /* end class */
