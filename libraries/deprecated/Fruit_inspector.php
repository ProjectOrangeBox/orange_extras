<?php

class Fruit_inspector
{
	protected $controllers = [];
	protected $current_package = '';

	/* internal */
	public function get_controllers_methods($all=false)
	{
		if ($all) {
			$this->globr(ROOTPATH, 'Controller.php');
		} else {
			$autoload = [];

			include ROOTPATH.'/application/config/autoload.php';

			/* add application folder */
			$autoload['packages'][] = ROOTPATH.'/application';

			foreach ($autoload['packages'] as $path) {
				$this->current_package = str_replace(ROOTPATH, '', $path);

				$this->globr($path, 'Controller.php');
			}
		}

		return $this->controllers;
	}

	public function inspect($path, $name='class')
	{
		$inspection = [];

		$original_class_name = basename($path, '.php');

		/* need to make a dummy class so names don't conflict */
		$new_class_file = $this->make_dummy_class_file($path);
		
		if ($new_class_file) {
			$new_class_name = basename($new_class_file, '.php');
	
			/* now we can reflect */
			include $new_class_file;
	
			/* loaded so remove the "fake" file */
			unlink($new_class_file);
	
			$reflect_class = new ReflectionClass($new_class_name);
	
			/* go up the tree finding parents */
			$parent_class = $reflect_class;
			$class_parents = [];
	
			while ($parent = $parent_class->getParentClass()) {
				$inspection[$name]['parents'][] = $parent->getName();
	
				$parent_class = $parent;
			}
	
			$inspection[$name]['file'] = str_replace(ROOTPATH, '', $path);
			$inspection[$name]['controller'] = $original_class_name;
			$inspection[$name]['short_controller'] = substr($original_class_name, 0, -10);
			$inspection[$name]['package'] = $this->current_package;
			$inspection[$name]['url'] = substr(strtolower(substr($path, strpos($path, '/controllers') + 12)), 0, -14);
			$inspection[$name]['comments'] = trim($reflect_class->getDocComment());
			
			$filter_methods = ($name == 'controller') ? 'Action' : '';
			
			$inspection['methods'] = $this->get_methods($reflect_class, $filter_methods, $original_class_name);
		}

		return $inspection;
	}

	protected function globr($searchDirectory, $searchPattern)
	{
		foreach (glob(escapeshellcmd($searchDirectory).'/*') as $folderitem) {
			if (is_dir($folderitem)) {
				$this->globr($folderitem, $searchPattern);
			} elseif (substr($folderitem, -strlen($searchPattern)) == $searchPattern) {
				$this->controllers[$this->current_package][str_replace([ROOTPATH,$this->current_package], '', $folderitem)] = $this->add($folderitem);
			}
		}
	}

	protected function add($path, $name='controller')
	{
		/* is it a Orange / CI Controller? */
		if (strpos(file_get_contents($path), 'MY_Controller') === false) {
			/* no */
			return;
		}

		return $this->inspect($path, $name);
	}

	protected function get_methods($reflect_class, $filter_methods, $original_class_name)
	{
		$inspection = [];

		$methods = $reflect_class->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($methods as $idx=>$ref_method) {
			$raw_method = $ref_method->name;
			
			$add = true;
			
			if (!empty($filter_methods)) {
				if (substr($raw_method, -strlen($filter_methods)) != $filter_methods) {
					$add = false;
				}
			}
			
			if ($add) {
				$pieces = preg_split('/(?=[A-Z])/', $raw_method);

				$request_method = (count($pieces) == 3) ? strtolower($pieces[1]) : 'get';

				$method_parent = $ref_method->getDeclaringClass();
				
				$method_parent = (substr($method_parent->name, 0, 5) == 'Fake_') ? $original_class_name : $method_parent->name;

				$inspection[$raw_method] = [
					'comments'=>trim($reflect_class->getMethod($raw_method)->getDocComment()),
					'request_method'=>$request_method,
					'method'=>$raw_method,
					'action'=>$pieces[0],
					'parent'=>$method_parent,
				];
			}
		}

		return $inspection;
	}

	protected function get_properties($prop)
	{
		$p = [];

		foreach ((array)$prop as $pp) {
			$p[$pp['name']] = $pp['name'];
		}

		return $p;
	}

	protected function make_dummy_class_file($real_path)
	{
		$new_class_filepath = false;
	
		if (substr(basename($real_path), 0, 1) != '.') {
			$new_class_name = 'Fake_'.md5(uniqid('', true));
			$new_class_filepath = CACHEPATH.'/'.$new_class_name.'.php';
			$old_class_name = basename($real_path, '.php');
	
			$file_content = file_get_contents($real_path);
	
			if (strpos($file_content, chr(10).'class '.$old_class_name.' ') === false) {
				die('Could not locate class name "'.$old_class_name.'" in "'.$real_path.'"'.chr(10));
			}
	
			file_put_contents($new_class_filepath, str_replace(chr(10).'class '.$old_class_name.' ', chr(10).'class '.$new_class_name.' ', $file_content));
		}

		return $new_class_filepath;
	}
	
	public static function clean_comments($input, $console=null)
	{
		$comments = '';

		if (strlen($input)) {
			$lines = explode(PHP_EOL, trim(substr($input, 3, -2)));
			
			foreach ($lines as $l) {
				$comments .= '  '.trim($l);
			}
			
			if ($console) {
				$console->e($comments);
			}
		}
		
		return $comments;
	}
}
