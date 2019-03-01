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
class Migration_helperController extends MY_Controller
{
	/**
	 * Current package path
	 *
	 * @var string
	 */
	protected $package_path = '';

	/**
	 * Current package path
	 *
	 * @var string
	 */
	protected $up_source = [];

	/**
	 * Current package path
	 *
	 * @var string
	 */
	protected $down_source = [];

	/**
	 * Current package path
	 *
	 * @var string
	 */
	protected $in_loop = false;

	/**
	 * show help
	 */
	public function helpCliAction() : void
	{
		ci('console')->help([
			['Show all of the registered packages'=>'%%/show-packages'],

			['Dynamically create migration'=>'%%/create'],

			['Create a empty migration in a given package'],
			['-p Package Path'],
			['-d Description'=>'%%/create-empty-in'],

			['Create a table schema migration in a given package'],
			['-p Package Path'],
			['-d Database Connection [default]'],
			['-t Table'=>'%%/create_table_in'],

			['Create a public symlink migration in a given package'],
			['-p Package Path'],
			['-f Folder Path'=>'%%/create_symlink_in'],

			['Create a read / write folder in var migration in a given package'],
			['-p Package Path'],
			['-f Folder'=>'%%/create_rw_folder_in'],

			['Create a config file copy migration in a given package'],
			['-p Package Path'],
			['-f Folder'=>'%%/create_config_copy_in'],

			['Create a role migration in a given package'],
			['-p Package Path'],
			['-r Role Name'],
			['-d Role Description'=>'%%/create_role_in'],

			['Create a navigation menu migration in a given package'],
			['-p Package Path'],
			['-u URL'=>'%%/create_nav_in'],

			['Create a permission migration in a given package'],
			['-p Package Path'=>'%%/create_permission_in'],

			['Create a settings migration in a given package'],
			['-p Package Path'],
			['-g Settings Group'],
			['-n Settings Name in Group'=>'%%/copy_settings_in'],
		]);
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function show_packagesCliAction() : void
	{
		ci('console')->h1('Registered Packages');

		foreach (get_packages(null, 'app') as $path) {
			ci('console')->out(str_replace(ROOTPATH, '', $path));
		}

		ci('console')->br();
	}

	/**
	 *
	 * Builds a standard migration template
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function createCliAction() : void
	{
		$actions = [
			'create_empty_in'=>'Create Empty Migration',
			'copy_settings_in'=>'Create Settings Migration',
			'create_permission_in'=>'Auto Determine Permissions & Create Migration',
			'create_nav_in'=>'Auto Determine Navigation & Create Migration',
			'create_role_in'=>'Create Add New Role Migration',
			'create_config_copy_in'=>'Create Configuration Copy Migration',
			'create_rw_folder_in'=>'Create Read / Write Folder Migration',
			'create_symlink_in'=>'Create Public Symlink Migration',
			'create_table_in'=>'Create Table Migration',
		];

		$this->capture_package();

		$this->in_loop = true;

		while ($this->in_loop) {
			$action = $this->get_radioboxes('What would you like to do?', $actions, 'a');

			$action .= 'Cliaction';

			$this->$action();

			$input = ci('console')->hr()->input('Add another option?');
			$input->accept(['y','n']);
			$response = $input->prompt();

			if ($response == 'n') {
				$this->in_loop = false;
			}
		}

		$this->write_file('Migration', 'default');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_empty_inCliaction() : void
	{
		$this->capture_package();

		$description = ci('console')->get_input('Please provide a simple migration description?', 'd');

		$this->write_file($description, 'empty');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_table_inCliAction() : void
	{
		$this->capture_package();

		$database_config = ci('console')->get_arg(2, false, 'Database Connection', 'default', 'd');

		$db = ci()->load->database($database_config, true);

		$tables = $db->list_tables();

		$response = ci('console')->get_checkboxes('Please select each table you would like a migration for:', $tables, 't');

		foreach ($response as $table) {
			/**
			 * Right now this only works with MySQL
			 */
			$record = $db->query('SHOW CREATE TABLE `'.$table.'`')->row_array();

			/* strip auto increment */
			$record['Create Table'] = preg_replace('/AUTO_INCREMENT=(\d+)/m', '', $record['Create Table']);

			$table_schema[$record['Table']] = $record['Create Table'];
		}

		foreach ($table_schema as $tablename=>$schema) {
			$schema = str_replace('"', '\"', $schema);

			$this->up_source[] = 'ci()->db->query("'.$schema.'");';
			$this->down_source[] = "ci()->db->query('DROP TABLE `$tablename`;');";
		}

