<?php

class Migration_001_init extends Migration_base
{
	public function up()
	{
		echo $this->migration('up');
		
		return true;
	}

	public function down()
	{
		echo $this->migration('down');
		
		return true;
	}
} /* end migration */
