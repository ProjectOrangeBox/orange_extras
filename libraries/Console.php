<?php

class Console
{
	protected $climate;
	protected $command_prefix = '<white>php public/index.php</white> cli/';

	public function __construct()
	{
		$this->climate = new \League\CLImate\CLImate;
	}

	public function __call($method,$arguments)
	{
		return call_user_func_array([$this->climate,$method], $arguments);
	}

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

	public function br(int $count=1) : Console
	{
		for ($i = 0; $i < $count; $i++) {
			$this->climate->br();
		}

		return $this;
	}

	public function hr() : Console
	{
		$this->climate->border('-', (int)exec('tput cols'));

		return $this;
	}

	public function h1(string $txt) : Console
	{
		$this->climate->cyan()->bold()->underline()->out($txt);

		return $this;
	}

	public function h2(string $txt) : Console
	{
		$this->climate->cyan()->bold()->out($txt);

		return $this;
	}

	public function command(string $txt)
	{
		$this->climate->yellow()->tab()->out($this->command_prefix.$txt);

		return $this;
	}

	public function text(string $txt) : Console
	{
		$this->climate->white()->out($txt);

		return $this;
	}

	public function error(string $txt,bool $die = true) : Console
	{
		$this->climate->red()->bold()->out($txt);

		if ($die) {
			exit(1);
		}

		return $this;
	}

} /* end class */
