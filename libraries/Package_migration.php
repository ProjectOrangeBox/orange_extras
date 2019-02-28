<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Migration Class
 *
 * All migrations should implement this, forces up() and down() and gives
 * access to the CI super-global.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Reactor Engineers
 * @link
 */
class Package_migration
{

	/**
	 * Path to migration classes
	 *
	 * @var string
	 */
	protected $_migration_path = null;

	/**
	 * Current migration version
	 *
	 * @var mixed
	 */
	protected $_migration_version = 0;

	/**
	 * Database table with migration info
	 *
	 * @var string
	 */
	protected $_migration_table = '';

	/**
	 * Whether to automatically run migrations
	 *
	 * @var	bool
	 */
	protected $_migration_auto_latest = false;

	/**
	 * Migration basename regex
	 *
	 * @var string
	 */
	protected $_migration_regex = '/^\d+_(\w+)$/';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $_error_string = '';

	/**
	 * Initialize Migration Class
	 *
	 * @param	array	$config
	 * @return	void
	 */
	public function __construct()
	{
		log_message('info', 'Migrations Class Initialized');

		// Are they trying to use migrations while it is disabled?
		if (config('migration.migration_enabled') !== true) {
			show_error('Migrations has been loaded but is disabled or set up incorrectly.');
		}

		$this->_migration_version = config('migration.migration_version');
		$this->_migration_table = config('migration.migration_table');

		$this->set_path(config('migration.migration_path'));

		// Load migration language
		$this->lang->load('migration');

		// They'll probably be using dbforge
		$this->load->dbforge();

		// Make sure the migration table name was set.
		if (empty($this->_migration_table)) {
			show_error('Migrations configuration file (migration.php) must have "migration_table" set.');
		}

		// If the migrations table is missing, make it
		if (!$this->db->table_exists($this->_migration_table)) {
			$this->dbforge->add_field([
				'package' => ['type' => 'VARCHAR', 'constraint' => 255],
				'version' => ['type' => 'BIGINT', 'constraint' => 20],
			]);

			$this->dbforge->add_key('package', true);

			$this->dbforge->create_table($this->_migration_table, true);
		}

		if (count($this->db->list_fields($this->_migration_table)) == 1) {
			show_error('Your migration table "'.$this->_migration_table.'" is not package compatible. Please delete "'.$this->_migration_table.'" and let the Package_migration class create it. Then only use the package migration class since it can handle multiple packages.');
		}
	}

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param	string	$target_version	Target schema version
	 * @return	mixed	TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function version($target_version)
	{
		// Note: We use strings, so that timestamp versions work on 32-bit systems
		$current_version = $this->get_version();

		$target_version = sprintf('%03d', $target_version);

		$migrations = $this->find_migrations();

		if ($target_version > 0 && ! isset($migrations[$target_version])) {
			$this->_error_string = sprintf($this->lang->line('migration_not_found'), $target_version);

			return false;
		}

		if ($target_version > $current_version) {
			$method = 'up';
		} elseif ($target_version < $current_version) {
			$method = 'down';
			// We need this so that migrations are applied in reverse order
			krsort($migrations);
		} else {
			// Well, there's nothing to migrate then ...
			return true;
		}

		// Validate all available migrations within our target range.
		//
		// Unfortunately, we'll have to use another loop to run them
		// in order to avoid leaving the procedure in a broken state.
		//
		// See https://github.com/bcit-ci/CodeIgniter/issues/4539
		$pending = [];

		foreach ($migrations as $number => $file) {
			// Ignore versions out of our range.
			//
			// Because we've previously sorted the $migrations array depending on the direction,
			// we can safely break the loop once we reach $target_version ...
			if ($method === 'up') {
				if ($number <= $current_version) {
					continue;
				} elseif ($number > $target_version) {
					break;
				}
			} else {
				if ($number > $current_version) {
					continue;
				} elseif ($number <= $target_version) {
					break;
				}
			}

			// Check for sequence gaps
			if (isset($previous) && abs($number - $previous) > 1) {
				$this->_error_string = sprintf($this->lang->line('migration_sequence_gap'), $number);

				return false;
			}

			$previous = $number;

			include_once $file;

			/* full filename used so we don't run into other migration classes that have the same name but a different version */
			$class = 'Migration_'.ucfirst(strtolower(basename($file, '.php')));

			// Validate the migration file structure
			if (!class_exists($class, false)) {
				$this->_error_string = sprintf($this->lang->line('migration_class_doesnt_exist'), $class);

				return false;
			} elseif (! is_callable([$class, $method])) {
				$this->_error_string = sprintf($this->lang->line('migration_missing_'.$method.'_method'), $class);

				return false;
			}

			$pending[$number] = [$class, $method];
		}

		// Now just run the necessary migrations
		foreach ($pending as $number => $migration) {
			log_message('debug', 'Migrating '.$method.' from version '.$current_version.' to version '.$number);

			$migration[0] = new $migration[0]();

			$success = call_user_func($migration); /* migrations must return true to continue */

			if (!$success) {
				$error_string = $migration[0]->error_string();

				$this->_error_string = trim('Error Migrating '.$method.' from version '.$current_version.' to version '.$number.chr(10).$error_string);

				log_message('debug', $this->_error_string);

				return false;
			}

			$current_version = $number;

			$this->_update_version($current_version);
		}

		// This is necessary when moving down, since the the last migration applied
		// will be the down() method for the next migration up from the target
		if ($current_version <> $target_version) {
			$current_version = $target_version;

			$this->_update_version($current_version);
		}

		log_message('debug', 'Finished migrating to '.$current_version);

		return $current_version;
	}

