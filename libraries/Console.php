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
	 * $command_prefix
	 *
	 * @var string
	 */
	protected $command_prefix = '/cli/';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->climate = new \League\CLImate\CLImate;
	}

	/**
	 * __call
	 *
	 * @param mixed $method
	 * @param mixed $arguments
	 * @return void
	 */
	public function __call($method,$arguments)
	{
		return call_user_func_array([$this->climate,$method], $arguments);
	}

	/**
	 * help_command
	 *
	 * @param mixed $help
	 * @param mixed $command
	 * @return void
	 */
	public function help_command($help,$command) : Console
	{
		foreach ((array)$help as $txt) {
			$this->climate->info($txt);
		}

		foreach ((array)$command as $txt) {
			ci('console')->command($txt);
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
		$this->climate->border('-', (int)exec('tput cols'));

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
	public function command(string $txt)
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
	public function error(string $txt,bool $die = true) : Console
	{
		$this->climate->red()->bold()->out('⛔ '.$txt);

		if ($die) {
			$this->climate->br();
			exit(1);
		}

		return $this;
	}

	public function get_arg(int $number,bool $required = false,string $text = null,$default = null)
	{
		/* the first useable arg is 2 */
		$number = $number + 1;

		$arg = (!isset($_SERVER['argv'][$number])) ? $default : $_SERVER['argv'][$number];

		if ($required && empty($arg)) {
			$err = ($text) ? 'a '.$text.' (argument '.($number - 1).')' : 'argument '.($number - 1);

			ci('console')->br()->error('Please provide '.$err.'.');
		}

		return $arg;
	}

} /* end class */
