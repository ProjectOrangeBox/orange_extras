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

class Deploy_helperController extends MY_Controller
{
	/**
	 * Generate the Deploy JSON for adding all found GIT Repositories
	 */
	public function helpCliAction()
	{
		ci('console')->help([
			['Generate gitx status deploy.json syntax for all loaded packages'=>'deploy_helper/gitx_status'],
			['Generate gitx update deploy.json syntax for all loaded packages'=>'deploy_helper/gitx_update'],
			['Generate gitx checkout deploy.json syntax for all loaded packages'=>'deploy_helper/gitx_checkout'],
			['Generate migrate up deploy.json syntax for all loaded packages'=>'deploy_helper/migrate_up'],
		]);
	}

	public function gitx_statusCliAction()
	{
		/* "gitx update {PWD}/packages/projectorangebox/extra-validations {GITBRANCH}" */
		$this->inspect(function($folder) {
			ci('console')->out('"gitx status {PWD}'.$folder.'",');
		});
	}

	public function gitx_updateCliAction()
	{
		/* "gitx update {PWD}/packages/projectorangebox/extra-validations {GITBRANCH}" */
		$this->inspect(function($folder) {
			ci('console')->out('"gitx update {PWD}'.$folder.'",');
		});
	}

	public function gitx_checkoutCliAction()
	{
		/* gitx checkout https://github.com/ProjectOrangeBox/Orange_v2_cli.git {PWD}/packages/projectorangebox/migrations {GITBRANCH} */
		$this->inspect(function($folder,$package) {
			if ($remote = $this->get_remote($package)) {
				/* gitx checkout https://github.com/ProjectOrangeBox/Orange_v2_cli.git {PWD}/packages/projectorangebox/migrations {GITBRANCH} */
				ci('console')->out('"gitx checkout '.$remote.' {PWD}'.$folder.' {GITBRANCH}",');
			}
		});
	}

	public function migrate_upCliAction()
	{
		/* "cd {PWD}/public;php index.php cli/migrate/up packages/projectorangebox/scaffolding" 	*/
		$this->inspect(function($folder) {
			ci('console')->out('"cd {PWD};php public/index.php cli/migrate/up'.$folder.'",');
		});
	}

	protected function inspect($callback)
	{
		ci('console')->h1('Deploy Syntax - copy and paste as needed.');

		foreach (get_packages('root',null,true) as $idx=>$package) {
			$callback('/'.trim(str_replace(ROOTPATH, '', $package), '/'),$package);
		}
	}

	protected function get_remote($package)
	{
		$package = (empty($package)) ? ROOTPATH : $package;

		if (file_exists($package.'/.git')) {
			$lines = file($package.'/.git/config');

			foreach ($lines as $idx=>$line) {
				$line = trim($line);

				if (substr($line, 0, 9) == '[remote "' && substr($line, -2) == '"]') {
					$parts = explode(' ', trim($lines[$idx+1]));

					return $parts[2];
				}
			}
		}

		return false;
	}
}
