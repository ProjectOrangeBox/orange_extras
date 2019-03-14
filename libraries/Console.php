<?php
/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Console class.
 *
 * Wrapper around \League\CLImate\CLImate
 * with additional methods provided
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0.0
 * @filesource
 *
 * @uses # \League\CLImate\CLImate
 *
 */

class Console
{
	/**
	 * $climate
	 *
	 * @var undefined
	 */
	protected $climate;

	/**
	 * $window_width
	 *
	 * @var integer
	 */
	protected $window_width = 0;

	/**
	 * $command_prefix
	 *
	 * @var string
	 */
	protected $command_prefix = '/cli/';

	/**
	 * $command_prefix
	 *
	 * @var string
	 */
	protected $args = [];

	/**
	 * $command_prefix
	 *
	 * @var string
	 */
	protected $argc = 0;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->args = $_SERVER['argv'];
		$this->argc = $_SERVER['argc'];

		$this->climate = new \League\CLImate\CLImate;

		if (isset($_SERVER['TERM_COLUMNS'])) {
			$this->window_width = (int)$_SERVER['TERM_COLUMNS'];
		} else {
			$this->window_width = (int)exec('tput cols');
		}
	}

	/**
	 * __call
	 *
	 * @param mixed $method
	 * @param mixed $arguments
	 * @return void
	 */
	public function __call($method, $arguments)
	{
		$climate_responds = call_user_func_array([$this->climate,$method], $arguments);

		return (in_array($method, ['checkboxes','radio','password','input','confirm'])) ? $climate_responds : $this;
	}

	/**
	 * help_command
	 *
	 * @param mixed $help
	 * @param mixed $command
	 * @return void
	 */
	public function help(array $help, bool $self_help = true) : Console
	{
		$this->h1('Help');

		if ($self_help) {
			$bt = debug_backtrace(false, 2);

			$controller = str_replace('_', '-', strtolower(substr($bt[0]['file'], strpos($bt[0]['file'], '/controllers/') + 17, -14)));

			$this->help_command('Display this help.', [$controller,$controller.'/help']);
		}

		foreach ($help as $text_command) {
			$text = key($text_command);
			$command = array_shift($text_command);

			if (is_numeric($text)) {
				$this->climate->info($this->format_for_options($command));
			} else {
				$this->help_command($text, $command);
			}
		}

		$this->br()->white('** if you have spark installed you can just type "spark controller/method...".')->br();

		return $this;
	}

	protected function format_for_controller(string $string) : string
	{
		$controller = '??';

		$bt = debug_backtrace(false, 8);

		foreach ($bt as $record) {
			$file = $record['file'];

			$pos = strpos($file,'/controllers/');

			if ($pos !== false) {
				$controller = str_replace('_', '-', strtolower(substr($file, $pos + 17, -14)));
			}

		}

		return str_replace('%%', $controller, $string);
	}

	protected function format_for_options(string $string) : string
	{
		if (substr($string, 0, 1) == '-') {
			if (substr($string, -1) == ']') {
				$left_bracket_start = strrpos($string, '[');
				$string = substr($string, 0, $left_bracket_start).'<yellow>'.substr($string, $left_bracket_start).'</yellow>';
			}

			$string = '<blue>'.substr($string, 0, 2).'</blue> <light_blue>'.substr($string, 2).'</light_blue>';
		}

		return $string;
	}

	/**
	 * help_command
	 *
	 * @param mixed $help
	 * @param mixed $command
	 * @return void
	 */
	public function help_command($help, $command) : Console
	{
		foreach ((array)$help as $txt) {
			if (!empty($txt)) {
				$txt = $this->format_for_options($txt);

				$this->climate->info($txt);
			}
		}

		foreach ((array)$command as $txt) {
			if (!empty($txt)) {
				$txt = $this->format_for_controller($txt);

				$this->command($txt);
			}
		}

		$this->hr();

		return $this;
	}

	/**
	 * br
	 *
	 * @param int $count=1
	 * @return void
	 */
	public function br(int $count=1) : Console
	{
		for ($i = 0; $i < $count; $i++) {
			$this->climate->br();
		}

		return $this;
	}

	/**
	 * hr
	 *
	 * @return void
	 */
	public function hr() : Console
	{
		$this->climate->border('-', $this->window_width);

		return $this;
	}

	/**
	 * h1
	 *
	 * @param string $txt
	 * @return void
	 */
	public function h1(string $txt) : Console
	{
		$this->climate->cyan()->bold()->underline()->out($txt);

		return $this;
	}

	/**
	 * h2
	 *
	 * @param string $txt
	 * @return void
	 */
	public function h2(string $txt) : Console
	{
		$this->climate->cyan()->bold()->out($txt);

		return $this;
	}

	/**
	 * command
	 *
	 * @param string $txt
	 * @return void
	 */
	public function command(string $txt) : Console
	{
		$this->climate->yellow()->tab()->out($this->command_prefix.$txt);

		return $this;
	}

	/**
	 * text
	 *
	 * @param string $txt
	 * @return void
	 */
	public function text(string $txt) : Console
	{
		$this->climate->white()->out($txt);

		return $this;
	}

	/**
	 * error
	 *
	 * @param string $txt
	 * @param mixed bool
	 * @return void
	 */
	public function error(string $txt, bool $die = true) : Console
	{
		$this->climate->red()->bold()->out('⛔ '.$txt);

		if ($die) {
			$this->climate->br();
			exit(1);
		}

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param $responds
	 * @param string $option i
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function get_arg($named, bool $required = false, string $text = null, $default = null, $option_string='a')
	{
		/**
		 * Warning Multiple Exists
		 */

		/* first search by option string because if that's there then we are done. */
		$value = $this->get_arg_by_option($option_string, false, '', '%%not-found%%');

		if ($value != '%%not-found%%') {
			/* exit 1 */
			return $value;
		}

		if (is_string($named)) {
			/* exit 2 */
			return $this->get_arg_by_option($named, $required, $text, $default);
		} elseif (is_numeric($named)) {
			/* exit 3 */
			return $this->get_arg_by_position($named, $required, $text, $default, $option_string);
		} else {
			/* full stop */
			$this->error('Please supply option or position number.');
		}
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param $responds
	 * @param string $option i
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function get_arg_by_position(int $number, bool $required = false, string $text = null, $default = null, string $option_string='a')
	{
		/* the first useable arg is 2 */
		$number = $number + 1;

		$arg = (!isset($this->args[$number])) ? $default : $this->args[$number];

		if ($required && empty($arg)) {
			$err = ($text) ? 'a '.$text.' (argument '.($number - 1).')' : 'argument '.($number - 1);

			$this->br()->error('Please provide '.$err.'.');
		}

		return $arg;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param $responds
	 * @param string $option i
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function get_arg_by_option(string $named, bool $required = false, string $text = null, $default = null)
	{
		/**
		 * Warning Multiple Exists
		 */
		foreach ($this->args as $idx=>$value) {
			if (strtolower($value) == '-'.$named) {
				return $this->get_arg_by_position(($idx + 1), $required, $text, $default);
			}
		}

		/* let this handle the default, required, etc... */
		return $this->get_arg_by_position(-999, $required, $text, $default);
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param $responds
	 * @param string $option i
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function get_input(string $text, string $option) : string
	{
		/**
		 * Warning Multiple Exists
		 */

		/* did they include the option with -? */
		foreach ($this->args as $idx=>$arg) {
			if ($arg == '-'.$option) {
				if (isset($this->args[$idx+1])) {
					return $this->args[$idx+1];
				}
			}
		}

		$this->br();

		/* else ask */
		$input = $this->climate->input('<green>'.$text.'</green>');

		$responds = trim($input->prompt());

		if (empty($responds)) {
			$this->error('Nothing entered.');
		}

		return $responds;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param array $options
	 * @param $responds
	 * @param string $option c
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function get_checkboxes(string $text, array $options, string $option) : array
	{
		/**
		 * Warning Multiple Exists
		 */

		/* did they include the option with -i? */
		foreach ($this->args as $idx=>$arg) {
			if ($arg == '-'.$option) {
				if (isset($this->args[$idx+1])) {
					return explode(',', $this->args[$idx+1]);
				}
			}
		}

		/* else ask */

		if (!count($options)) {
			$this->error('Nothing options provided.');
		}

		if (count($options) == 1) {
			$responds = [];

			$responds[] = key($options);

			return $responds;
		}

		$this->br();

		$input = $this->climate->checkboxes($text, $options);

		$responds = $input->prompt();

		if (!count($responds)) {
			$this->error('Nothing selected.');
		}

		return $responds;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param array $options
	 * @param $responds
	 * @param string $option r
	 *
	 * @throws
	 * @return
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function get_radioboxes(string $text, array $options, string $option) : string
	{
		/**
		 * Warning Multiple Exists
		 */

		/* did they include the option with -i? */
		foreach ($this->args as $idx=>$arg) {
			if ($arg == '-'.$option) {
				if (isset($this->args[$idx+1])) {
					return $this->args[$idx+1];
				}
			}
		}

		/* else ask */

		if (!count($options)) {
			$this->error('Nothing options provided.');
		}

		if (count($options) == 1) {
			return key($options);
		}

		$this->br();

		$input =  $this->climate->radio('<green>'.$text.'</green>', $options);

		$responds = $input->prompt();

		if (empty($responds)) {
			$this->error('Nothing selected.');
		}

		return $responds;
	}
} /* end class */
