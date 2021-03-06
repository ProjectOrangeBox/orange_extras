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
 * @help filter_replace[find,replace,find1,replace1]
 *
 */
class Filter_replace extends \Filter_base
{
	public function filter(&$field, string $options = '') : void
	{
		/* built the key value pair */
		$items  = explode(',', $options);
		$idx    = 0;
		$keys   = [];
		$values = [];

		foreach ($items as $item) {
			$idx++;
			if ($idx % 2) {
				$keys[] = $item;
			} else {
				$values[] = $item;
			}
		}

		if (count($keys) > 0 && count($values) > 0) {
			$field = str_replace($keys, $values, $field);
		}
	}
} /* end class */
