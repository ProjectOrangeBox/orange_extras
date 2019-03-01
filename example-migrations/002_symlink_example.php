<?php

class Migration_002_symlink_example extends \Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		$this->_link_public('/assets/example');
		
		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		$this->_unlink_public('/assets/example');
		
		return true;
	}
} /* end migration */
