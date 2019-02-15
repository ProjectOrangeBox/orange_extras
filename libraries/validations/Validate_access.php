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
class Validate_access extends Validate_base
{
	/* access['edit::monkeys'] translates to user can('edit::monkeys')  */
	public function validate(&$field, string $options = '') : bool
	{
		if (empty($options)) {
			$this->error_string = 'No permission option provided for %s';
		
			return false;
		}
		
		if (!is_object(ci()->user)) {
			$this->error_string = 'No user loaded %s.';

			return false;
		}

		$this->error_string = 'You do not have access to %s';

		return ci('user')->can($options);
	}
} /* end class */
