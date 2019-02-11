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
 * LightnCandy Handlebars Helper
 *
 * Provide Handlebars.js Parsing to CodeIgniter
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0.0
 * @filesource
 *
 * @uses # LightnCandy\LightnCandy - https://github.com/zordius/lightncandy
 *
 * @config debug boolean
 * @config flags integer
 * @config template extension string
 * @config plugin extension string
 * @config cache path string absolute path
 * @config partials path string | array of paths
 * @config plugin path string | array of paths
 *
 */

use LightnCandy\LightnCandy;

class Handlebars_helper {
	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $debug = false;

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $flags;

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $template_extension = 'hbs';

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $default_flags = LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_BESTPERFORMANCE | LightnCandy::FLAG_NAMEDARG | LightnCandy::FLAG_ADVARNAME | LightnCandy::FLAG_ERROR_LOG;

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $compiled_path = CACHEPATH;

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $plugin_regex = '(.*)\.plugin\.php';

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $plugins = [];

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $plugins_paths = [];

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $partials_paths = [];

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $partial_files = [];

	/**
	 * errors configuration array
	 *
	 * @var {{}}
	 */
	protected $everything_loaded = false;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 */
	public function __construct()
	{
		$this
			->debug(config('handlebars.debug',(ENVIRONMENT == 'development')))
			->flags(config('handlebars.flags',$this->default_flags))
			->template_extension(config('handlebars.template extension',$this->template_extension))
			->plugin_regex(config('handlebars.plugin regex',$this->plugin_regex))
			->compiled_path(config('handlebars.cache path',$this->compiled_path))
			->add_partial_path(config('handlebars.partials path','/application/views/handlebars'))
			->add_plugin_path(config('handlebars.plugin path',str_replace(ROOTPATH,'',__DIR__.'/plugins')));
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param $plugin_path
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function add_plugin_path($plugin_path) : Handlebars_helper
	{
		if (is_array($plugin_path)) {
			foreach ($plugin_path as $pp) {
				$this->add_plugin_path($pp);
			}

			return $this;
		}

		$path = '/'.trim($plugin_path,'/');

		$this->plugins_paths[$path] = $path;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param $partials_path
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function add_partial_path($partials_path) : Handlebars_helper
	{
		if (is_array($partials_path)) {
			foreach ($partials_path as $pp) {
				$this->add_partial_path($pp);
			}

			return $this;
		}

		$path = '/'.trim($partials_path,'/');

		$this->partials_path[$path] = $path;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $template_extension
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function template_extension(string $template_extension) : Handlebars_helper
	{
		$this->template_extension = '.'.trim($template_extension,'.');

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $plugin_regex
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function plugin_regex(string $plugin_regex) : Handlebars_helper
	{
		$this->plugin_extension = $plugin_regex;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param int $flags
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function flags(int $flags) : Handlebars_helper
	{
		$this->flags = $flags;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param bool $bool true
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function debug(bool $bool=true) : Handlebars_helper
	{
		$this->debug = $bool;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $compiled_path
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function compiled_path(string $compiled_path) : Handlebars_helper
	{
		$this->compiled_path = '/'.trim($compiled_path,'/');

		/* testing is writable in compile since we don't actually need to "write" when we change this */
		if (!realpath($this->compiled_path)) {
			mkdir($this->compiled_path,0777,true);

			if (!realpath($this->compiled_path)) {
				throw new \Exception(__METHOD__.' Cannot locate compiled handlebars folder "'.$this->compiled_path.'"');
			}
		}

		return $this;
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
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	protected function load_plugins() : Handlebars_helper
	{
		$cache_file_path = CACHEPATH.'/handlebars.plugins.php';

		if ($this->debug || !file_exists($cache_file_path)) {
			$content = '';

			/* attach the plugins */
			foreach ($this->plugins_paths as $plugin_path) {
				$directory = new RecursiveDirectoryIterator(ROOTPATH.$plugin_path);
				$flattened = new RecursiveIteratorIterator($directory);
				$plugin_files = new RegexIterator($flattened,'#^'.$this->plugin_regex.'$#i',RecursiveRegexIterator::GET_MATCH);				
				
				foreach ($plugin_files as $plugin_file) {
					$php = trim(file_get_contents($plugin_file[0]));

					if (substr($php,0,5) == '<?php') {
						$php = substr($php,5);
					}

					if (substr($php,0,2) == '<?') {
						$php = substr($php,2);
					}

					if (substr($php,-2) == '?>') {
						$php = substr($php,0,-2);
					}

					$content .= $php;
				}
			}

			/* save it */
			atomic_file_put_contents($cache_file_path,'<?php '.$content);
		}

		$plugin = null;

		/* include the cache file */
		include $cache_file_path;

		if (is_array($plugin)) {
			/* merge with what might already be in there */
			$this->plugins = $plugin + $this->plugins;
		}

		return $this;
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
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	protected function load_partials() : Handlebars_helper
	{
		$cache_file_path = CACHEPATH.'/handlebars.partials.php';

		if ($this->debug || !file_exists($cache_file_path)) {
			$partials = [];

			foreach ($this->partials_path as $path) {
				if (!file_exists(ROOTPATH.$path)) {
					throw new \Exception('Partials path "'.$path.'" not found.');
				}

				$partial_files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOTPATH.$path));

				foreach ($partial_files as $partial_file) {
					$partial_file = $partial_file->getPathName();

					if (!is_dir($partial_file)) {
						$partials[strtolower(trim(substr(str_replace(ROOTPATH.$path,'',$partial_file),0,-strlen($this->template_extension)),'/'))] = $partial_file;
					}
				}
			}

			/* save it */
			atomic_file_put_contents($cache_file_path,'<?php return '.var_export($partials,true).';');
		}

		$this->partial_files = include $cache_file_path;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param callable $plugin
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function add_plugin(string $name,callable $plugin) : Handlebars_helper
	{
		$this->plugins[strtolower($name)] = $plugin;

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param array $plugins
	 *
	 * @throws
	 * @return Handlebars_helper
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function add_plugins(array $plugins) : Handlebars_helper
	{
		foreach ($plugins as $name=>$plugin) {
			$this->add_plugin($name,$plugin);
		}

		return $this;
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $template_file
	 * @param array $data []
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function parse(string $template_file, array $data=[]) : string
	{
		if (!file_exists($template_file)) {
			throw new \Exception('Template "'.$template_file.'" not found ');
		}

		return $this->parse_string(file_get_contents($template_file),$data,$template_file);
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param string $template_string
	 * @param array $data []
	 * @param string $type string
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function parse_string(string $template_string, array $data=[],string $type='string') : string
	{
		$compiled_filename = $this->compiled_path.'/'.md5($template_string).'.php';

		/* delete the compiled file if we are in debug mode */
		if ($this->debug) {
			if (file_exists($compiled_filename)) {
				unlink($compiled_filename);
			}
		}

		/* compile if it's not there */
		if (!file_exists($compiled_filename)) {
			if (!$this->compile($compiled_filename,$template_string,$type)) {
				throw new \Exception('Error compiling your handlebars template');
			}
		}

		$template_php = include $compiled_filename;

		/* send data into the magic void... */
		return $template_php($data);
	}

	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $compiled_filename
	 * @param string $template
	 * @param string $type
	 *
	 * @throws
	 * @return bool
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	protected function compile(string $compiled_filename,string $template, string $type) : bool
	{
		if (!is_writable(dirname($compiled_filename))) {
			throw new \Exception(__METHOD__.' Cannot write to folder "'.$this->compiled_path.'"');
		}

		$compiled_php = $this->_compile($template,$type);

		/* compile it into a php magic! */
		return (!empty($compiled_php)) ? (bool)file_put_contents($compiled_filename,'<?php '.$compiled_php.'?>') : false;
	}
	
	/**
	 *
	 * Description Here
	 *
	 * @access protected
	 *
	 * @param string $template
	 * @param string $type
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	protected function _compile(string $template,string $type) : string
	{
		/* at first compile load everything */
		if (!$this->everything_loaded) {
			$this->load_plugins();
			$this->load_partials();

			$this->everything_loaded = true;
		}

		$options = [
			'flags'=>$this->flags,
			'helpers'=>$this->plugins,
			'renderex'=>'/* compiled '.str_replace(ROOTPATH,'',$type).' @ '.date('Y-m-d h:i:s e').' */', /* added to compiled PHP */
			'partialresolver'=>function($context,$partial_name) { /* include / partial handler */
				$key = trim(strtolower($partial_name),'/');

				if (!isset($this->partial_files[$key])) {
					throw new \Exception('Partial "'.$key.'" not found ');
				}

				return file_get_contents($this->partial_files[$key]);
			},
		];

		/* compile it into a php magic! */
		return LightnCandy::compile($template,$options);
	}

	/**
	 *
	 * Description Here
	 *
	 * @access public
	 *
	 * @param $root
	 *
	 * @throws
	 * @return 
	 *
	 * #### Example
	 * ```
	 *
	 * ```
	 */
	public function compile_all($root)
	{
		if (empty(trim($this->template_extension,'.'))) {
			throw new \Exception('Template extension is empty.');
		}
	
		$templates = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

		foreach ($templates as $template) {
			if (!is_dir($template)) {
				$fileinfo = pathinfo($template);

				if ($fileinfo['extension'] === trim($this->template_extension,'.')) {
					$template_string = file_get_contents($template);
					
					$compiled_filename = $this->compiled_path.'/'.md5($template_string).'.php';
			
					if (file_exists($compiled_filename)) {
						unlink($compiled_filename);
					}
			
					if (!$this->compile($compiled_filename,$template_string,$template)) {
						echo 'Error compiling handlebars template "'.$template.'".'.PHP_EOL;
					}
				}
			}
		}
	}

} /* end class */
