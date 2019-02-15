<?php

class Migration_004_copy_config_example extends Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		$this->_copy_config('support/config/nuts.php');
		
		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		$this->_unlink_config('support/config/nuts.php');
		
		return true;
	}
} /* end migration */
