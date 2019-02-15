<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * Modified to work with CodeIgniter by Lonnie Ezell.
 * And provided additional features and tools, like the progress bar
 * and different option handling.
 */

class Console
{
	public $debug = false;
	public $segments = [];
	public $options = [];

	protected $escape;
	protected $inProgress = true;
	protected $foreground_colors = array();
	protected $background_colors = array();

	public function __construct()
	{
		// Set up shell colors
		$this->foreground_colors['off'] = '0;0';

		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->parseCommand();
	}

	/**
	 * Parses the command line it was called from and collects all
	 * options and valid segments.
	 *
	 * I tried to use getopt but had it fail occasionally to find any
	 * but argc has always had our back. We don't have all of the "power"
	 * of getopt but this does us just fine.
	 */
	protected function parseCommand()
	{
		$options_found = false;

		for ($i = 1; $i < $_SERVER['argc']; $i++) {
			// If there's no '-' at the beginning of the argument
			// then add it to our segments.
			if (! $options_found && strpos($_SERVER['argv'][$i], '-') === false) {
				$this->segments[] = $_SERVER['argv'][$i];
				continue;
			}

			$options_found = true;

			if (substr($_SERVER['argv'][$i], 0, 1) != '-') {
				continue;
			}

			$arg = str_replace('-', '', $_SERVER['argv'][$i]);
			$value = null;

			// If the next item starts with a dash it's a value
			if (isset($_SERVER['argv'][$i + 1]) && substr($_SERVER['argv'][$i + 1], 0, 1) != '-') {
				$value = $_SERVER['argv'][$i + 1];
				$i++;
			}

			$this->options[$arg] = $value;
		}
	}

	public function option_plus_one($match)
	{
		foreach ($_SERVER['argv'] as $idx=>$a) {
			if (substr($a, 0, 1) == '-' && substr($a, -1) == $match) {
				return $_SERVER['argv'][$idx+1];
			}
		}

		return null;
	}

	public function segment($num)
	{
		return $this->segments[$num];
	}

	public function option($x, $default=null)
	{
		return (array_key_exists($x, $this->options)) ? $this->options[$x] : $default;
	}

	// Returns colored string
	public function out($input, $die=false, $stream='STDOUT')
	{
		if (is_array($input)) {
			$input = implode(PHP_EOL, $input);
		}

		foreach ($this->foreground_colors as $color=>$console) {
			$input = str_replace('<'.$color.'>', "\033[".$console."m", $input);
			$input = str_replace('</'.$color.'>', "\033[0m", $input);
		}

		$input = str_replace('\n', chr(10), $input)."\033[0m";

		/* which stream? */
		if ($stream == 'STDOUT') {
			fwrite(STDOUT, $input);
		} else {
			fwrite(STDERR, $input);
		}

		if ($die) {
			exit(0);
		}

		return $this;
	}

	public function e($input)
	{
		$this->out($input.PHP_EOL);

		return $this;
	}

	public function output($input)
	{
		$this->out($input.PHP_EOL);

		return $this;
	}

	public function debug($bol)
	{
		$this->debug = $bol;

		return $this;
	}

	public function error($input, $die=true)
	{
		return $this->out('<light_red>'.$input.'</off>'.PHP_EOL, $die, 'STDERR');
	}
	
	public function heading($txt)
	{
		$this->e('<cyan>'.str_pad('- '.$txt.' ', exec('tput cols'), '-', STR_PAD_RIGHT).'</off>');
	}

	public function sub_heading($txt)
	{
		$this->e('<blue> ## '.$txt.' ##</off>');
	}

	public function success($txt)
	{
		$this->e('<green>✓ '.$txt.'</off>');
	}

	public function info($txt)
	{
		$this->e('<cyan>? '.$txt.'</off>');
	}

	public function warning($txt)
	{
		$this->e('<yellow>⚠ '.$txt.'</off>');
	}

	public function danger($txt)
	{
		$this->e('<red>X '.$txt.'</off>');
	}

	/**
	 * Get input from the shell, using readline or the standard STDIN
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param	string|int	$name	the name of the option (int if unnamed)
	 * @return	string
	 */
	public function in($prefix = '')
	{
		if ($this->readline_support) {
			return readline($prefix);
		}

		echo $prefix;

		return fgets(STDIN);
	}

	public function input($prefix = '')
	{
		return $this->in($prefix);
	}

	public function d()
	{
		if ($this->debug) {
			foreach (func_get_args() as $a) {
				var_dump($a);
			}
		}

		return $this;
	}

