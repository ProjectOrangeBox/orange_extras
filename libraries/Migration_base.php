<?php

class Migration_base
{
	/**
	 * $dbforge
	 *
	 * @var undefined
	 */
	protected $dbforge;

	/**
	 * $_error_string
	 *
	 * @var string
	 */
	protected $_error_string = '';

	/**
	 * $console
	 *
	 * @var undefined
	 */
	protected $console;

	/**
	 * $hash
	 *
	 * @var null
	 */
	protected $hash = null;

	/**
	 * $migration
	 *
	 * @var undefined
	 */
	protected $migration;

	/**
	 * $migration_file
	 *
	 * @var undefined
	 */
	protected $migration_file;

	/**
	 * $migration_package
	 *
	 * @var undefined
	 */
	protected $migration_package;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		/* we will probbly need this */
		ci()->load->helper('file');

		/* and this */
		$this->dbforge = &ci()->dbforge;
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
	 * up
	 *
	 * @return void
	 */
	public function up()
	{
		return true;
	}

	/**
	 * down
	 *
	 * @return void
	 */
	public function down()
	{
		return true;
	}

	/**
	 * get_hash
	 *
	 * @return void
	 */
	protected function get_hash()
	{
		return $this->hash;
	}

	/**
	 * hash
	 *
	 * @return void
	 */
	protected function hash()
	{
		return $this->hash;
	}

	/**
	 * migration
	 *
	 * @param mixed $direction
	 * @param mixed $as_string=true
	 * @return void
	 */
	protected function migration($direction, $as_string=true)
	{
		/* one back trace here and thats it! */
		$children = debug_backtrace();

		$file = $children[0]['file'];

		$info = pathinfo($file);

		$this->migration_file = $info['filename'];
		$this->migration_package = str_replace([ROOTPATH.'/','/support/migrations'], '',$info['dirname']);
		$this->hash = $this->migration_package.'/'.$info['filename'];

		$data = [
			'migration'=>$this->migration_file,
			'direction'=>$direction,
			'hash'=>$this->hash,
		];

		return ($as_string) ? 'Migrations: '.$data['migration'].PHP_EOL.' Direction: '.$data['direction'].PHP_EOL.'      Hash: '.$data['hash'].PHP_EOL.PHP_EOL : $data;
	}

	/**
	 * e
	 *
	 * @param mixed $output
	 * @return void
	 */
	protected function e($output)
	{
		if (is_cli()) {
			echo $output.chr(10);
		}
	}

	/**
	 * _get_package
	 *
	 * @return void
	 */
	protected function _get_package()
	{
		return $this->migration_package;
	}

	/**
	 * _copy_config
	 *
	 * @param mixed $filename
	 * @return void
	 */
	protected function _copy_config($filename)
	{
		$filename = trim($filename, '/');

		$package_folder = $this->_get_package();
		$package_config = $package_folder.'/'.$filename;
		$config_file = 'config/'.basename($filename);

		$success = true;

		if (file_exists(ROOTPATH.$package_config)) {
			if (is_writable(APPPATH.'config')) {
				$success = copy(ROOTPATH.$package_config, APPPATH.$config_file);
			} else {
				show_error('Can not write to "'.APPPATH.'config"');
			}
		} else {
			show_error('Could not locate the specified config file "'.$package_config.'"');
		}

		return $success;
	}

	/**
	 * _unlink_config
	 *
	 * @param mixed $filename
	 * @return void
	 */
	protected function _unlink_config($filename)
	{
		$name = basename($filename, '.php');
		$config_file = 'config/'.$name.'.php';

		$success     = true;

		if (file_exists(APPPATH.$config_file)) {
			$success = unlink(APPPATH.$config_file);
		}

		return $success;
	}

	/**
	 * _link_public
	 *
	 * @param mixed $path
	 * @return void
	 */
	protected function _link_public($path)
	{
		$package_folder = ROOTPATH.$this->_get_package().'/public/'.ltrim($path, '/');
		$public_folder = WWW.'/'.ltrim($path, '/');

		/* let's make the public path if it's not there */
		$drop_folder = dirname($public_folder);

		if (!is_dir($drop_folder)) {
			mkdir($drop_folder, 0777, true);
		}

		/* remove the link/file if it's there */
		@unlink($public_folder);

		if (!$this->_relative_symlink($package_folder, $public_folder)) {
			show_error('Could not symlink package folder to public location.');
		}

		return true;
	}

	/**
	 * _unlink_public
	 *
	 * @param mixed $path
	 * @return void
	 */
	protected function _unlink_public($path)
	{
		$public_folder = WWW.'/'.ltrim($path, '/');

		return unlink($public_folder);
	}

	/**
	 * _add_rw_folder
	 *
	 * @param mixed $path
	 * @return void
	 */
	protected function _add_rw_folder($path)
	{
		$var_folder = dirname(site_url('{rootpath}{uploads}', false));

		return (is_writable($var_folder)) ? @mkdir($var_folder.'/'.rtrim($path, '/'), 0777, true) : false;
	}

	/**
	 * _remove_rw_folder
	 *
	 * @param mixed $path
	 * @return void
	 */
	protected function _remove_rw_folder($path)
	{
		$var_folder = dirname(path('{rootpath}{uploads}', false));

		return $this->_rmdirr($var_folder.'/'.rtrim($path, '/'));
	}

