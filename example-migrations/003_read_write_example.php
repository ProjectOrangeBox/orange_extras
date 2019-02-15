<?php

class Migration_003_read_write_example extends Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		$this->_add_rw_folder('/downloads/cookies');
		
		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		$this->_remove_rw_folder('/downloads/cookies');
		
		return true;
	}
} /* end migration */
