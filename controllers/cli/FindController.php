<?php

class FindController extends MY_Controller
{
	public function indexCliAction()
	{
		$console = new League\CLImate\CLImate;
		$console->green('Syntax');
		$console->yellow('php public/index.php cli/find/file database_model');
	}

	/**
	 *	Search your application for files.
	 */
	public function fileCliAction($filename=null)
	{
		$console = new League\CLImate\CLImate;

		if (!isset($_SERVER['argv'][2])) {
			$console->error('Please provide a filename to search for.');
			exit(1);
		}

		$filename = $_SERVER['argv'][2];

		$console->info('Looking for "'.$filename.'"');

		$autoload = load_config('autoload', 'autoload');

		$autoload['packages'][] = APPPATH;
		$autoload['packages'][] = BASEPATH;

		foreach ($autoload['packages'] as $package) {
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($package, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF));
	
			foreach ($files as $name=>$file) {
				if (!$file->isDot()) {
					if (substr($file->getFilename(), 0, 1) != '.') {
						$re = '/'.preg_quote($filename).'/mi';
		
						if (preg_match_all($re, $file->getFilename(), $matches, PREG_SET_ORDER, 0)) {
							$parts = pathinfo(str_replace(ROOTPATH, '', $name));

							$styled = str_ireplace($matches[0][0], '<cyan>'.$matches[0][0].'</cyan>', $parts['basename']);
							
							$console->out($parts['dirname'].'/'.$styled);
						}
					}
				}
			}
		}
	}
} /* end class */
