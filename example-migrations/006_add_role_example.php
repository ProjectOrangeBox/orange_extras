<?php

class Migration_006_add_role_example extends \Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		ci('o_role_model')->migration_add('Cookie Admin', 'Cookie Designer and Eater', $this->hash());
		
		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		ci('o_role_model')->migration_remove($this->hash());
		
		return true;
	}
} /* end migration */
