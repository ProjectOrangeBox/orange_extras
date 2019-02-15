<?php

class {ucontroller_name}Controller extends MY_Controller {
	use admin_controller_trait;

	public $controller					= '{controller_name}'; 
	public $controller_path			= '{controller_path}';
	public $controller_model		= '{dataset_model}';
	public $controller_title		= '{singular}';
	public $controller_titles		= '{plural}';
	public $controller_order_by	= null; /* column in model used by catalog */
	public $controller_limit		= 1000; /* index rows to show */

} /* end class {ucontroller_name}Controller */
