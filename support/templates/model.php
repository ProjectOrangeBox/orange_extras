<?php

class {ucontroller_name}_model extends Database_model {
	protected $table = '{tablename}'; /* required */
	protected $primary_key = 'id'; /* parent default id */
	protected $auto_generated_primary = true; /* parent default true */
	protected $rules = [
	];
	protected $rule_sets = [
		'insert'=>'{required columns}', /* required */
	];

} /* end class {ucontroller_name}_model */
