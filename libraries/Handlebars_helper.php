<?php

use LightnCandy\LightnCandy;

class Handlebars_helper {
	protected $debug = false;
	protected $flags;
	protected $compiled_path;
	protected $plugins = [];
	protected $plugins_paths = [];
	protected $partials_paths = [];
	protected $template_extension;
	protected $plugin_extension;
	protected $plugin_path;
	protected $partial_array = [];
	protected $everything_loaded = false;

	public function __construct()
	{
		$this
			->debug(config('handlebars.debug',true))
			->flags(config('handlebars.options',LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_BESTPERFORMANCE | LightnCandy::FLAG_NAMEDARG | LightnCandy::FLAG_ADVARNAME | LightnCandy::FLAG_ERROR_LOG))
			->template_extension(config('handlebars.template extension','html'))
			->plugin_extension(config('handlebars.plugin extension','plugin.php'))
			->compiled_path(config('handlebars.cache path',CACHEPATH))
			->add_partial_path(config('handlebars.partials path','/application/views/handlebars'))
			->add_plugin_path(config('handlebars.plugin path',str_replace(ROOTPATH,'',__DIR__.'/plugins')));
	}

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

	public function template_extension(string $template_extension) : Handlebars_helper
	{
		$this->template_extension = '.'.trim($template_extension,'.');

		return $this;
	}

	public function plugin_extension(string $plugin_extension) : Handlebars_helper
	{
		$this->plugin_extension = '.'.trim($plugin_extension,'.');

		return $this;
	}

	public function flags(int $flags) : Handlebars_helper
	{
		$this->flags = $flags;

		return $this;
	}

	public function debug(bool $bool=true) : Handlebars_helper
	{
		$this->debug = $bool;

		return $this;
	}

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

	protected function load_plugins() : Handlebars_helper
	{
		$cache_file_path = CACHEPATH.'/handlebars.plugins.php';

		if (ENVIRONMENT == 'development' || !file_exists($cache_file_path)) {
			$content = '';

			/* attach the plugins */
			foreach ($this->plugins_paths as $plugin_path) {
				foreach (glob(ROOTPATH.'/'.$plugin_path.'/*'.$this->plugin_extension) as $plugin_file) {
					$php = trim(file_get_contents($plugin_file));

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
			$this->plugins = $plugin;
		}

		return $this;
	}

	protected function load_partials() : Handlebars_helper
	{
		$cache_file_path = CACHEPATH.'/handlebars.partials.php';

		if (ENVIRONMENT == 'development' || !file_exists($cache_file_path)) {
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

		$this->partial_array = include $cache_file_path;

		return $this;
	}

	public function add_plugin(string $name,callable $plugin) : Handlebars_helper
	{
		$this->plugins[strtolower($name)] = $plugin;

		return $this;
	}

	public function add_plugins(array $plugins) : Handlebars_helper
	{
		foreach ($plugins as $name=>$plugin) {
			$this->add_plugin($name,$plugin);
		}

		return $this;
	}

	public function parse(string $template_file, array $data=[]) : string
	{
		if (!file_exists($template_file)) {
			throw new \Exception('Template "'.$template_file.'" not found ');
		}

		return $this->parse_string(file_get_contents($template_file),$data);
	}

	public function parse_string(string $template_string, array $data=[]) : string
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
			if (!$this->compile($compiled_filename,$template_string)) {
				throw new \Exception('Error compiling your handlebars template');
			}
		}

		$template_php = include $compiled_filename;

		/* send data into the magic void... */
		return $template_php($data);
	}

	/* template file path */
	protected function compile(string $compiled_filename,string $template) : bool
	{
		if (!is_writable(dirname($compiled_filename))) {
			throw new \Exception(__METHOD__.' Cannot write to folder "'.$this->compiled_path.'"');
		}

		if (!$this->everything_loaded) {
			$this->load_plugins();
			$this->load_partials();

			$this->everything_loaded = true;
		}

		$options = [
			'flags'=>$this->flags,
			'helpers'=>$this->plugins,
			'renderex'=>'/* compiled @ '.date('Y-m-d h:i:s e').' */', /* added to compiled PHP */
			'partialresolver'=>function($context,$partial_name) { /* include / partial handler */
				$key = trim(strtolower($partial_name),'/');

				if (!isset($this->partial_array[$key])) {
					throw new \Exception('Partial "'.$key.'" not found ');
				}

				return file_get_contents($this->partial_array[$key]);
			},
		];

		/* compile it into a php magic! */
		return ($compiled_php = LightnCandy::compile($template,$options)) ? (bool)file_put_contents($compiled_filename,'<?php '.$compiled_php.'?>') : false;
	}

} /* end class */
