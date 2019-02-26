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
 * Migrates up to the current version
 * migrate/up /packages/example/vendor
 * migrate/latest /packages/example/vendor
 *
 * Roll back changes or step forwards programmatically to specific versions
 * migrate/version /packages/example/vendor 2
 * migrate/down /packages/example/vendor 2
 *
 * Find all migrations and show status
 * migrate/find
 *
 * Create a new migration
 * migrate/create /packages/example/vendor "description with spaces"
 *
 *
 */
class MigrateController extends MY_Controller
{
	protected $version_arg = 1;
	protected $description_arg = 1;
	protected $migration_folder_path = '';
	protected $package_folder_path = '';
	protected $packages = [];
	protected $args = [];
	protected $reverse = [];

	public function __construct()
	{
		parent::__construct();

		$autoload = load_config('autoload', 'autoload');

		$this->packages = $autoload['packages'];

		$this->migration_folder_path = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');
		$this->package_folder_path = $this->get_package();

		ci('package_migration_cli_wrapper')->set_path($this->package_folder_path, $this->migration_folder_path);
	}

	/**
	 * show help
	 */
	public function helpCliAction()
	{
		ci('console')
			->h1('Help')
			->help_command('Display this help.','migrate/help')
			->help_command('Run all migrations found in the application migration folder.',['migrate/up','migrate/latest'])
			->help_command('Run all migrations found in the /packages/misc/orange_snippets migration folder.',['migrate/up /packages/misc/orange_snippets','migrate/latest /packages/misc/orange_snippets'])
			->help_command('Run all migrations down to number 3 in the application migration folder.','migrate/down 3')
			->help_command('Run all migrations down to number 3 in the /packages/misc/orange_snippets migration folder.','migrate/down /packages/misc/orange_snippets 3')
			->help_command(['Migrates up to the current version in the application migration folder*','Whatever is set for $config[\'migration_version\'] in application/config/migration.php.'],'migrate/current')
			->help_command(['Migrates up to the current version in the /packages/misc/orange_snippets migration folder','Whatever is set for $config[\'migration_version@packages/misc/orange_snippets\'] in application/config/migration.php.'],'migrate/current /packages/misc/orange_snippets')
			->help_command('Run all migrations in the application migration folder up or down to 3.','packages/misc/orange_snippets 3')
			->help_command('Run all migrations in the /packages/misc/orange_snippets migration folder up or down to 3.','packages/misc/orange_snippets /packages/misc/orange_snippets 3')
			->help_command('Display all migration found.','migrate/find')
			->help_command('Create an empty migration file in the application migration folder.','migrate/create "This is the migration"')
			->help_command('Create an empty migration file in the /packages/misc/orange_snippets migration folder.','migrate/create /packages/misc/orange_snippets "This is the migration"')
			->help_command('Auto create a navigation migration for a package.','migrate/generate_nav_for /packages/misc/orange_snippets')
			->help_command('Auto create a permission migration for a package.','migrate/generate_permission_for /packages/misc/orange_snippets')
			->br(2);
	}

	/**
	 * Wrapper for migrate/current
	 */
	public function upCliAction()
	{
		ci('package_migration_cli_wrapper')->latest();
	}

	/**
	 * Wrapper for migrate/version/###
	 */
	public function downCliAction()
	{
		ci('package_migration_cli_wrapper')->version((int)ci('console')->get_arg($this->version_arg,true,'version'));
	}

	/* built in functions */

