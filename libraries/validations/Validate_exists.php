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
class Validate_exists extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		/* exists[model_name.column] */
		$this->error_string = '%s that you requested already exists.';

		list($model, $column) = explode('.', $options, 2);

		if (empty($model)) {
			return false;
		}

		if (empty($column)) {
			return false;
		}

		/* try to load the model */
		ci()->load->model($model);

		return (method_exists(ci()->$model, 'exists')) ? ci()->$model->exists($field, $column) : false;
	}
} /* end class */
