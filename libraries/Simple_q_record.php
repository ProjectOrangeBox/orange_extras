<?php

class Simple_q_record {
	public $payload;
	protected $record;

	public function __construct(&$record)
	{
		$this->record = $record;
		$this->payload = &$record->payload;
	}

	public function __get($name)
	{
		return (isset($this->record->$name)) ? $this->record->$name : null;
	}

	public function complete()
	{
		ci('simple_q')->update($this->token,'processed');
	}

	public function new()
	{
		ci('simple_q')->update($this->token,'new');
	}

	public function error()
	{
		ci('simple_q')->update($this->token,'error');
	}


} /* end class */