	/**
	 * This works much the same way as current() but instead of looking for the $config['migration_version']
	 * the Migration class will use the very newest migration found in the filesystem.
	 *
	 * https://www.codeigniter.com/user_guide/libraries/migration.html#CI_Migration::latest
	 *
	 * TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function latestCliAction()
	{
		ci('package_migration_cli_wrapper')->latest();
	}

	/**
	 * Migrates up to the current version (whatever is set for $config['migration_version'] in application/config/migration.php).
	 *
	 * https://www.codeigniter.com/user_guide/libraries/migration.html#CI_Migration::current
	 *
	 * TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function currentCliAction()
	{
		$key = 'migration.migration_version@'.trim($this->package_folder_path, '/');

		$version = config($key, false);

		if (!$version) {
			ci('console')->error('Not current configuration found for "'.$key.'".');
		}

		ci('package_migration_cli_wrapper')->current((int)$version);
	}

	/**
	 * Version can be used to roll back changes or step forwards programmatically to specific versions.
	 * It works just like current() but ignores $config['migration_version'].
	 *
	 * https://www.codeigniter.com/user_guide/libraries/migration.html#CI_Migration::version
	 *
	 * TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function versionCliAction()
	{
		ci('package_migration_cli_wrapper')->version((int)ci('console')->get_arg($this->version_arg,true,'version'));
	}

	/**
	 * Return an array of migration filenames that are found in the migration_path property.
	 *
	 * https://www.codeigniter.com/user_guide/libraries/migration.html#CI_Migration::find_migrations
	 */
	public function findCliAction()
	{
		/* application */
		ci('package_migration_cli_wrapper')->find();

		asort($this->packages);

		/* look in each package */
		foreach ($this->packages as $package) {
			ci('package_migration_cli_wrapper')->set_path($package, $this->migration_folder_path)->find();
		}

		ci('console')->hr();
	}

	/**
	 * Builds a standard migration template
	 */
	public function createCliAction()
	{
		$description = ci('console')->get_arg($this->description_arg,true,'description');

		ci('package_migration_cli_wrapper')->create($description);
	}

	/* protected */
	protected function get_package(bool $create = false)
	{
		$path = '';

		$package_path = ci('console')->get_arg(1,false,null,false);

		if ($package_path !== false) {
			$package_path = trim($package_path,'/');

			/* strip off the migration folder incase they added that to the end of the path */
			if (substr($package_path, -strlen($this->migration_folder_path)) == $this->migration_folder_path) {
				$package_path = substr($package_path, 0, -strlen($this->migration_folder_path));
			}

			/* ok does this package path exist? */
			if (file_exists(ROOTPATH.'/'.$package_path)) {
				$this->create_folder(ROOTPATH.'/'.$package_path.$this->migration_folder_path);

				/* does the migration folder exist in this package? */
				if (file_exists(ROOTPATH.'/'.$package_path.$this->migration_folder_path)) {
					/* everything seems good that this is a package therefore let's set path to the package path */
					$path = $package_path;

					/* because we are in a package every command line option advances by 1 */
					$this->version_arg++;
					$this->description_arg++;
				}
			}
		}

		return $path;
	}

	/**
	 * Create Navigation Menu(s) Migration for a package
	 */
	public function generate_nav_forCliAction()
	{
		$search_package = trim(ci('console')->get_arg(1,true,'dynamic controller url'),'/');

		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if ($search_package == trim($record['package'],'/')) {
				ci('orange_inspector_collector')->inspect($record['controller']);
			}
		}

		$details = ci('orange_inspector_collector')->details();

		if (count($details) == 0) {
			ci('console')->error('No controllers found in "'.$search_package.'".');
		}

		$groups = [];

		/* now for each of the controllers we need to create a migration */
		foreach ($details as $controller_class) {
			$single_group = [];

			foreach ($controller_class['methods'] as $method) {
				if ($method['public'] && substr($method['name'],-6) == 'Action') {
					$pieces = preg_split('/(?=[A-Z])/',$method['name']);
					$action = array_shift($pieces);

					/* grab the http request method it would be the second if it's there */
					$request_type = strtolower(array_shift($pieces));

					if (!in_array($request_type,['post','put','delete','cli'])) {
						$action = ($action == 'index') ? '' : $action;
						$controller_position = strpos($controller_class['file path'],'/controllers/');
						$url = substr($controller_class['file path'],$controller_position + 12,-14);
						$url = str_replace('_','-','/'.strtolower(trim($url.'/'.$action,'/')));
						$text = trim(ucwords(strtolower(str_replace(['/','_','-'], ' ', $url))), ' ');

						$single_group[$url] = "ci('o_nav_model')->migration_add('".$url."','".$text."',\$this->get_hash());";
					}
				}

				ksort($single_group);

				$groups[$controller_class['name']] = $single_group;
			}
		}

