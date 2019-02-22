<?php

class FindController extends MY_Controller
{
	public function helpCliAction()
	{
		ci('console')
			->h1('Help')
			->help_command('Find PHP files matching entered search parse.','find/file database_model')
			->br(2);
	}

	/**
	 *	Search your application for files.
	 */
	public function fileCliAction($filename=null)
	{
		if (!isset($_SERVER['argv'][2])) {
			ci('console')->error('Please provide a filename search term.');
		}

		$filename = $_SERVER['argv'][2];

		ci('console')->h1('Looking for "'.$filename.'"');

		$autoload = load_config('autoload', 'autoload');

		$autoload['packages'][] = APPPATH;
		$autoload['packages'][] = BASEPATH;

		foreach ($autoload['packages'] as $package) {
			foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($package)), '/(.*)(?P<search>'.preg_quote($filename).')(.*)/mi', RecursiveRegexIterator::GET_MATCH) as $match) {
				if (strpos($match[0],'/.') === false) {
					ci('console')->out(str_replace($match['search'],'<cyan>'.$match['search'].'</cyan>',str_replace(ROOTPATH, '', $match[0])));
				}
			}
		}
	}
} /* end class */
