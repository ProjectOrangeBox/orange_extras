<?php

class Migration_005_add_setting_example extends Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		/**
		 *
		 * $name=null; name of setting
		 * $group=null; group (tab) to put the setting in
		 * $value=null; value for setting
		 * $help=null; help to display for this setting
		 *
		 * $options=null; radio button, textarea, checkbox, select (dropdown), text field. see below
		 * 	{"type":"radio","options":{"1":"Red","2":"Green","3":"Yellow","4":"Blue"}}
		 * 	{"type":"textarea","rows":5}
		 * 	{"type":"checkbox","copy":"Show","data-on":8,"data-off":9}
		 * 	{"type":"select","options":{"1":"Red","2":"Green","3":"Yellow","4":"Blue"}}
		 * 	{"type":"text","width":"50","mask":"int"}
		 *
		 * $migration=null; migration hash
		 * $optional=[]; additional options
		 *
		 */
		ci('o_setting_model')->migration_add('Foobar', 'Example', 0, '', '', $this->hash());

		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		ci('o_setting_model')->migration_remove($this->hash());

		return true;
	}
} /* end migration */
