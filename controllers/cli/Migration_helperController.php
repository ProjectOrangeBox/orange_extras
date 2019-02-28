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
	 * show help
	 */
	public function helpCliAction()
	{
		ci('console')->help([
			['Show all of the packages'=>'migration_helper/show-packages'],
			['Create an empty migration file in the packages migration folder.'=>'migration-helper/create_in /packages/misc/orange_snippets'],
			['Auto create a settings migration for select settings.'=>'migration-helper/create_in /packages/misc/orange_snippets'],
			['Auto create a navigation migration for the provided package.'=>'migrate/create_nav_for /packages/misc/orange_snippets'],
			['Auto create a permission migration for the provided package.'=>'migrate/create_permission_for /packages/misc/orange_snippets'],
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

		foreach (get_packages(null,'app') as $path) {
			ci('console')->out(str_replace(ROOTPATH,'',$path));
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
		
		$action = $this->get_radioboxes('What would you like to do?',$actions,'a');
		
		$action .= 'Cliaction';
		
		$this->$action();
	}

	public function create_empty_inCliaction() : void
	{
		$this->validate_package();

		$description = $this->get_input('Please provide a simple migration description?');

		ci('package_migration_cli_wrapper')->create($description,'','','Migration_empty_template');
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
		$this->validate_package();

		$database_config = ci('console')->get_arg(2,false,'Database Connection','default');

		$db = ci()->load->database($database_config, true);

		$tables = $db->list_tables();

		$response = $this->get_checkboxes('Please select each table you would like a migration for:',$tables);

		foreach ($response as $table) {
			/**
			 * Right now this only works with MySQL
			 */
			$record = $db->query('SHOW CREATE TABLE `'.$table.'`')->row_array();

			/* strip auto increment */
			$record['Create Table'] = preg_replace('/AUTO_INCREMENT=(\d+)/m','', $record['Create Table']);

			$table_schema[$record['Table']] = $record['Create Table'];
		}

		$up = '';
		$down = '';

		foreach ($table_schema as $tablename=>$schema) {
			$schema = str_replace('"','\"',$schema);

			$up .= chr(9).chr(9).'ci()->db->query("'.$schema.'");'.PHP_EOL.PHP_EOL;
			$down .= chr(9).chr(9)."ci()->db->query('DROP TABLE `$tablename`;');".PHP_EOL.PHP_EOL;
		}

		ci('package_migration_cli_wrapper')->create('create_table_'.date('Y_m_d'), trim($up), trim($down),'Migration_table_template');
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
		$this->validate_package();

		if (!file_exists(ROOTPATH.'/'.$this->package_path.'/public')) {
			ci('console')->error('There is no public folder in "'.$this->package_path.'".');
		}

		$directory = new RecursiveDirectoryIterator(ROOTPATH.'/'.$this->package_path.'/public',FilesystemIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($directory,RecursiveIteratorIterator::SELF_FIRST);

		$folders = [];

		foreach ($files as $file) {
			if (is_dir($file->getPathname())) {
				$folders[] = $file->getPathname();
			}
		}

		$response = $this->get_checkboxes('Please select each folder your would like a symlink migration created for:',$folders);

		$up = '';
		$down = '';

		foreach ($response as $path) {
			$public_position = strpos($path,'/public/');
			$www_path = substr($path,$public_position + 7);

			$up .= chr(9).chr(9)."\$this->_link_public('$www_path');".PHP_EOL;
			$down .= chr(9).chr(9)."\$this->_unlink_public('$www_path');".PHP_EOL;
		}

		ci('package_migration_cli_wrapper')->create('create_public_symlink_'.date('Y_m_d'), trim($up), trim($down),'Migration_symlink_template');
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
		$this->validate_package();

		$response = $this->get_input('What would you like the folder name to be?');

		$response = '/'.trim($response,'/');

		$up = chr(9).chr(9)."\$this->_add_rw_folder('$response');";
		$down = chr(9).chr(9)."\$this->_remove_rw_folder('$response');";

		ci('package_migration_cli_wrapper')->create('add_readwrite_'.date('Y_m_d'), trim($up), trim($down),'Migration_rw_folder_template');
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
		$this->validate_package();

		if (!file_exists(ROOTPATH.'/'.$this->package_path.'/config')) {
			ci('console')->error('No configuration folder found at "'.$this->package_path.'/config".');
		}

		$files = glob(ROOTPATH.'/'.$this->package_path.'/config/*.php');

		if (!count($files)) {
			ci('console')->error('No configuration files found in "'.$this->package_path.'/config".');
		}

		$response = $this->get_checkboxes('Please select each configuration file your would to copy:',$files);

		$up = '';
		$down = '';

		foreach ($response as $path) {
			$filename = basename($path);

			$up .= chr(9).chr(9)."\$this->_copy_config('support/config/$filename');".PHP_EOL;
			$down .= chr(9).chr(9)."\$this->_unlink_config('support/config/$filename');".PHP_EOL;
		}

		ci('package_migration_cli_wrapper')->create('copy_config_'.date('Y_m_d'), trim($up), trim($down),'Migration_copy_config_template');
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
		$this->validate_package();

		$role_name = $this->get_input('What would you like the role name to be?','r');
		$role_description = $this->get_input('What would you like the role description to be?','d');

		$up = chr(9).chr(9)."ci('o_role_model')->migration_add('$role_name', '$role_description', \$this->hash());";
		$down = chr(9).chr(9)."ci('o_role_model')->migration_remove(\$this->hash());";

		ci('package_migration_cli_wrapper')->create('add_role_'.date('Y_m_d'), trim($up), trim($down),'Migration_role_template');
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
		$this->validate_package();

		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if ($this->package_path == trim($record['package'],'/')) {
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

						$single_group[$url] = "ci('o_nav_model')->migration_add('".$url."','".$text."',\$this->hash());";
					}
				}

				ksort($single_group);

				$groups[$controller_class['name']] = $single_group;
			}
		}

		$up = '';

		foreach ($groups as $group) {
			foreach ($group as $s) {
				$up .= chr(9).chr(9).$s.PHP_EOL;
			}

			$up .= PHP_EOL;
		}

		$down = "ci('o_nav_model')->migration_remove(\$this->hash());";

		ci('package_migration_cli_wrapper')->create('nav_'.date('Y_m_d'), trim($up), trim($down),'Migration_nav_template');
	} /* end function */

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
		$this->validate_package();

		ci('orange_inspector_collector')->cli_end_point('/cli/Orange_inspector_cli/inspect');

		foreach (orange_locator::controllers() as $name=>$record) {
			if ($this->package_path == trim($record['package'],'/')) {
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

						$single_group[] = "ci('o_permission_model')->migration_add('".$key."','".$group."','".$description."',\$this->hash());";
					}
				}

				ksort($single_group);

				$groups[$controller_class['name']] = $single_group;
			}
		}

		$up = '';

		foreach ($groups as $group) {
			foreach ($group as $s) {
				$up .= chr(9).chr(9).$s.PHP_EOL;
			}

			$up .= PHP_EOL;
		}

		$down = "ci('o_permission_model')->migration_remove(\$this->hash());";

		ci('package_migration_cli_wrapper')->create('permission_'.date('Y_m_d'), trim($up), trim($down),'Migration_permission_template');
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
		$this->validate_package();

		$db = ci()->load->database('default', true);

		$query = $db->get('orange_settings');

		foreach ($query->result_array() as $row) {
			$groups[$row['group']] = $row['group'];
		}

		$selected_group = $this->get_radioboxes('Please select a group you wish to export settings from:',$groups);

		$query = $db->get_where('orange_settings',['group'=>$selected_group]);

		foreach ($query->result_array() as $row) {
			$group_items['row'.$row['id']] = $row['name'];
		}

		$selected_records = $this->get_checkboxes('Please select the settings your would like to build a migration for:',$group_items);

		$up = '';
		$down = "ci('o_setting_model')->migration_remove(\$this->hash());";

		foreach ($selected_records as $primary_id) {
			$id = substr($primary_id,3);
			
			$row = $db->get_where('orange_settings',['id'=>$id])->row_array();

			$name = str_replace("'","\'",$row['name']);
			$group = str_replace("'","\'",$row['group']);
			$value = str_replace(["'",chr(10),chr(13)],["\'",'',''],$row['value']);
			$help = str_replace(["'",chr(10),chr(13)],["\'",'',''],$row['help']);
			$options = str_replace(["'",chr(10),chr(13)],["\'",'',''],$row['options']);

			$up .= chr(9).chr(9)."ci('o_setting_model')->migration_add('$name', '$group', '$value', '$help', '$options', \$this->hash());".PHP_EOL;
		}

		ci('package_migration_cli_wrapper')->create('settings_'.$selected_group.'_'.date('Y_m_d'), trim($up), trim($down),'Migration_settings_template');
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param $responds
	 * @param string $option i
	 *
	 * @throws
	 * @return 
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	protected function get_input(string $text,string $option = 'i')
	{
		/* did they include the option with -? */
		foreach ($_SERVER['argv'] as $idx=>$arg) {
			if ($arg == '-'.$option) {
				if (isset($_SERVER['argv'][$idx+1])) {
					return $_SERVER['argv'][$idx+1];
				}
			}
		}

		/* else ask */
		$input = ci('console')->br()->input('<green>'.$text.'</green>');

		$responds = trim($input->prompt());

		if (empty($responds)) {
			ci('console')->error('Nothing entered.');
		}
		
		return $responds;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param array $options
	 * @param $responds
	 * @param string $option c
	 *
	 * @throws
	 * @return 
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	protected function get_checkboxes(string $text,array $options,string $option = 'c')
	{
		/* did they include the option with -i? */
		foreach ($_SERVER['argv'] as $idx=>$arg) {
			if ($arg == '-'.$option) {
				if (isset($_SERVER['argv'][$idx+1])) {
					return explode(',',$_SERVER['argv'][$idx+1]);
				}
			}
		}

		/* else ask */

		if (!count($options)) {
			ci('console')->error('Nothing options provided.');
		}

		if (count($options) == 1) {
			$responds = [];
			
			$responds[] = key($options);

			return;
		}

		$input = ci('console')->br()->checkboxes($text,$options);

		$responds = $input->prompt();

		if (!count($responds)) {
			ci('console')->error('Nothing selected.');
		}
		
		return $responds;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $text
	 * @param array $options
	 * @param $responds
	 * @param string $option r
	 *
	 * @throws
	 * @return 
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	protected function get_radioboxes(string $text,array $options, string $option = 'r')
	{
		/* did they include the option with -i? */
		foreach ($_SERVER['argv'] as $idx=>$arg) {
			if ($arg == '-'.$option) {
				if (isset($_SERVER['argv'][$idx+1])) {
					return $_SERVER['argv'][$idx+1];
				}
			}
		}

		/* else ask */

		if (!count($options)) {
			ci('console')->error('Nothing options provided.');
		}

		if (count($options) == 1) {
			$responds = [];

			$responds[] = key($options);

			return;
		}

		$input = ci('console')->br()->radio('<green>'.$text.'</green>',$options);

		$responds = $input->prompt();

		if (empty($responds)) {
			ci('console')->error('Nothing selected.');
		}
		
		return $responds;
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
	protected function validate_package() : Migration_helperController
	{
		$selected_package_path = $this->get_radioboxes('Select the package you would like to create a migration for:',get_packages(null,null,true),'p');

		$this->package_path = str_replace(ROOTPATH.'/','',$selected_package_path);

		$migration_path = '/'.trim(str_replace(ROOTPATH, '', config('migration.migration_path', '/support/migrations/')), '/');

		/* create folder if it doesn't exist */
		ci('package_migration_cli_wrapper')->create_folder(ROOTPATH.'/'.$this->package_path.$migration_path);

		/* switch package migration to work in this folder */
		ci('package_migration')->set_path('/'.$this->package_path.$migration_path);
		
		return $this;
	}

} /* end class */
