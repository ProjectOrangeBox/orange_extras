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
class MigrateController extends \MY_Controller
{
	/**
	 * Store the configuration key which contains the current version number
	 * either
	 *
	 * migration.migration_version
	 *
	 * for the application "root" migrations folder
	 *
	 * - or -
	 *
	 * migration.migration_version@packages/misc/masquerade
	 *
	 * for a package specific version
	 *
	 * @var string
	 */
	protected $migration_key = '';

	/**
	 * The captured version number
	 * if boolean false no version number captured
	 *
	 * @var bool | integer
	 */
	protected $version = false;

	/**
	 * show help
	 */
	public function helpCliAction() : void
	{
		ci('console')->help([
			['Run all migrations found in the application migration folder.'=>['migrate/up','migrate/latest']],
			['Run all migrations found in the /packages/misc/orange_snippets migration folder.'=>['migrate/up /packages/misc/orange_snippets','migrate/latest /packages/misc/orange_snippets']],
			['Run all migrations down to number 3 in the application migration folder.'=>'migrate/down 3'],
			['Run all migrations down to number 3 in the /packages/misc/orange_snippets migration folder.'=>'migrate/down /packages/misc/orange_snippets 3'],
			['Migrates up to the current version in the application migration folder'],
			['Whatever is set for $config[\'migration_version\'] in application/config/migration.php.'=>'migrate/current'],
			['Migrates up to the current version in the /packages/misc/orange_snippets migration folder'],
			['Whatever is set for $config[\'migration_version@packages/misc/orange_snippets\'] in application/config/migration.php.'=>'migrate/current /packages/misc/orange_snippets'],
			['Run all migrations in the application migration folder up or down to 3.'=>'migration/version 3'],
			['Run all migrations in the /packages/misc/orange_snippets migration folder up or down to 3.'=>'migration/version /packages/misc/orange_snippets 3'],
			['Display all migration found.'=>'migrate/find'],
		]);
	}

	/**
	 * Wrapper for migrate/current
	 */
	public function upCliAction() : void
	{
		$this->parse_arguments();

		ci('package_migration_cli_wrapper')->latest();
	}

	/**
	 * Wrapper for migrate/version/###
	 */
	public function downCliAction() : void
	{
		$this->parse_arguments(true);

		ci('package_migration_cli_wrapper')->version($this->version);
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
	public function latestCliAction() : void
	{
		$this->parse_arguments();

		ci('package_migration_cli_wrapper')->latest();
	}

	/**
	 * Migrates up to the current version (whatever is set for $config['migration_version'] in application/config/migration.php).
	 *
	 * https://www.codeigniter.com/user_guide/libraries/migration.html#CI_Migration::current
	 *
	 * TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function currentCliAction() : void
	{
		$this->parse_arguments();

		if (!$version = config($this->migration_key, false)) {
			ci('console')->error('Not current configuration found for "'.$this->migration_key.'".');
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
	public function versionCliAction() : void
	{
		$this->parse_arguments(true);

		ci('package_migration_cli_wrapper')->version($this->version);
	}

	/**
	 * Return an array of migration filenames that are found in the migration_path property.
	 *
	 * https://www.codeigniter.com/user_guide/libraries/migration.html#CI_Migration::find_migrations
	 */
	public function findCliAction() : void
	{
		$migration_folder = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');

		foreach (get_packages('app',null,true) as $package) {
			ci('package_migration_cli_wrapper')->set_path($package,$migration_folder)->find();
		}

		ci('console')->hr();
	}

	/**
	 *
	 * Parse arguments
	 * either:
	 * package
	 * package version
	 * version
	 *
	 * @access protected
	 *
	 * @param bool $version_required false
	 *
	 * @return void
	 *
	 */
	protected function parse_arguments(bool $version_required = false) : void
	{
		/* determine what the migration folder is inside a package */
		$migration_folder = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');

		$version_position = 0;

		/* get the first argument */
		$arg1 = ci('console')->get_arg(1,false);

		/* if the argument a number? if so then it's the version */
		if (is_numeric($arg1)) {
			$this->version = (int)$arg1;

			/* set the package to the application migration folder */
			ci('package_migration_cli_wrapper')->set_path('',$migration_folder);
		} else {
			/* it's not a number so they must be trying to send in a package */
			$package = trim($arg1,'/');

			/* strip off the migration folder incase they added that to the end of the path */
			if (substr($package, -strlen($migration_folder)) == $migration_folder) {
				$package = substr($package, 0, -strlen($migration_folder));
			}

			/* does this package even exist? */
			if (!file_exists(ROOTPATH.'/'.$package)) {
				ci('console')->error('Package not found.');
			}

			/* ok set the package path and migration folder */
			ci('package_migration_cli_wrapper')->set_path($package,$migration_folder);

			/**
			 * if they still need a version
			 * the version position just went up by 1
			 * because the first is the package
			 */
			$version_position++;
		}

		/**
		 * Migration Config Key
		 * Used in migration.config file
		 * and in current method
		 */
		$this->migration_key = trim('migration.migration_version@'.trim($package, '/'),'@');

		/**
		 * if version is required
		 * and we haven't captured it already
		 * then we need to capture it
		 */
		if ($version_required && !$this->version) {
			$this->version = (int)ci('console')->get_arg($version_position,true,'version');
		}
	}

} /* end class */
