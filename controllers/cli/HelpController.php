<?php

class HelpController extends MY_Controller
{

	/**
	 * Show all of the available Command Line Functions
	 */
	public function indexCliAction()
	{
		$console = new League\CLImate\CLImate;

		$inspection = (new Fruit_inspector)->get_controllers_methods();

		$output = [];

		foreach ($inspection as $package) {
			foreach ($package as $controller=>$details) {
				$controller = $details['controller'];

				foreach ($details['methods'] as $method) {
					if ($method['request_method'] == 'cli' && $method['parent'] != 'MY_Controller') {
						$action = ($method['action'] != 'index') ? '/'.$method['action'] : '';
	
						if (strlen($method['comments'])) {
							$lines = explode(PHP_EOL, trim($method['comments']));
							$comments = '';
							
							foreach ($lines as $l) {
								$clean = ltrim($l, ' */\\'.chr(9).chr(10).chr(13));
								
								if (!empty($clean)) {
									$comments .= chr(9).$clean.PHP_EOL;
								}
							}
							
							$output[$controller['url'].$action] = $comments;
						}
					}
				}
			}
		}

		ksort($output);

		foreach ($output as $controller=>$comment) {
			$console->border('-', (int)exec('tput cols'))->info($controller)->out($comment);
		}
	}
	
	/**
	 * Test all database connections (no query's run)
	 */
	public function test_databasesCliAction()
	{
		$console = new League\CLImate\CLImate;

		$padding = $console->padding(16)->char('.');

		$db = load_config('database', 'db');

		foreach ($db as $name=>$values) {
			$console->info($name);
			
			foreach (['dsn','hostname','username','password','database'] as $key) {
				$padding->label($key)->result($values[$key]);
			}
			
			try {
				$this->load->database($name, true);
				$console->info('* Success')->border();
			} catch (Exception $e) {
				$console->error('* Failed')->border();
			}
		}
	}
	
	/**
	 * Display current .env and .env.local as well as merged results
	 */
	public function envCliAction()
	{
		$console = new League\CLImate\CLImate;

		$padding = $console->padding(32)->char('.');

		$env = (file_exists('.env')) ? parse_ini_file('.env', true, INI_SCANNER_TYPED) : [];

		$console->border()->info('.env');

		$this->_env_loop($console, $padding, $env);

		$env_local = (file_exists('.env.local')) ? parse_ini_file('.env.local', true, INI_SCANNER_TYPED) : [];

		$console->info('.env.local');

		$this->_env_loop($console, $padding, $env_local);

		$console->info('Merged');

		$merged = array_merge($_ENV, $env, $env_local);
			
		$this->_env_loop($console, $padding, $merged);
	}
	
	protected function _env_loop(&$console, &$padding, $env)
	{
		foreach ($env as $label=>$result) {
			if (is_array($result)) {
				$padding->label($label)->result($result);
				
				foreach ($result as $l=>$r) {
					$padding->label('  '.$l)->result($r);
				}
			} else {
				$padding->label($label)->result($result);
			}
		}
	
		$console->border();
	}
} /* end class */