		$this->write_file('create_table', 'table');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_symlink_inCliAction() : void
	{
		$this->capture_package();

		if (!file_exists(ROOTPATH.'/'.$this->package_path.'/public')) {
			ci('console')->error('There is no public folder in "'.$this->package_path.'".');
		}

		$directory = new RecursiveDirectoryIterator(ROOTPATH.'/'.$this->package_path.'/public', FilesystemIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

		$folders = [];

		foreach ($files as $file) {
			if (is_dir($file->getPathname())) {
				$folders[] = $file->getPathname();
			}
		}

		$response = ci('console')->get_checkboxes('Please select each folder your would like a symlink migration created for:', $folders, 'f');

		foreach ($response as $path) {
			$public_position = strpos($path, '/public/');
			$www_path = substr($path, $public_position + 7);

			$this->up_source[] = "\$this->_link_public('$www_path');";
			$this->down_source[] = "\$this->_unlink_public('$www_path');";
		}

		$this->write_file('create_public_symlink', 'symlink');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_rw_folder_inCliAction() : void
	{
		$this->capture_package();

		$response = ci('console')->get_input('What would you like the folder name to be?', 'f');

		$response = '/'.trim($response, '/');

		$this->up_source[] = "\$this->_add_rw_folder('$response');";
		$this->down_source[] = "\$this->_remove_rw_folder('$response');";

		$this->write_file('add_readwrite', 'rw_folder');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_config_copy_inCliAction() : void
	{
		$this->capture_package();

		if (!file_exists(ROOTPATH.'/'.$this->package_path.'/config')) {
			ci('console')->error('No configuration folder found at "'.$this->package_path.'/config".');
		}

		$files = glob(ROOTPATH.'/'.$this->package_path.'/config/*.php');

		if (!count($files)) {
			ci('console')->error('No configuration files found in "'.$this->package_path.'/config".');
		}

		$response = ci('console')->get_checkboxes('Please select each configuration file your would to copy:', $files, 'f');

		foreach ($response as $path) {
			$filename = basename($path);

			$this->up_source[] = "\$this->_copy_config('support/config/$filename');";
			$this->down_source[] = "\$this->_unlink_config('support/config/$filename');";
		}

		$this->write_file('copy_config', 'copy_config');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_role_inCliAction() : void
	{
		$this->capture_package();

		$role_name = ci('console')->get_input('What would you like the role NAME to be?', 'r');
		$role_description = ci('console')->get_input('What would you like the role DESCRIPTION to be?', 'd');

		$this->up_source[] = "ci('o_role_model')->migration_add('$role_name', '$role_description', \$this->hash());";
		$this->down_source[] = "ci('o_role_model')->migration_remove(\$this->hash());";

		$this->write_file('add_role', 'role');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_nav_inCliAction() : void
	{
		$this->capture_package();

		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if ($this->package_path == trim($record['package'], '/')) {
				ci('orange_inspector_collector')->inspect($record['controller']);
			}
		}

		$details = ci('orange_inspector_collector')->details();

		if (count($details) == 0) {
			ci('console')->error('No controllers found in "'.$this->package_path.'".');
		}

		$groups = [];

		/* now for each of the controllers we need to create a migration */
		foreach ($details as $controller_class) {
			foreach ($controller_class['methods'] as $method) {
				if ($method['public'] && substr($method['name'], -6) == 'Action') {
					$pieces = preg_split('/(?=[A-Z])/', $method['name']);
					$action = array_shift($pieces);

					/* grab the http request method it would be the second if it's there */
					$request_type = strtolower(array_shift($pieces));

					if (!in_array($request_type, ['post','put','delete','cli'])) {
						$action = ($action == 'index') ? '' : $action;
						$controller_position = strpos($controller_class['file path'], '/controllers/');
						$url = substr($controller_class['file path'], $controller_position + 12, -14);
						$url = str_replace('_', '-', '/'.strtolower(trim($url.'/'.$action, '/')));
						$text = trim(ucwords(strtolower(str_replace(['/','_','-'], ' ', $url))), ' ');

						$groups[$url] = "ci('o_nav_model')->migration_add('".$url."','".$text."',\$this->hash());";
					}
				}
			}
		}

		ksort($groups);

		/* build display and determine the array key you want */
		$display_array = [];

		foreach ($groups as $k=>$g) {
			$display_array[$k] = $k;
		}

		$selected = ci('console')->get_checkboxes('Select the URLs you would like to create migrations for:', $display_array, 'u');

		$up_source_group = array_intersect_key($groups, array_combine($selected, $selected));

		foreach ($up_source_group as $up) {
			$this->up_source[] = $up;
		}

		$this->down_source[] = "ci('o_nav_model')->migration_remove(\$this->hash());";

		$this->write_file('nav', 'nav');
	}
	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function create_permission_inCliAction() : void
	{
		$this->capture_package();

		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if ($this->package_path == trim($record['package'], '/')) {
				ci('orange_inspector_collector')->inspect($record['controller']);
			}
		}

		$details = ci('orange_inspector_collector')->details();

		if (count($details) == 0) {
			ci('console')->error('No controllers found in "'.$this->package_path.'".');
		}

		$groups = [];

		/* now for each of the controllers we need to create a migration */
		foreach ($details as $controller_class) {
			foreach ($controller_class['methods'] as $method) {
				if ($method['public'] && substr($method['name'], -6) == 'Action') {
					$pieces = preg_split('/(?=[A-Z])/', $method['name']);
					$count_pieces = count($pieces);

					if ($count_pieces == 3) {
						/* foobarPostAction */
						$url_piece = $pieces[0];
						$request_type = strtolower($pieces[1]);
						$action = $pieces[2];
					} elseif ($count_pieces == 2) {
						/* foobarAction */
						$url_piece = $pieces[0];
						$request_type = 'get';
						$action = $pieces[2];
					} else {
						ci('console')->error('spliting method "'.$method['name'].'" failed must have 2 or 3 sections seperated by uppercase letters.');
					}

					if (!in_array($request_type, ['cli'])) {
						$controller_position = strpos($controller_class['file path'], '/controllers/');
						$url = substr($controller_class['file path'], $controller_position + 12, -14);
						$group = filter('human', $url);
						$key = strtolower('url::'.$url.'::'.$url_piece.'~'.$request_type);
						$description = filter('human', $url.' '.$url_piece.' '.$request_type);

						$groups[] = "ci('o_permission_model')->migration_add('".$key."','".$group."','".$description."',\$this->hash());";
					}
				}
			}
		}

		ksort($groups);

		foreach ($groups as $up) {
			$this->up_source[] = $up;
		}

		$this->down_source[] = "ci('o_permission_model')->migration_remove(\$this->hash());";

		$this->write_file('permission', 'permission');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function copy_settings_inCliAction() : void
	{
		$this->capture_package();

		$db = ci()->load->database('default', true);

		$query = $db->get('orange_settings');

		foreach ($query->result_array() as $row) {
			$groups[$row['group']] = $row['group'];
		}

		$selected_group = ci('console')->get_radioboxes('Please select a group you wish to export settings from:', $groups, 'g');

		$query = $db->get_where('orange_settings', ['group'=>$selected_group]);

		foreach ($query->result_array() as $row) {
			$group_items[$row['name']] = $row['name'];
		}

		$selected_records = ci('console')->get_checkboxes('Please select the settings your would like to build a migration for:', $group_items, 'n');

		$this->down_source[] = "ci('o_setting_model')->migration_remove(\$this->hash());";

		foreach ($selected_records as $name) {
			$row = $db->get_where('orange_settings', ['group'=>$selected_group,'name'=>$name])->row_array();

			$name = str_replace("'", "\'", $row['name']);
			$group = str_replace("'", "\'", $row['group']);
			$value = str_replace(["'",chr(10),chr(13)], ["\'",'',''], $row['value']);
			$help = str_replace(["'",chr(10),chr(13)], ["\'",'',''], $row['help']);
			$options = str_replace(["'",chr(10),chr(13)], ["\'",'',''], $row['options']);

			$this->up_source[] = "ci('o_setting_model')->migration_add('$name', '$group', '$value', '$help', '$options', \$this->hash());";
		}

		$this->write_file('settings_'.$selected_group, 'settings');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $filename
	 * @param string $template
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	protected function write_file(string $filename, string $template) : void
	{
		if (!$this->in_loop) {
			$up = '';

			foreach ($this->up_source as $s) {
				$up .= chr(9).chr(9).$s.PHP_EOL;
			}

			$down = '';

			foreach ($this->down_source as $s) {
				$down .= chr(9).chr(9).$s.PHP_EOL;
			}

			ci('package_migration_cli_wrapper')->create($filename.'_'.date('Y_m_d'), trim($up), trim($down), 'Migration_'.$template.'_template');

			exit(1);
		}
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param
	 *
	 * @throws
	 * @return void
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	protected function capture_package() : Migration_helperController
	{
		if (empty($this->package_path)) {
			$selected_package_path = ci('console')->get_radioboxes('Select the package you would like to create a migration for:', get_packages(null, null, true), 'p');

			$this->package_path = trim(str_replace(ROOTPATH.'/', '', $selected_package_path), '/');

			$migration_path = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');

			/* create folder if it doesn't exist */
			ci('package_migration_cli_wrapper')->create_folder(ROOTPATH.'/'.$this->package_path.$migration_path);

			/* switch package migration to work in this folder */
			ci('package_migration')->set_path('/'.$this->package_path.$migration_path);
		}

		return $this;
	}

} /* end class */
