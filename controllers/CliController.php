<?php

class CliController extends \MY_Controller
{

	public function indexCliAction()
	{
		/* hack */
		include __DIR__.'/cli/HelpController.php';

		(new HelpController)->indexCliAction();
	}

}
