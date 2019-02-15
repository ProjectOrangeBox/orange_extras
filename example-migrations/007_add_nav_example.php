<?php

class Migration_007_add_nav_example extends Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		ci('o_nav_model')->migration_add('/scaffolding/columns', 'Columns', $this->hash());
		ci('o_nav_model')->migration_add('/scaffolding/regenerate', 'Regenerate', $this->hash());
		ci('o_nav_model')->migration_add('/scaffolding/tables', 'Tables', $this->hash());
		
		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		ci('o_nav_model')->migration_remove($this->hash());
		
		return true;
	}
} /* end migration */
