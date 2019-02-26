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
 */

class ShowController extends MY_Controller
{
	protected $docblock_regx = '/(?s)(\/\*(?:(?!\*\/).)+\*\/)\s+/';

	public function helpCliAction() : void
	{
		ci('console')
			->h1('Help')
			->help_command('Display this help.',['show','show/help'])
			->help_command('Show all registered package paths.','show/packages')
			->help_command('Show all registered pear plugins.','show/pear')
			->help_command('Show all registered validations.','show/validate')
			->help_command('Show all registered filters.','show/filter')
			->help_command('Show all registered models.','show/models')
			->help_command('Show all registered controllers.','show/controllers')
			->help_command('Show all registered controller traits.','show/controller-traits')
			->help_command('Show all registered middleware.','show/middleware')
			->h2('* -p suffix will show the files path.')
			->h2('* -d suffix will show the files details.')
			->br(2);
	}

	/**
	 * Show all registered packages. Use optional -p to show path instead of help
	 */
	public function packagesCliAction($arg='') : void
	{
		$autoload = load_config('autoload', 'autoload');

		$this->search_paths = explode(PATH_SEPARATOR, rtrim(APPPATH, '/').PATH_SEPARATOR.implode(PATH_SEPARATOR, $autoload['packages']));

		ci('console')->h1('Registered Packages');

		foreach ($this->search_paths as $path) {
			ci('console')->out($path);
		}

		ci('console')->br();
	}

	/**
	 * Show all registered validation classes. Use optional -p to show path instead of help
	 */
	public function validateCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/libraries/validations/Validate_(.*).php');
	}

	/**
	 * Show all registered pear classes. Use optional -p to show path instead of help
	 */
	public function pearCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/libraries/pear_plugins/Pear_(.*).php');
	}

	/**
	 * Show all registered filter classes. Use optional -p to show path instead of help
	 */
	public function filterCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/libraries/filters/Filter_(.*).php');
	}

	/**
	 * Show all registered models classes. Use optional -p to show path instead of help
	 */
	public function modelsCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/models/(.*)_model.php');
	}

	/**
	 * Show all registered libraries classes. Use optional -p to show path instead of help
	 */
	public function controllersCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/controllers/(.*)Controller.php');
	}

	/**
	 * Show all registered controller traits. Use optional -p to show path instead of help
	 */
	public function controller_traitsCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/controllers/traits/(.*)_trait.php');
	}

	/**
	 * Show all registered middleware. Use optional -p to show path instead of help
	 */
	public function middlewareCliAction($arg='') : void
	{
		$this->options($arg)->loop_over('(.*)/middleware/(.*)Middleware.php');
	}

	/* protected */

	protected function options(string $arg) : ShowController
	{
		switch (strtolower($arg)) {
			case '_p':
			case '-p':
				ci('console')->h1('Show Path');
				$this->show = 'path';
			break;
			case '-d':
			case '_d':
				ci('console')->h1('Show Details (@details)');
				$this->show = 'details';
			break;
			default:
				ci('console')->h1('Show Help (@help)');
				$this->show = 'help';
		}

		return $this;
	}

	protected function loop_over(string $regex) : void
	{
		foreach (orange_locator::classes() as $name=>$path) {
			if (preg_match('#^'.$regex.'$#i',$path, $matches, PREG_OFFSET_CAPTURE)) {
				ci('console')->info(basename($matches[0][0],'.php'));

				switch ($this->show) {
					case 'path':
						$this->get_path($matches[0][0]);
					break;
					case 'details':
						$this->get_between_tags($matches[0][0],'@details');
					break;
					case 'help':
						$this->get_tag($matches[0][0],'@help');
					break;
				}
			}
		}

		ci('console')->br();
	}

	protected function get_path(string $filepath) : void
	{
		ci('console')->out(chr(9).str_replace(ROOTPATH, '',$filepath))->br();
	}

	protected function get_between_tags(string $filepath,string $tag) : void
	{
		if (preg_match_all('/'.$tag.'(.*)'.$tag.'/is', file_get_contents($filepath), $matches, PREG_SET_ORDER, 0)) {
			$this->echo_block($matches);
		} else {
			ci('console')->out(chr(9).' --');
		}

		ci('console')->br();
	}

	protected function get_tag(string $filepath,string $tag) : void
	{
		if (preg_match_all('/'.$tag.' (.*)/i', file_get_contents($filepath), $matches, PREG_SET_ORDER, 0)) {
			$this->echo_block($matches);
		} else {
			ci('console')->out(chr(9).' --');
		}

		ci('console')->br();
	}

	protected function echo_block(array $matches) : void
	{
		foreach ($matches as $m) {
			foreach (explode(PHP_EOL,$m[1]) as $line) {
				$line = trim($line);
				if (!empty($line)) {
					ci('console')->out(chr(9).trim($line));
				}
			}
		}
	}

} /* end class */
