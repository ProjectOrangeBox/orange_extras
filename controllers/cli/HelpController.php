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
		$packages = get_packages(null,null,true);
		
		foreach ($packages as $package) {
			$cli_folder = $package.'/controllers/cli';
			
			if (file_exists($cli_folder)) {
				$matches = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cli_folder,FilesystemIterator::SKIP_DOTS));

				foreach ($matches as $match) {
					$pathname = $match->getPathname();
				
					$controller_position = strpos($pathname,'/controllers/');
					$url = strtolower(substr($pathname,$controller_position + 13,-14));
					
					ci('console')->br()->h2('Controller: cli/'.substr($url,4))->br();
					
					$exit = $this->shell('php '.ROOTPATH.'/public/index.php '.$url.'/help',$stdout,$stderr);
					
					echo $stdout;
				}
			}
		}

		ci('console')->br()->white('** if you have spark installed you can just type "spark '.$uri.'".');
	}

	public function helpCliAction()
	{
		ci('console')->help([
			['Show every cli controllers help.'=>'help'],
			['Display this help.'=>'help/help'],
			['Test add database connections'=>'help/test-databases'],
			['Show details about .env files.'=>'help/env'],
		],false);
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

	protected function shell($cmd, &$stdout=null, &$stderr=null) {
		$cols = (int)exec('tput cols');
	
		$proc = proc_open($cmd,[1=>['pipe','w'],	2=>['pipe','w']],$pipes,null,['CLICOLOR'=>1,'TERM'=>'xterm','TERM_PROGRAM'=>'Hyper','TERM_COLUMNS'=>$cols]);
	
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
	
		return proc_close($proc);
	}	
} /* end class */
