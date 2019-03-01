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
class Validate_credit_card extends \Validate_base
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s is not a valid credit card number.';

		$cards = array(
			"visa"       => "(4\d{12}(?:\d{3})?)",
			"amex"       => "(3[47]\d{13})",
			"jcb"        => "(35[2-8][89]\d\d\d{10})",
			"maestro"    => "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
			"solo"       => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
			"mastercard" => "(5[1-5]\d{14})",
			"switch"     => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
		);
		$names   = array("Visa", "American Express", "JCB", "Maestro", "Solo", "Mastercard", "Switch");
		$matches = array();
		$pattern = "#^(?:" . implode("|", $cards) . ")$#";
		$result  = preg_match($pattern, str_replace(" ", "", $field), $matches);

		return ($result > 0);
	}
} /* end class */
