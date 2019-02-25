<?php

class HelpController extends MY_Controller
{
	/**
	 * Show all of the available Command Line Functions
	 */
	public function indexCliAction()
	{
		$inspection = (new Fruit_inspector)->get_controllers_methods();

		$output = [];

		foreach ($inspection as $package) {
			foreach ($package as $controller=>$details) {
				$controller = $details['controller'];

				foreach ($details['methods'] as $method) {
					if ($method['request_method'] == 'cli' && $method['parent'] != 'MY_Controller') {
						$controller_path = substr(ltrim($controller['url'],'/'),4);
						$action = ($method['action'] != 'index') ? '/'.$method['action'] : '';

						if (strlen($method['comments'])) {
							$lines = explode(PHP_EOL, trim($method['comments']));
							$help = [];

							foreach ($lines as $l) {
								$clean = ltrim($l, ' */\\'.chr(9).chr(10).chr(13));

								if (!empty($clean)) {
									$help[] = $clean;
								}
							}

							$output[$controller_path.$action] = $help;
						}
					}
				}
			}
		}

		ksort($output);

		ci('console')->h1('Help');

		foreach ($output as $controller=>$comment) {
			ci('console')->help_command($comment,$controller);
		}

		ci('console')->br()->white('** if you have spark installed you can just type "spark '.$controller.'".');

		ci('console')->br(2);
	}

	/**
	 * Test all database connections (no query's run)
	 */
	public function test_databasesCliAction()
	{
		$db = load_config('database', 'db');

		foreach ($db as $name=>$values) {
			ci('console')->info($name);

			$header = $line = '';

			foreach (['dsn'=>32,'hostname'=>20,'username'=>24,'password'=>24,'database'=>24] as $key=>$padding) {
				$header .= str_pad($key,$padding);
				$line .= str_pad($values[$key],$padding);
			}

			ci('console')->h1($header)->h2($line);

			try {
				$this->load->database($name, true);
				ci('console')->info('* Success')->br(2);
			} catch (Exception $e) {
				ci('console')->error('* Failed',false)->hr();
			}
		}
	}

	/**
	 * Display current .env and .env.local as well as merged results
	 */
	public function envCliAction()
	{
		$env = (file_exists('.env')) ? parse_ini_file('.env', true, INI_SCANNER_TYPED) : [];

		ci('console')->h1('.env');

		$this->_env_loop($env);

		$env_local = (file_exists('.env.local')) ? parse_ini_file('.env.local', true, INI_SCANNER_TYPED) : [];

		ci('console')->h1('.env.local');

		$this->_env_loop($env_local);

		ci('console')->h1('Merged');

		$merged = array_merge($_ENV, $env, $env_local);

		$this->_env_loop($merged);
	}

	protected function _env_loop($env)
	{
		foreach ($env as $label=>$result) {
			if (is_array($result)) {
				ci('console')->info($label);

				foreach ($result as $l=>$r) {
					ci('console')->out(' '.str_pad($l,41).' '.$r);
				}
			} else {
				ci('console')->out(str_pad($label,42).' '.$result);
			}
		}

		ci('console')->br(2);
	}
} /* end class */
