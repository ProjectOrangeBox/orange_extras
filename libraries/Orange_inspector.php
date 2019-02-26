<?php

class Orange_inspector
{
	protected $details = [];

	protected $new_class_name;
	protected $new_class_filepath;
	protected $fake_class_name;
	protected $real_class_name;
	protected $protect = false;

	public function find(string $path, string $regular_expression = '.+\.php') : Orange_inspector
	{
		$directory = new RecursiveDirectoryIterator($path);
		$it = new RecursiveIteratorIterator($directory);
		$matches = new RegexIterator($it,'/^'.$regular_expression.'$/i',RecursiveRegexIterator::GET_MATCH);

		foreach ($matches as $filepath) {
			$this->inspect($filepath[0]);
		}

		return $this;
	}

	public function inspect(string $filepath) : Orange_inspector
	{
		$details = $this->class($filepath);

		if ($details) {
			$this->details[$filepath] = $details;
		}

		return $this;
	}

	public function details() : array
	{
		return $this->details;
	}

	public function protect(bool $fakes) : Orange_inspector
	{
		$this->protect = $fakes;

		return $this;
	}

	/**
	 * protected
	 */

	protected function class($filepath) {
		$details = false;

		if ($this->protect) {
			if ($this->make_dummy_class_file($filepath)) {
				$details = $this->reflect($this->new_class_name);
			} else {
				throw new \Exception('Could not make protected class for '.$filepath.' .',500);
			}
		} else {
			require $filepath;

			$class_name = $this->find_first_class_name(file_get_contents($filepath));

			$details = $this->reflect($class_name[2]);
		}

		return $details;
	}

	protected function reflect($class_name)
	{
		/* class */
		$reflectionClass = new ReflectionClass($class_name);

		$this->real_class_name = $this->fake_class_name = $reflectionClass->getName();

		/* if we are using protected "faking" then remote the prefix */
		if ($this->protect) {
			$this->real_class_name = substr($this->real_class_name,33);
		}

		$details['name'] = $this->real_class_name;
		$details['doc comment'] = $reflectionClass->getDocComment();
		$details['namespace'] = $reflectionClass->getNamespaceName();
		$details['constants'] = $reflectionClass->getConstants();
		$details['interfaces'] = $reflectionClass->getInterfaces();
		$details['extends'] = $reflectionClass->getParentClass()->name;
		$details['traits'] = $reflectionClass->getTraitNames();
		$details['final'] = $reflectionClass->isFinal();
		$details['trait'] = $reflectionClass->isTrait();

		/* class properties */
		$properties = $reflectionClass->getProperties();

		$defaults = $reflectionClass->getDefaultProperties();

		foreach ($properties as $property) {
			$default_type = gettype($defaults[$property->getName()]);

			$default_type = ($default_type == 'NULL') ? null : $default_type;

			$details['properties'][$property->getName()] = [
				'doc comment'=>$property->getDocComment(),
				'name'=>$property->getName(),
				'private'=>$property->isPrivate(),
				'public'=>$property->isPublic(),
				'protected'=>$property->isProtected(),
				'static'=>$property->isStatic(),
				'default'=>$defaults[$property->getName()],
				'default type'=>$default_type,
				'class'=>$this->get_class($property->getDeclaringClass()->getName()),
			];
		}

		/* class methods */
		$methods = $reflectionClass->getMethods();

		foreach ($methods as $method) {
			$parameter_details = [];

			$parameters = $method->getParameters();

			foreach ($parameters as $parameter) {
				$position = $parameter->getPosition();

				/* methods parameters */
				$parameter_details[$position] = [
					'name'=>$parameter->name,
					'allows null'=>$parameter->allowsNull(),
					'can be passed by value'=>$parameter->canBePassedByValue(),
					'position'=>$parameter->getPosition(),
					'type'=>(string)$parameter->getType(),
					'has type'=>$parameter->hasType(),
					'is array'=>$parameter->isArray(),
					'is callable'=>$parameter->isCallable(),
					'is default value available'=>$parameter->isDefaultValueAvailable(),
					'is optional'=>$parameter->isOptional(),
					'is passed by reference'=>$parameter->isPassedByReference(),
					'is variadic'=>$parameter->isVariadic(),
				];

				/* special perameter instances */
				if ($parameter_details[$position]['is default value available']) {
					$parameter_details[$position]['default'] = $parameter->getDefaultValue();
					$parameter_details[$position]['is default value constant'] = $parameter->isDefaultValueConstant();
				}
			}

			$details['methods'][$method->name] = [
				'doc comment'=>$method->getDocComment(),
				'name'=>$method->name,
				'private'=>$method->isPrivate(),
				'public'=>$method->isPublic(),
				'protected'=>$method->isProtected(),
				'static'=>$method->isStatic(),
				'has return type'=>$method->hasReturnType(),
				'return type'=>(string)$method->getReturnType(),
				'parameters'=>$parameter_details, /* attach the parameters */
				'class'=>$this->get_class($method->class),
			];
		}

		return $details;
	}

	protected function get_class(string $class) : string
	{
		return ($class == $this->fake_class_name) ? $this->real_class_name : $class;
	}

	protected function make_dummy_class_file(string $filepath) : bool
	{
		$success = false;

		$source = file_get_contents($filepath);

		$found = $this->find_first_class_name($source);

		if (is_array($found)) {
			$success = true;

			$original_class_name = $found[2];

			$this->new_class_name = 'F'.md5_file($filepath).$original_class_name;
			$this->new_class_filepath = CACHEPATH.'/'.$this->new_class_name.'.php';

			$find = implode('',$found);

			$found[2] = $this->new_class_name;

			$replace = implode('',$found);

			$source = str_replace($find,$replace,$source);

			/* dump fake class */
			file_put_contents($this->new_class_filepath,$source);

			/* include the fake class */
			require $this->new_class_filepath;

			/* remove fake file */
			unlink($this->new_class_filepath);
		}

		return $success;
	}

	protected function find_first_class_name(string $source)
	{
		$tokens = token_get_all($source);

		foreach ($tokens as $idx=>$token) {
			if ($token[0] == T_CLASS) {
				return [
					$tokens[$idx][1],
					$tokens[$idx+1][1],
					$tokens[$idx+2][1],
					$tokens[$idx+3][1],
				];
			}
		}

		return false;
	}

} /* end class */