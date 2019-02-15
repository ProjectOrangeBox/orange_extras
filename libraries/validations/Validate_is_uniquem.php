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
class Validate_is_uniquem extends Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		/* is_uniquem[model_name.column_name.$_POST[primary_key]] */
		$this->error_string = '%s is already being used.';

		list($model, $column, $postkey) = explode('.', $options, 3);

		if (empty($model)) {
			return false;
		}

		if (empty($column)) {
			return false;
		}

		if (empty($postkey)) {
			return false;
		}

		/* try to load the model */
		ci()->load->model($model);

		return ci()->$model->is_uniquem($field, $column, $postkey);
	}
} /* end class */
