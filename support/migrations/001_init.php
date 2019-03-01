<?php

/* 001_init.php */

class Migration_001_init extends \Migration_base
{
	public function up()
	{
		echo $this->migration('up');
		
		ci()->db->query('CREATE TABLE `simple_q` (
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL,
  `queue` char(32) CHARACTER SET latin1 NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `token` char(40) CHARACTER SET latin1 DEFAULT NULL,
  `payload` longblob NOT NULL,
  KEY `idx_token` (`token`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_updated` (`updated`) USING BTREE,
  KEY `idx_handler` (`queue`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8');
		
		return true;
	}

	public function down()
	{
		echo $this->migration('down');

		ci()->db->query('DROP TABLE `simple_q`');
		
		return true;
	}
} /* end migration */