	/**
	 * _rmdirr
	 *
	 * @param mixed $directory
	 * @return void
	 */
	protected function _rmdirr($directory)
	{
		/* checks */
		if (!is_string($directory) || empty($directory)) {
			return $false;
		}
		if (!is_dir($directory) || is_link($directory)) {
			return unlink($directory);
		}

		foreach (scandir($directory) as $file) {
			if ($file == '.' || $file == '..') {
				continue;
			}

			if (!$this->_rmdirr($directory . '/' . $file)) {
				chmod($directory . '/' . $file, 0777);

				if (!$this->_rmdirr($directory . '/' . $file)) {
					return false;
				}
			};
		}

		/* finally the folder */
		return rmdir($directory);
	}

	/**
	 * _add_route
	 *
	 * @param mixed $text
	 * @param mixed $before_text
	 * @return void
	 */
	protected function _add_route($text, $before_text)
	{
		$text = rtrim($text);
		$route_file = APPPATH.'config/routes.php';

		if (!is_writable($route_file)) {
			show_error('Route file "'.$route_file.'" can not be read and written to please chmod it before continuing.');
		}

		$matched = false;
		$new_contents = '';
		$lines = explode(PHP_EOL, file_get_contents($route_file));

		foreach ($lines as $line) {
			if (strpos($line, $before_text) !== false && !$matched) {
				$new_contents .= $text.PHP_EOL;
				$matched = true;
			}

			$new_contents .= $line.PHP_EOL;
		}

		if (!$matched) {
			show_error('Route before text "'.$before_text.'" not found in "'.$route_file.'"');
		}

		return file_put_contents($route_file, trim($new_contents).PHP_EOL);
	}

	/**
	 * _remove_route
	 *
	 * @param mixed $text
	 * @param mixed $quiet=false
	 * @return void
	 */
	protected function _remove_route($text, $quiet=false)
	{
		$text = rtrim($text);
		$route_file = APPPATH.'config/routes.php';

		if (!is_writable($route_file)) {
			show_error('Route file "'.$route_file.'" can not be read and written to please chmod it before continuing.');
		}

		$content = file_get_contents($route_file);

		if (!$quiet) {
			if (strpos($content, $text) === false) {
				show_error('Route text not found in "'.$route_file.'"');
			}
		}

		$content = str_replace($text.PHP_EOL, '', $content);

		return file_put_contents($route_file, trim($content).PHP_EOL);
	}

	/**
	 * _describe_table
	 *
	 * @param mixed $tablename
	 * @param mixed $database_config
	 * @return void
	 */
	protected function _describe_table($tablename, $database_config = 'default')
	{
		$db = ci()->load->database($database_config, true);

		$fields = false;

		if ($db->table_exists($tablename)) {
			$fields = (array)$db->list_fields($tablename);
		}

		return $fields;
	}

	/**
	 * _db_has_column
	 *
	 * @param mixed $column
	 * @param mixed $tablename
	 * @param mixed $database_config
	 * @return void
	 */
	protected function _db_has_column($column, $tablename, $database_config = 'default')
	{
		$columns = $this->_describe_table($tablename, $database_config);

		return (is_array($columns)) ? in_array($column, $columns) : false;
	}

	/**
	 * _find_n_replace
	 *
	 * @param mixed $file_path
	 * @param mixed $find
	 * @param mixed $replace
	 * @param mixed $return=false
	 * @return void
	 */
	protected function _find_n_replace($file_path, $find, $replace, $return=false)
	{
		$success = false;

		if (file_exists($file_path)) {
			$contents = file_get_contents($file_path);

			$contents = str_replace($find, $replace, $contents);

			$success = (!$return) ? file_put_contents($file_path, $contents) : $contents;
		}

		return $success;
	}

	/* target = file target / link = name */
	/**
	 * _relative_symlink
	 *
	 * @param mixed $target
	 * @param mixed $link
	 * @return void
	 */
	protected function _relative_symlink($target, $link)
	{
		/* remove the link that might be there */

		$link = str_replace(ROOTPATH, '', $link);
		$target = str_replace(ROOTPATH, '', $target);

		/* remove it if it's already there */
		@unlink(ROOTPATH.$link);

		/* create it */
		return symlink(ROOTPATH.$target, ROOTPATH.$link);
	}

	/**
	 * _getRelativePath
	 *
	 * @param mixed $from_path
	 * @param mixed $to_path
	 * @return void
	 */
	protected function _getRelativePath($from_path, $to_path)
	{
		/* some compatibility fixes for Windows paths */
		$from_path = is_dir($from_path) ? rtrim($from_path, '\/') . '/' : $from_path;
		$to_path   = is_dir($to_path) ? rtrim($to_path, '\/') . '/' : $to_path;

		$from_path = str_replace('\\', '/', $from_path);
		$to_path   = str_replace('\\', '/', $to_path);

		$from_path    = explode('/', $from_path);
		$to_path      = explode('/', $to_path);

		$relPath = $to_path;

		foreach ($from_path as $depth => $dir) {
			// find first non-matching dir
			if ($dir === $to_path[$depth]) {
				// ignore this directory
				array_shift($relPath);
			} else {
				// get number of remaining dirs to $from_path
				$remaining = count($from_path) - $depth;

				if ($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath   = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}

		return implode('/', $relPath);
	}
} /*end class */