	/**
	 * Asks the user for input.  This can have either 1 or 2 arguments.
	 *
	 * Usage:
	 *
	 * // Waits for any key press
	 * $this->cli->prompt();
	 *
	 * // Takes any input
	 * $color = $this->cli->prompt('What is your favorite color?');
	 *
	 * // Takes any input, but offers default
	 * $color = $this->cli->prompt('What is your favourite color?', 'white');
	 *
	 * // Will only accept the options in the array
	 * $ready = $this->cli->prompt('Are you ready?', array('y','n'));
	 *
	 * @return	string	the user input
	 */
	public function prompt()
	{
		$args = func_get_args();

		$options = array();
		$output = '';
		$default = null;

		// How many we got
		$arg_count = count($args);

		// Is the last argument a boolean? True means required
		$required = end($args) === true;

		// Reduce the argument count if required was passed, we don't care about that anymore
		$required === true and --$arg_count;

		// This method can take a few crazy combinations of arguments, so lets work it out
		switch ($arg_count) {
			case 2:
				// E.g: $ready = $this->cli->prompt('Are you ready?', array('y','n'));
				if (is_array($args[1])) {
					list($output, $options)=$args;
				}
				// E.g: $color = $this->cli->prompt('What is your favourite color?', 'white');
				elseif (is_string($args[1])) {
					list($output, $default)=$args;
				}
			break;

			case 1:
				// No question (probably been asked already) so just show options
				// E.g: $ready = $this->cli->prompt(array('y','n'));
				if (is_array($args[0])) {
					$options = $args[0];
				}
				// Question without options
				// E.g: $ready = $this->cli->prompt('What did you do today?');
				elseif (is_string($args[0])) {
					$output = $args[0];
				}
			break;
		}

		// If a question has been asked with the read
		if ($output !== '') {
			$extra_output = '';

			if ($default !== null) {
				$extra_output = ' [ Default: "'.$default.'" ]';
			} elseif ($options !== array()) {
				$extra_output = ' [ '.implode(', ', $options).' ]';
			}

			$this->out($output.$extra_output.': ');
		}

		// Read the input from keyboard.
		($input = trim($this->input())) or $input = $default;

		// No input provided and we require one (default will stop this being called)
		if (empty($input) and $required === true) {
			$this->write('This is required.');
			$this->new_line();

			$input = call_user_func(array($this, 'prompt'), $args);
		}

		// If options are provided and the choice is not in the array, tell them to try again
		if (! empty($options) && ! in_array($input, $options)) {
			$this->write('This is not a valid option. Please try again.');
			$this->new_line();

			$input = call_user_func_array(array($this, 'prompt'), $args);
		}

		return $input;
	}

	/**
	 * Beeps a certain number of times.
	 *
	 * @param	int $num	the number of times to beep
	 */
	public function beep($num = 1)
	{
		echo str_repeat("\x07", $num);

		return $this;
	}

	/**
	 * Waits a certain number of seconds, optionally showing a wait message and
	 * waiting for a key press.
	 *
	 * @param	int		$seconds	number of seconds
	 * @param	bool	$countdown	show a countdown or not
	 */
	public function wait($seconds = 0, $countdown = false)
	{
		if ($countdown === true) {
			$time = $seconds;

			while ($time > 0) {
				$this->out($time.'... ');
				sleep(1);
				$time--;
			}

			$this->write();
		} else {
			if ($seconds > 0) {
				sleep($seconds);
			} else {
				$this->write($this->wait_msg);
				$this->read();
			}
		}

		return $this;
	}

	/**
	 * Enter a number of empty lines
	 *
	 * @param	integer	Number of lines to output
	 * @return	void
	 */
	public function new_line($num = 1)
	{
		// Do it once or more, write with empty string gives us a new line
		for ($i = 0; $i < $num; $i++) {
			$this->e('');
		}

		return $this;
	}

	/**
	 * Clears the screen of output
	 *
	 * @return	void
	 */
	public function clear_screen()
	{
		fwrite(STDOUT, chr(27)."[H".chr(27)."[2J");

		return $this;
	}

	public function getWidth()
	{
		return (int)shell_exec('tput cols');
	}

	//--------------------------------------------------------------------

	public function getHeight()
	{
		return (int)shell_exec('tput lines');
	}

	/**
   * Displays a progress bar on the CLI. You must call it repeatedly
   * to update it. Set $thisStep = false to erase the progress bar.
   *
   * @param int $thisStep
   * @param int $totalSteps
   */
	public function showProgress($thisStep=1, $totalSteps=10)
	{
		// The first time through, save
		// our position so the script knows where to go
		// back to when writing the bar, and
		// at the end of the script.
		if (!$this->inProgress) {
			fwrite(STDOUT, "\0337");
			$this->inProgress = true;
		}

		// Restore position
		fwrite(STDERR, "\0338");

		if ($thisStep !== false) {
			// Don't allow div by zero or negative numbers....
			$thisStep = abs($thisStep);
			$totalSteps = $totalSteps < 1 ? 1 : $totalSteps;

			$percent = intval(($thisStep / $totalSteps) * 100);
			$step = (int)round($percent / 10);

			// Write the progress bar
			fwrite(STDOUT, "[\033[32m" . str_repeat('#', $step) . str_repeat('.', 10 - $step) . "\033[0m]");
			// Textual representation...
			fwrite(STDOUT, " {$percent}% Complete" . PHP_EOL);
			// Move up, undo the PHP_EOL
			fwrite(STDOUT, "\033[1A");
		} else {
			fwrite(STDERR, "\007");
		}
	}

	public function line($length=null, $char='-')
	{
		$length = ($length) ? (int)$length : $this->getWidth();

		return $this->e(str_pad('', $length, $char));
	}
} /* end output class */
