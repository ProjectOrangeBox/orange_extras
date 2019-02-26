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

class CreateController extends MY_Controller
{
	protected $package_folder;

	public function helpCliAction()
	{
		ci('console')
			->h1('Help')
			->help_command(['Create a Package.','package folder path & url must be included.'],'create/package foldername/package_name /admin/details')
			->br(2);
	}

	/**
	 *	Generate a generic package
	 *
	 *	php index.php cli/create/package package_folder/package_name url_path
	 */
	public function packageCliAction()
	{
		$package = trim(ci('console')->get_arg(1,true,'package path'),'/');
		$folder = trim(ci('console')->get_arg(2,true,'controller url'),'/');

		$this->package_folder = ROOTPATH.'/packages/'.$package;

		/* make the package folder */
		if (!file_exists($this->package_folder)) {
			$umask = umask();
			umask(0);
			if (!@mkdir($this->package_folder,0777,true)) {
				ci('console')->error('Could not make the package path "'.$this->package_folder.'".');
			}
			umask($umask);
		}

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

		$ut_name = str_replace(' ','',ucwords(str_replace('/',' ',dirname($folder).' '.ucfirst($controller_filename))));

		$this->make('support/'.$ut_name.'Test.php','unittest',$data + ['ut_name'=>$ut_name]);
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
			$template_file = realpath(__DIR__.'/../../support/templates/'.$template.'.tpl');

			if (!$template_file) {
				ci('console')->error('Template file "'.$template_file.'" not found.');
			}

			$template = file_get_contents($template_file);

			foreach ($data as $key=>$val) {
				$template = str_replace('{'.$key.'}', $val, $template);
			}

			$path = $this->package_folder.'/'.$name;

			ci('console')->out('Using Template "'.str_replace(ROOTPATH,'',$template_file).'" to create "'.str_replace(ROOTPATH,'',$path).'".');

			@mkdir(dirname($path), 0775, true);
			file_put_contents($path, $template);
			@chmod($path, 0775);
		}
	}
}