	/**
	 * Sets the schema to the latest migration
	 *
	 * @return	mixed	Current version string on success, FALSE on failure
	 */
	public function latest()
	{
		$migrations = $this->find_migrations();

		if (empty($migrations)) {
			$this->_error_string = $this->lang->line('migration_none_found');

			return false;
		}

		$last_migration = basename(end($migrations));

		// Calculate the last migration step from existing migration
		// filenames and proceed to the standard version migration
		return $this->version($this->_get_migration_number($last_migration));
	}

	/**
	 * Sets the schema to the migration version set in config
	 *
	 * @return	mixed	TRUE if no migrations are found, current version string on success, FALSE on failure
	 */
	public function current($version=null)
	{
		$version = ($version) ?? $this->_migration_version;

		return $this->version($version);
	}

	/**
	 * Error string
	 *
	 * @return	string	Error message returned as a string
	 */
	public function error_string()
	{
		return $this->_error_string;
	}

	/**
	 * Retrieves list of available migration scripts
	 *
	 * @return	array	list of migration file paths sorted by version
	 */
	public function find_migrations()
	{
		$migrations = [];

		// Load all *_*.php files in the migrations path
		foreach (glob(ROOTPATH.$this->_migration_path.'*_*.php') as $file) {
			$name = basename($file, '.php');

			// Filter out non-migration files
			if (preg_match($this->_migration_regex, $name)) {
				$number = $this->_get_migration_number($name);

				// There cannot be duplicate migration numbers
				if (isset($migrations[$number])) {
					$this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $number);

					show_error($this->_error_string);
				}

				$migrations[$number] = $file;
			}
		}

		ksort($migrations);

		return $migrations;
	}

	/**
	 * Extracts the migration number from a filename
	 *
	 * @param	string	$migration
	 * @return	string	Numeric portion of a migration filename
	 */
	protected function _get_migration_number($migration)
	{
		return sscanf($migration, '%[0-9]+', $number) ? $number : '0';
	}

	/**
	 * Retrieves current schema version
	 *
	 * @return	string	Current migration version
	 */
	public function get_version()
	{
		$row = $this->db->select('version')->where(['package'=>$this->prep_package($this->_migration_path)])->get($this->_migration_table)->row();

		return $row ? $row->version : '0';
	}

	public function set_path($path = null)
	{
		$this->_migration_path = ($path) ? rtrim($path, '/').'/' : rtrim(config('migration.migration_path'), '/');

		return $this;
	}

	public function create(string $description, string $up , string $down, string $template)
	{
		$name = ($description) ? filter('filename', $description) : 'migration';
		$stamp = (config('migration.migration_type') == 'timestamp') ? date('YmdHis') : $this->get_next_sequential($this->_migration_path);

		$file = ROOTPATH.rtrim($this->_migration_path, '/').'/'.$stamp.'_'.$name.'.php';

		if (!file_exists(dirname($file))) {
			if (!mkdir(dirname($file), 0777, true)) {
				show_error('Can not write to '.dirname($file).chr(10));
			}
		}

		if (file_exists($file)) {
			show_error('File "'.$file.'" already exists.'.PHP_EOL);
		}

		$template = file_get_contents(__DIR__.'/templates/'.$template.'.tmpl');

		$php = ci('parser')->parse_string($template, ['name'=>basename($file, '.php'),'up'=>$up,'down'=>$down], true);

		$written = (file_put_contents($file, $php)) ? $file : false;

		if ($written) {
			chmod($file, 0644);
		}

		return $written;
	}

	/**
	 * Stores the current schema version
	 *
	 * @param	string	$migration	Migration reached
	 * @return	void
	 */
	protected function _update_version($migration)
	{
		$this->db->replace($this->_migration_table, ['package'=>$this->prep_package($this->_migration_path),'version'=>$migration]);
	}

	protected function prep_package($path)
	{
		$prep = str_replace([ROOTPATH,'/support/migrations/'], '', $path);

		return (empty($prep) ? '/application' : $prep);
	}

	protected function get_next_sequential($folder)
	{
		list($highest) = explode('_', basename(end(glob(ROOTPATH.$folder.'*_*.php'))), 1);

		return substr('000'.((int)$highest+1), -3);
	}

	/**
	 * Enable the use of CI super-global
	 *
	 * @param	string	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}
}
