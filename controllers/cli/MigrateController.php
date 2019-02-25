<?php
/**
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
		$this->build_migration_for('/* Browser URL, Menu Text, Migration Hash */',$this->build_migrations_nav_groups(),'navigation');
	}

	/**
	 * Create Permission(s) Migration for a package
	 */
	public function generate_permission_forCliAction()
	{
		$this->build_migration_for('/* Orange URL Key, Permission Tab, Permission Text, Migration Hash */',$this->build_migrations_permission_groups(),'permission');
	}

	protected function build_migration_for(string $comment,array $groups,string $file_prefix)
	{
		$source_for = str_replace('-', '_', trim(ci('console')->get_arg(1,true,'dynamic controller url'), '/'));

		if (!isset($groups[$source_for])) {
			$this->show_migrations_available('No dynamic controller urls found at "'.$source_for.'"', $groups);
		}

		$source = $comment.PHP_EOL;

		foreach ($groups[$source_for] as $s) {
			$source .= chr(9).chr(9).$s.PHP_EOL;
		}

		$package = $this->reverse[$source_for];

		ci('package_migration_cli_wrapper')->set_path($package,'/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/'))->create($file_prefix.'_'.date('Y_m_d'), $source, 'ci(\'o_nav_model\')->migration_remove($this->hash());');
	}

	protected function build_migrations_permission_groups()
	{
		$inspection = (new Fruit_inspector)->get_controllers_methods();

		$groups = [];

		foreach ($inspection as $package) {
			foreach ($package as $controller=>$details) {
				$controller = $details['controller'];
				$this->reverse[trim($controller['url'], '/')] = $details['controller']['package'];
				foreach ($details['methods'] as $method) {
					if ($method['request_method'] != 'cli') {
						$group = filter('human', $controller['url']);
						$key = 'url::'.$controller['url'].'::'.$method['action'].'~'.$method['request_method'];
						$group = filter('human', $controller['url']);
						$description = filter('human', $controller['url'].' '.$method['action'].' '.$method['request_method']);

						$groups[trim($controller['url'], '/')][] = "ci('o_permission_model')->migration_add('".$key."','".$group."','".$description."',\$this->get_hash());";
					}
				}
			}
		}

		return $groups;
	}

	protected function build_migrations_nav_groups()
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

		return $success;
	}


} /* end class */
