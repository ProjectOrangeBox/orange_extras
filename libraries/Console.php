<?php

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
	protected $command_prefix = '<white>php public/index.php</white> cli/';

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
		$this->climate->red()->bold()->out($txt);

		if ($die) {
			exit(1);
		}

		return $this;
	}

} /* end class */
