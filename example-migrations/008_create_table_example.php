<?php

class Migration_008_create_table_example extends Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		ci()->db->query('CREATE TABLE `category_mgr_members` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;');

		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		ci()->db->query('DROP TABLE `category_mgr_members`;');

		return true;
	}
} /* end migration */
