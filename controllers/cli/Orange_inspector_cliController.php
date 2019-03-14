<?php

class Orange_inspector_cliController extends \MY_Controller
{
	public function helpCliAction() : void
	{
		ci('console')->help([
			['Used internally to reflect on a class.'=>'%%/inspect'],
		]);
	}

	public function inspectCliAction() : void
	{
		ci('orange_inspector')->as_json($_SERVER['argv'][2]);
	}

} /* end class */
