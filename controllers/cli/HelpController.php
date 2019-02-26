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
 * _ class.
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0.0
 * @filesource
 *
 */

class HelpController extends MY_Controller
{
	/**
	 * Show all of the available Command Line Functions
	 */
	public function indexCliAction()
	{
		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if (substr($name,0,4) == 'cli/') {
				ci('orange_inspector_collector')->inspect($record['controller']);
			}
		}

		$controllers = [];

		foreach (ci('orange_inspector_collector')->details() as $class) {
			foreach ($class['methods'] as $method) {
				if (substr($method['name'],-9) == 'CliAction') {
					$controllers[$method['class'].'::'.$method['name']] = [
						'class'=>$method['class'],
						'name'=>$method['name'],
						'doc comment'=>$method['doc comment'],
						'documentation'=>$method['documentation'],
					];
				}
			}
		}

		ksort($controllers);

		ci('console')->h1('Help');

		foreach ($controllers as $controller=>$record) {
			if (!empty($record['documentation'])) {
				$uri = strtolower(substr($record['class'],0,-10).'/'.substr($record['name'],0,-9));

				ci('console')->help_command($record['documentation'],$uri);
			}
		}

		ci('console')->br()->white('** if you have spark installed you can just type "spark '.$uri.'".');

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
