<?php
/**
 *
 * Migrates up to the current version
 * php public/index.php cli/migrate/up /packages/example/vendor
 * php public/index.php cli/migrate/latest /packages/example/vendor
 *
 * Roll back changes or step forwards programmatically to specific versions
 * php public/index.php cli/migrate/version /packages/example/vendor 2
 * php public/index.php cli/migrate/down /packages/example/vendor 2
 *
 * Find all migrations and show status
 * php public/index.php cli/migrate/find
 *
 * Create a new migration
 * php public/index.php cli/migrate/create /packages/example/vendor "description with spaces"
 *
 *
 */
class MigrateController extends MY_Controller
{
	protected $version_arg = 1;
	protected $description_arg = 1;
	protected $folder_arg = 1;
	protected $migration_folder_path = '';
	protected $package_folder_path = '';
	protected $console;
	protected $packages = [];
	protected $args = [];

	public function __construct()
	{
		parent::__construct();

		$this->args = $_SERVER['argv'];

		$this->console = new League\CLImate\CLImate;

		$this->package_folder_path = $this->get_package();
		$this->migration_folder_path = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');

		$autoload = load_config('autoload', 'autoload');

		$this->packages = $autoload['packages'];

		ci('package_migration_cli_wrapper')->set_path($this->package_folder_path, $this->migration_folder_path);
	}

	/**
	 * show help
	 */
	public function indexCliAction()
	{
		$this->helpCliAction();
	}

	/**
	 * show help
	 */
	public function helpCliAction()
	{
		$this->console->out('php public/index.php cli/migrate/help');
		$this->console->tab()->info('Display this help')->br();

		$this->console->out('php public/index.php cli/migrate/up');
		$this->console->out('php public/index.php cli/migrate/latest');
		$this->console->tab()->info('Run all migrations found in the application migration folder.')->br();

		$this->console->out('php public/index.php cli/migrate/up /packages/misc/orange_snippets');
		$this->console->out('php public/index.php cli/migrate/latest /packages/misc/orange_snippets');
		$this->console->tab()->info('Run all migrations found in the /packages/misc/orange_snippets migration folder.')->br();

		$this->console->out('php public/index.php cli/migrate/down 3');
		$this->console->tab()->info('Run all migrations down to number 3 in the application migration folder.')->br();

		$this->console->out('php public/index.php cli/migrate/down /packages/misc/orange_snippets 3');
		$this->console->tab()->info('Run all migrations down to number 3 in the /packages/misc/orange_snippets migration folder.')->br();

		$this->console->out('php public/index.php cli/migrate/current');
		$this->console->tab()->info('Migrates up to the current version in the application migration folder*');
		$this->console->tab()->info('Whatever is set for $config[\'migration_version\'] in application/config/migration.php.')->br();

		$this->console->out('php public/index.php cli/migrate/current /packages/misc/orange_snippets');
		$this->console->tab()->info('Migrates up to the current version in the /packages/misc/orange_snippets migration folder');
		$this->console->tab()->info('Whatever is set for $config[\'migration_version@package folder\'] in application/config/migration.php.')->br();


		$this->console->out('php public/index.php cli/migrate/version 3');
		$this->console->tab()->info('Run all migrations in the application migration folder up or down to 3.')->br();

		$this->console->out('php public/index.php cli/migrate/version /packages/misc/orange_snippets 3');
		$this->console->tab()->info('Run all migrations in the /packages/misc/orange_snippets migration folder up or down to 3.')->br();

		$this->console->out('php public/index.php cli/migrate/find');
		$this->console->tab()->info('Display all migration found.')->br();

		$this->console->out('php public/index.php cli/migrate/create "This is the migration"');
		$this->console->tab()->info('Create an empty migration file in the application migration folder.')->br();

		$this->console->out('php public/index.php cli/migrate/version /packages/misc/orange_snippets "This is the migration"');
		$this->console->tab()->info('Create an empty migration file in the /packages/misc/orange_snippets migration folder.')->br();
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
		ci('package_migration_cli_wrapper')->version((int)$this->get_section($this->version_arg, 'version'));
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
			$this->console->error('Not current configuration found for "'.$key.'".');
			exit(1);
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
		ci('package_migration_cli_wrapper')->version((int)$this->get_section($this->version_arg, 'version'));
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

		/* look in each package */
		foreach ($this->packages as $package) {
			ci('package_migration_cli_wrapper')->set_path($package, $this->migration_folder_path)->find();
		}

		$this->console->border('-', (int)exec('tput cols'));
	}

	/**
	 * Builds a standard migration template
	 */
	public function createCliAction()
	{
		ci('package_migration_cli_wrapper')->create($this->get_section($this->description_arg, 'description'));
	}

	/* protected */
	protected function get_package()
	{
		$path = '';

		/* did they include anything? */
		$raw_folder = $this->get_section($this->folder_arg, 'package folder', false);

		/* is arg1 a folder */
		if (strpos($raw_folder, '/') !== false) {
			/* yes it's a package folder - let's verify it */
			$this->version_arg++;
			$this->description_arg++;

			/* verify it's a valid package */
			$path = '/'.trim($raw_folder, '/');

			if (substr($path, -strlen($this->migration_folder_path)) == $this->migration_folder_path) {
				$path = substr($path, 0, -strlen($this->migration_folder_path));
			}

			if (!file_exists(ROOTPATH.$folder)) {
				$this->console->error('"'.$path.'" does not seem to be a valid package path.');
				exit(1);
			}

			if (!file_exists(ROOTPATH.$folder.$this->migration_folder_path)) {
				mkdir(ROOTPATH.$folder.$this->migration_folder_path, 0777, true);

				if (!file_exists(ROOTPATH.$folder.$this->migration_folder_path)) {
					$this->console->error('"'.$folder.$this->migration_folder_path.'" does not seem to be a valid package migration path.');
					exit(1);
				}
			}
		}

		return $path;
	}

	protected function get_section($num, $text, $required=true)
	{
		/* the first useable arg is 2 */
		$num = $num + 1;

		if ($required) {
			if (trim($this->args[$num]) == '') {
				$this->console->error('Please provide a '.$text.'.');
				exit();
			}
		}

		return $this->args[$num];
	}
} /* end class */
