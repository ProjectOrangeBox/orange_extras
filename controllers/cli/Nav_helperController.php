<?php

class Nav_helperController extends MY_Controller
{

	/**
	 *
	 * Generate the Migration PHP for adding all found get http requests
	 *
	 */
	public function indexCliAction()
	{
	} /* so help shows up */

	public function _remap($method)
	{
		if ($method == 'migrationCliAction') {
			$this->migrationCliAction();
			exit(1);
		}

		$console = new League\CLImate\CLImate;
		$source_for = str_replace('-', '_', trim($_SERVER['argv'][2], '/'));

		$groups = $this->build_groups();

		if (!isset($_SERVER['argv'][2])) {
			$this->show_available('Please enter a dynamic controller url.', $groups, $console);
		}

		if (isset($groups[$source_for])) {
			$console->br()->info('/* Browser URL, Menu Text, Migration Hash */');

			foreach ($groups[$source_for] as $s) {
				$console->white($s);
			}

			$console->br();
		} else {
			$this->show_available('No dynamic controller urls found at "'.$source_for.'"', $groups, $console);
		}
	}

	/**
	 *
	 * Generate the actual Migration file for adding all found get http requests for the given package
	 * php public/index.php cli/nav_helper/migration /admin/products
	 *
	 */
	public function migrationCliAction()
	{
		$console = new League\CLImate\CLImate;
		$source_for = str_replace('-', '_', trim($_SERVER['argv'][2], '/'));

		$groups = $this->build_groups();

		if (!isset($_SERVER['argv'][2])) {
			$this->show_available('Please enter a dynamic controller url.', $groups, $console);
		}

		if (isset($groups[$source_for])) {
			$source = '/* Browser URL, Menu Text, Migration Hash */'.PHP_EOL;

			foreach ($groups[$source_for] as $s) {
				$source .= $s.PHP_EOL;
			}

			$migration_folder_path = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');
	
			$package = $this->reverse[$source_for];
		
			ci('package_migration_cli_wrapper')->set_path($package, $migration_folder_path)->create('nav_helper_'.time('ymd'), $source, 'ci(\'o_nav_model\')->migration_remove($this->hash());');
		} else {
			$this->show_available('No dynamic controller urls found at "'.$source_for.'"', $groups, $console);
		}
	}

	protected function build_groups()
	{
		$inspection = (new Fruit_inspector)->get_controllers_methods();
		$groups = [];

		foreach ($inspection as $package) {
			foreach ($package as $controller=>$details) {
				$controller = $details['controller'];
				$this->reverse[trim($controller['url'], '/')] = $details['controller']['package'];
				foreach ($details['methods'] as $method) {
					if ($method['request_method'] == 'get') {
						$action = ($method['action'] == 'index') ? '' : $method['action'];
						$url = str_replace('_', '-', '/'.strtolower(trim($controller['url'].'/'.$action, '/')));
						$text = trim(ucwords(strtolower(str_replace(['/','_','-'], ' ', $url))), ' ');


						$groups[trim($controller['url'], '/')][] = "ci('o_nav_model')->migration_add('".$url."','".$text."',\$this->get_hash());";
					}
				}
			}
		}

		return $groups;
	}

	protected function show_available($text, $groups, $console)
	{
		$console->br()->error($text)->border('-', (int)exec('tput cols'));

		foreach ($groups as $url=>$source) {
			$console->out('/'.$url);
		}

		$console->br();
		exit(1);
	}
} /* end controller */
