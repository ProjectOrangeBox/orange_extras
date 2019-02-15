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
class Filter_combined extends Filter_base
{
	/**
	 * combined[{fielda},{fieldb},::,{fieldc}]
	 */
	public function filter(&$field, string $options = '') : void
	{
		$fields = explode(',', $options);

		foreach ($fields as $f) {
			if (substr($f, 0, 1) == '{' && substr($f, -1) == '}') {
				$combined .= $this->field_data[substr($f, 1, -1)];
			} else {
				$combined .= $f;
			}
		}

		$field = $combined;
	}
} /* end class */
