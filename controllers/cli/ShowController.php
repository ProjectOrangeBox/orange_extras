<?php

class ShowController extends MY_Controller
{
	protected $files = [];
	protected $search_paths = [];
	protected $show;

	protected $tag = 'show';

	public function __construct()
	{
		parent::__construct();

		$autoload = load_config('autoload', 'autoload');

		$this->search_paths = explode(PATH_SEPARATOR, rtrim(APPPATH, '/').PATH_SEPARATOR.implode(PATH_SEPARATOR, $autoload['packages']));
	}

	public function helpCliAction()
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
	public function packagesCliAction($arg=null)
	{
		foreach ($this->search_paths as $path) {
			ci('console')->out($path);
		}
	}

	/**
	 * Show all registered validation classes. Use optional -p to show path instead of help
	 */
	public function validateCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/libraries/validations/Validate_(.*).php');
	}

	/**
	 * Show all registered pear classes. Use optional -p to show path instead of help
	 */
	public function pearCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/libraries/pear_plugins/Pear_(.*).php');
	}

	/**
	 * Show all registered filter classes. Use optional -p to show path instead of help
	 */
	public function filterCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/libraries/filters/Filter_(.*).php');
	}

	/**
	 * Show all registered models classes. Use optional -p to show path instead of help
	 */
	public function modelsCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/models/(.*)_model.php');
	}

	/**
	 * Show all registered libraries classes. Use optional -p to show path instead of help
	 */
	public function controllersCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/controllers/(.*)Controller.php');
	}

	/**
	 * Show all registered controller traits. Use optional -p to show path instead of help
	 */
	public function controller_traitsCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/controllers/traits/(.*)_trait.php');
	}

	/**
	 * Show all registered middleware. Use optional -p to show path instead of help
	 */
	public function middlewareCliAction($arg=null)
	{
		$this->options($arg)->loop_over('(.*)/middleware/(.*)Middleware.php');
	}

	/* protected */

	protected function options($arg)
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

	protected function loop_over($regex)
	{
		ci('console')->h2('Searching.');

		foreach ($this->search_paths as $path) {
			switch ($this->show) {
				case 'path':
					$this->search($path,$regex,'get_path');
				break;
				case 'details':
					$this->search($path,$regex,'get_between_tags','@details');
				break;
				case 'help':
					$this->search($path,$regex,'get_tag','@help');
				break;
			}
		}

		ci('console')->br(2);
	}

	protected function get_path($match,$dummy) {
		ci('console')->out(chr(9).str_replace(ROOTPATH, '',$match[0]))->br();
	}

	protected function get_between_tags($match, $tag)
	{
		if (preg_match_all('/'.$tag.'(.*)'.$tag.'/is', file_get_contents($match[0]), $matches, PREG_SET_ORDER, 0)) {
			foreach ($matches as $m) {
				ci('console')->out(chr(9).trim($m[1]));
			}
		} else {
			ci('console')->out(chr(9).' --');
		}

		ci('console')->br();
	}

	protected function get_tag($match, $tag)
	{
		if (preg_match_all('/'.$tag.' (.*)/i', file_get_contents($match[0]), $matches, PREG_SET_ORDER, 0)) {
			foreach ($matches as $m) {
				ci('console')->out(chr(9).trim($m[1]));
			}
		} else {
			ci('console')->out(chr(9).' --');
		}

		ci('console')->br();
	}

	protected function search($path,$regex,$callback,$arg1=null)
	{
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)), '#^'.$regex.'$#i', RecursiveRegexIterator::GET_MATCH) as $match) {
			ci('console')->info(basename($match[0],'.php'));
			$this->$callback($match,$arg1);
		}
	}

} /* end class */
