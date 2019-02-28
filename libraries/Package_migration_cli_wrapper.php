<?php

/*
This provides cli OUTPUT because the package_migration library doesn't
*/
class Package_migration_cli_wrapper
{
	protected $package = '';
	protected $folder = '';

	/* https://www.codeigniter.com/user_guide/libraries/migration.html */

	public function latest()
	{
		/* Standard CodeIgniter */
		$this->process_mixed(ci('package_migration')->latest(), 'latest');
	}

	public function version($version)
	{
		/* Standard CodeIgniter */
		$this->process_mixed(ci('package_migration')->version($version), $version);
	}

	public function current($version)
	{
		$this->process_mixed(ci('package_migration')->current($version), $version);
	}

	public function find()
	{
		$found = ci('package_migration')->find_migrations();

		if (count($found)) {
			$highest = ci('package_migration')->get_version();

			foreach ($found as $num=>$value) {
				$text = substr_replace(basename($value), ' ', 3, 1);

				if ((int)$num <= $highest) {
					ci('console')->tab()->dim()->text('√ '.$text);
				} else {
					ci('console')->tab()->info('> '.$text);
				}
			}
		} else {
			ci('console')->tab()->dim()->text('≈ No migrations found.');
		}
	}

	public function packages($packages)
	{
		/* look in each folder */
		foreach ($packages as $package) {
			ci('console')->text(str_replace(ROOTPATH, '', $package));
		}
	}

	public function set_path($package, $folder)
	{
		$this->package = str_replace(ROOTPATH, '', $package);

		$path = '/'.trim('/'.trim($this->package, '/').'/'.trim($folder, '/'), '/');

		ci('package_migration')->set_path($path);

		ci('console')->hr()->text('Migration path switched to '.$path)->hr();

		return $this;
	}

	public function get_path()
	{
		return $this->package.$this->folder;
	}

	public function create(string $description, string $up, string $down, string $template)
	{
		if ($filename = ci('package_migration')->create($description, $up, $down, $template)) {
			ci('console')->hr()->text(str_replace(ROOTPATH, '', $filename).' created.')->hr();
		}
	}

	protected function process_mixed($mixed, $error_text)
	{
		if ($mixed === true) {
			ci('console')->text('No migrations found.');
		} elseif ($mixed === false) {
			ci('console')->error(ci('package_migration')->error_string());
		} else {
			ci('console')->text('Version changed to '.$mixed.'.');
		}
	}

	public function create_folder(string $folder,int $mode=0777) : bool
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
