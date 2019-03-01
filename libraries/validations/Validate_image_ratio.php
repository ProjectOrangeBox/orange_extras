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
class Validate_image_ratio extends \Validate_base
{
	/* options can be a number or a string like 4:3,16:9 */
	public function validate(&$field, string $options = '') : bool
	{
		if (!function_exists('getimagesize')) {
			throw new \Exception('Get Image Size Function Not Supported');
		}

		$this->error_string = '%s does must have a ratio (width/height) of ' . $options . '.';

		if (strpos($options, ':') === false) {
			return false;
		}

		list($width, $height) = explode(':', $options);

		if (!is_numeric($width) || !is_numeric($height)) {
			return false;
		}

		$ratio = $width / $height;

		if (!$file = $this->locate_file($field)) {
			return false;
		}

		$imageInfo   = getimagesize($file);
		$actualRatio = $imageInfo[0] / $imageInfo[1];

		return abs($actualRatio - $ratio);
	}
} /* end class */
