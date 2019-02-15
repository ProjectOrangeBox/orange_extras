<?php

class CreateController extends MY_Controller
{
	protected $package_folder;
	protected $console;

	public function indexCliAction()
	{
		$console = new League\CLImate\CLImate;
		$console->green('Syntax');
		$console->info('php public/index.php cli/create/package {location to place package} {browser URL}');
		$console->yellow('php public/index.php cli/create/package foldername/packagename /item/details');
	}

	/**
	 *	Generate a generic package
	 *
	 *	php index.php cli/create/package package_folder/package_name url_path
	 */
	public function packageCliAction()
	{
		$this->console = new League\CLImate\CLImate;
		
		$package = (isset($_SERVER['argv'][2])) ? trim($_SERVER['argv'][2], '/') : '';
		$folder =  (isset($_SERVER['argv'][3])) ? trim($_SERVER['argv'][3], '/') : '';

		if (empty($package)) {
			$this->console->error('Please provide a package path');
			exit(1);
		}

		if (empty($folder)) {
			$this->console->error('Please provide the controller url');
			exit(1);
		}

		$this->package_folder = ROOTPATH.'/packages/'.$package;

		if (!is_writable(dirname($this->package_folder))) {
			$this->console->error(dirname($this->package_folder).' is not writable.');
		}

		/* make the package folder */
		@mkdir($this->package_folder, 0775, true);
		@chmod($this->package_folder, 0775);

		$controller_filename = basename($folder);

		ci('load')->helper('inflector');

		$data = [
			'controller_name'=>$controller_filename,
			'ucontroller_name'=>ucfirst($controller_filename),
			'controller_path'=>'/'.$folder,
			'dataset_model'=>ucfirst($controller_filename).'_model',
			'singular'=>singular(humanize($controller_filename)),
			'plural'=>plural(humanize($controller_filename)),
		];

		$this->make('controllers/'.dirname($folder).'/'.ucfirst($controller_filename).'Controller.php', 'controller', $data);
		$this->make('models/'.ucfirst($controller_filename).'_model.php', 'model', $data);
		$this->make('views/'.$folder.'/index.php', 'index', $data);
		$this->make('views/'.$folder.'/details.php', 'details', $data);

		$this->make('libraries');
		$this->make('support');
		$this->make('support/migration');
		$this->make('support/migration/001_init.php', '001_init', $data);
	}

	protected function make($name, $template=null, $data=[])
	{
		$name = ltrim($name, '/');
	
		if (!$template) {
			@mkdir($this->package_folder.'/'.$name, 0775, true);
			@chmod($this->package_folder.'/'.$name, 0775);
		} else {
			$template_file = realpath(__DIR__.'/../../support/templates/'.$template.'.php');
			
			if (!$template_file) {
				$this->console->error('Template file "'.$template_file.'" not found.');
			}
		
			$template = file_get_contents($template_file);
	
			foreach ($data as $key=>$val) {
				$template = str_replace('{'.$key.'}', $val, $template);
			}
	
			$path = $this->package_folder.'/'.$name;

			$this->console->info('Using Template "'.$template_file.'" to create "'.$path.'".');
	
			@mkdir(dirname($path), 0775, true);
			file_put_contents($path, $template);
			@chmod($path, 0775);
		}
	}
}
