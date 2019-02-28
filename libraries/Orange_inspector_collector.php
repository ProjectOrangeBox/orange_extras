<?php

class Orange_inspector_collector {
	protected $details = [];
	protected $end_point = false;

	public function cli_end_point(string $end_point) : Orange_inspector_collector
	{
		$this->end_point = $end_point;

		return $this;
	}

	public function details() : array
	{
		return $this->details;
	}

	public function find_in_packages(string $regular_expression = '.+\.php') : Orange_inspector_collector
	{
		/* add application folder */
		foreach (get_packages('app',null,true) as $path) {
			$this->find_in($path,$regular_expression);
		}

		return $this;
	}

	public function find_in(string $path, string $regular_expression = '.+\.php') : Orange_inspector_collector
	{
		$matches = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),'/^'.$regular_expression.'$/i',RecursiveRegexIterator::GET_MATCH);

		foreach ($matches as $filepath) {
			$this->inspect($filepath[0]);
		}

		return $this;
	}

	public function inspect(string $filepath) : Orange_inspector_collector
	{
		if (!$this->end_point) {
			throw new \Exception('End point not supplied.',500);
		}

		exec('php '.ROOTPATH.'/public/index.php '.$this->end_point.' "'.$filepath.'"',$json);

		$this->details[$filepath] = json_decode($json[0],true);

		return $this;
	}

} /* end class */