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
class Validate_primary_exists extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		/* little assumption here $this->database is loaded */
		/* $options = model name */
		$this->error_string = '%s that you requested is unavailable.';

		if (empty($options)) {
			return false;
		}

		/* try to load the model */
		ci()->load->model($options);

		return ci()->$options->primary_exists($field);
	}
} /* end class */
