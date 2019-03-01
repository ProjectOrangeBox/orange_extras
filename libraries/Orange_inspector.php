<?php

/**
 *
 * Controller End Point
 *
 * public function orange_inspectCliAction()
 * {
 *  ci('orange_inspector')->as_json($_SERVER['argv'][2]);
 * }
 *
 */
class Orange_inspector
{
	public function as_json(string $filepath, bool $direct= true) : string
	{
		$json_string = json_encode($this->reflect($filepath), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);

		if ($direct) {
			echo $json_string;
			exit(1);
		}

		return $json_string;
	}

	public function reflect($filepath)
	{
		if (!file_exists($filepath)) {
			return ['error'=>true,'msg'=>'Could not locate class file "'.$filepath.'".','code'=>404];
		}

		/* default to no class found */
		$class_name = false;

		$tokens = token_get_all(file_get_contents($filepath));

		foreach ($tokens as $idx=>$token) {
			if ($token[0] == T_CLASS) {
				/* ok we found the first class - because good php only has 1 class per file right? */
				$class_name = $tokens[$idx+2][1];
				break;
			}
		}

		/* was a class name found? */
		if (!$class_name) {
			return ['error'=>true,'msg'=>'Could not locate class in "'.$filepath.'".','code'=>500];
		}

		/* bring in the class if it's not already */
		require_once $filepath;

		/* class */
		$reflectionClass = new ReflectionClass($class_name);

		$details['name'] = $reflectionClass->getName();
		$details['doc comment'] = $reflectionClass->getDocComment();
		$details['documentation'] = $this->documents($reflectionClass->getDocComment());
		$details['namespace'] = $reflectionClass->getNamespaceName();
		$details['constants'] = $reflectionClass->getConstants();
		$details['interfaces'] = $reflectionClass->getInterfaces();
		$details['extends'] = $reflectionClass->getParentClass()->name;
		$details['traits'] = $reflectionClass->getTraitNames();
		$details['final'] = $reflectionClass->isFinal();
		$details['trait'] = $reflectionClass->isTrait();
		$details['file path'] = $filepath;

		/* class properties */
		$properties = $reflectionClass->getProperties();

		$defaults = $reflectionClass->getDefaultProperties();

		foreach ($properties as $property) {
			$default_type = gettype($defaults[$property->getName()]);

			$default_type = ($default_type == 'NULL') ? null : $default_type;

			$details['properties'][$property->getName()] = [
				'doc comment'=>$property->getDocComment(),
				'documentation'=>$this->documents($property->getDocComment()),
				'name'=>$property->getName(),
				'private'=>$property->isPrivate(),
				'public'=>$property->isPublic(),
				'protected'=>$property->isProtected(),
				'static'=>$property->isStatic(),
				'default'=>$defaults[$property->getName()],
				'default type'=>$default_type,
				'class'=>$property->getDeclaringClass()->getName(),
			];
		}

		/* loop on method classes */
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
				'documentation'=>$this->documents($method->getDocComment()),
				'name'=>$method->name,
				'private'=>$method->isPrivate(),
				'public'=>$method->isPublic(),
				'protected'=>$method->isProtected(),
				'static'=>$method->isStatic(),
				'has return type'=>$method->hasReturnType(),
				'return type'=>(string)$method->getReturnType(),
				'parameters'=>$parameter_details, /* attach the parameters */
				'class'=>$method->class,
			];
		}

		return $details;
	}

	protected function documents(string $comment) : string
	{
		$lines = explode(PHP_EOL, $comment);
		$c = '';

		foreach ($lines as $line) {
			$line = trim($line);

			if (substr($line, 0, 3) == '/**') {
				/* do nothing */
			} elseif (substr($line, 0, 2) == '*/') {
				/* do nothing */
			} elseif (substr($line, 0, 3) == '* @') {
				/* do nothing */
			} elseif (substr($line, 0, 2) == '* ') {
				$c .= trim(substr($line, 2)).PHP_EOL;
			}
		}

		return trim($c);
	}
} /* end class */
