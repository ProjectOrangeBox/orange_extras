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
 * @help extracts datauri to path and returns url
 *
 */
class Filter_datauri extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		$field = ci('data_uri')->extract_data_img($field);
	}
} /* end class */
