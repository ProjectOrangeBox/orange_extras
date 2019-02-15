<?php

class Deploy_helperController extends MY_Controller
{
	/**
	 * Generate the Deploy JSON for adding all found GIT Repositories
	 */
	public function indexCliAction()
	{
		$console = new League\CLImate\CLImate;

		$console->blue()->out('Inspecting Packages');

		$autoload = load_config('autoload', 'autoload');
		$packages = $autoload['packages'];

		/* this adds root application folder */
		$packages = array_merge([''], $packages);

		$progress = $console->progress()->total(count($packages));

		sort($packages);

		foreach ($packages as $idx=>$package) {
			$progress->current($idx+1);

			$folder = '/'.trim(str_replace(ROOTPATH, '', $package), '/');

			/* "gitx update {PWD}/packages/projectorangebox/extra-validations {GITBRANCH}" */
			$git_update[] = '"gitx update {PWD}'.$folder.'"';

			/* "gitx update {PWD}/packages/projectorangebox/extra-validations {GITBRANCH}" */
			$git_status[] = '"gitx status {PWD}'.$folder.'"';

			/* "cd {PWD}/public;php index.php cli/migrate/up packages/projectorangebox/scaffolding" 	*/
			$migrations[] = '"cd {PWD};php public/index.php cli/migrate/up'.$folder.'"';

			if ($remote = $this->get_remote($package)) {
				/* gitx checkout https://github.com/ProjectOrangeBox/Orange_v2_cli.git {PWD}/packages/projectorangebox/migrations {GITBRANCH} */
				$checkout[] = '"gitx checkout '.$remote.' {PWD}'.$folder.' {GITBRANCH}"';
			}
		}

		$console->br()->blue()->out('Deploy commands - copy and paste as needed.');

		if (is_array($git_update)) {
			$console->br()->out(implode(','.PHP_EOL, $git_update));
		}

		if (is_array($git_status)) {
			$console->br()->out(implode(','.PHP_EOL, $git_status));
		}

		if (is_array($checkout)) {
			$console->br()->out(implode(','.PHP_EOL, $checkout));
		}

		if (is_array($migrations)) {
			$console->br()->out(implode(','.PHP_EOL, $migrations));
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