		$this->build_migration_for('/* Browser URL, Menu Text, Migration Hash */',$groups,'navigation','ci(\'o_nav_model\')->migration_remove($this->hash());');
	} /* end function */

	/**
	 * Create Permission(s) Migration for a package
	 */
	public function generate_permission_forCliAction()
	{
		$search_package = trim(ci('console')->get_arg(1,true,'dynamic controller url'),'/');

		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if ($search_package == trim($record['package'],'/')) {
				ci('orange_inspector_collector')->inspect($record['controller']);
			}
		}

		$details = ci('orange_inspector_collector')->details();

		if (count($details) == 0) {
			ci('console')->error('No controllers found in "'.$search_package.'".');
		}

		$groups = [];

		/* now for each of the controllers we need to create a migration */
		foreach ($details as $controller_class) {
			$single_group = [];

			foreach ($controller_class['methods'] as $method) {
				if ($method['public'] && substr($method['name'],-6) == 'Action') {
					$pieces = preg_split('/(?=[A-Z])/',$method['name']);
					$count_pieces = count($pieces);

					if ($count_pieces == 3) {
						/* foobarPostAction */
						$url_piece = $pieces[0];
						$request_type = strtolower($pieces[1]);
						$action = $pieces[2];
					} elseif($count_pieces == 2) {
						/* foobarAction */
						$url_piece = $pieces[0];
						$request_type = 'get';
						$action = $pieces[2];
					} else {
						ci('console')->error('spliting method "'.$method['name'].'" failed must have 2 or 3 sections seperated by uppercase letters.');
					}

					if (!in_array($request_type,['cli'])) {
						$controller_position = strpos($controller_class['file path'],'/controllers/');
						$url = substr($controller_class['file path'],$controller_position + 12,-14);
						$group = filter('human',$url);
						$key = strtolower('url::'.$url.'::'.$url_piece.'~'.$request_type);
						$description = filter('human',$url.' '.$url_piece.' '.$request_type);

						$single_group[] = "ci('o_permission_model')->migration_add('".$key."','".$group."','".$description."',\$this->get_hash());";
					}
				}

				ksort($single_group);

				$groups[$controller_class['name']] = $single_group;
			}
		}

		$this->build_migration_for('/* Orange URL Key, Permission Tab, Permission Text, Migration Hash */',$groups,'permission','ci(\'o_permission_model\')->migration_remove($this->hash());');
	}

	protected function build_migration_for(string $comment,array $groups,string $file_prefix,string $down)
	{
		$up = $comment.PHP_EOL;

		foreach ($groups as $group) {
			foreach ($group as $s) {
				$up .= chr(9).chr(9).$s.PHP_EOL;
			}
			$up .= PHP_EOL;
		}

		ci('package_migration_cli_wrapper')->create($file_prefix.'_'.date('Y_m_d'), trim($up), trim($down));
	}

	protected function show_migrations_available($text, $groups)
	{
		krsort($groups);

		ci('console')->error($text,false)->hr();

		foreach ($groups as $url=>$source) {
			ci('console')->out('/'.$url);
		}

		ci('console')->br();

		exit(1);
	}

	protected function create_folder(string $folder,int $mode=0777) : bool
	{
		$success = true;

		if (!file_exists($folder)) {
			ci('console')->info('Creating folder "'.$folder.'".');

			$paths = explode('/', $folder);

			$dir = '';

			foreach ($paths as $folder) {
				$dir .= $folder.'/';

				if (!is_dir($dir) && strlen($dir) > 0) {
					if (!$success = mkdir($dir, $mode)) {
						return $success;
					}
				}
			}
		}

		return $success;
	}


} /* end class */
