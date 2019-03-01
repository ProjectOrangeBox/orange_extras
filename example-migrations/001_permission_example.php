<?php

class Migration_001_permission_example extends \Migration_base
{

	/* example up function */
	public function up()
	{
		echo $this->migration('up');

		ci('o_permission_model')->migration_add('url::/cli/scaffolding::generate~cli', 'Scaffolding', 'Cli Cli Scaffolding Generate', $this->hash());
		ci('o_permission_model')->migration_add('url::/cli/scaffolding::generate~cli', 'Scaffolding', 'Cli Cli Scaffolding Generate', $this->hash());
		ci('o_permission_model')->migration_add('url::/cli/scaffolding::create_columns~cli', 'Scaffolding', 'Cli Cli Scaffolding Create Columns');
		ci('o_permission_model')->migration_add('url::/cli/scaffolding::create_missing_columns~cli', 'Scaffolding', 'Cli Cli Scaffolding Create Missing Columns', $this->hash());
		ci('o_permission_model')->migration_add('url::/cli/scaffolding::create_files~cli', 'Scaffolding', 'Cli Cli Scaffolding Create Files', $this->hash());
		ci('o_permission_model')->migration_add('url::/cli/scaffolding::create_missing_files~cli', 'Scaffolding', 'Cli Cli Scaffolding Create Missing Files', $this->hash());
		ci('o_permission_model')->migration_add('url::/cli/scaffolding::display_permissions~cli', 'Scaffolding', 'Cli Cli Scaffolding Display Permissions', $this->hash());
		
		ci('o_permission_model')->migration_add('url::/scaffolding/columns::index~get', 'Scaffolding', 'Scaffolding Get Columns Index', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/columns::details~get', 'Scaffolding', 'Scaffolding Get Columns Details', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/columns::index~post', 'Scaffolding', 'Scaffolding Post Columns Index', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/columns::index~patch', 'Scaffolding', 'Scaffolding Patch Columns Index', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/columns::index~delete', 'Scaffolding', 'Scaffolding Delete Columns Index', $this->hash());
		
		ci('o_permission_model')->migration_add('url::/scaffolding/regenerate::button_regenerate_all_files~get', 'Scaffolding', 'Scaffolding Get Regenerate Button Regenerate All Files', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/regenerate::button_regenerate_missing_files~get', 'Scaffolding', 'Scaffolding Get Regenerate Button Regenerate Missing Files', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/regenerate::button_regenerate_all_columns~get', 'Scaffolding', 'Scaffolding Get Regenerate Button Regenerate All Columns', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/regenerate::button_regenerate_missing_columns~get', 'Scaffolding', 'Scaffolding Get Regenerate Button Regenerate Missing Columns', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/regenerate::index~get', 'Scaffolding', 'Scaffolding Get Regenerate Index');
		
		ci('o_permission_model')->migration_add('url::/scaffolding/tables::index~get', 'Scaffolding', 'Scaffolding Get Tables Index', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/tables::details~get', 'Scaffolding', 'Scaffolding Get Tables Details', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/tables::index~post', 'Scaffolding', 'Scaffolding Post Tables Index', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/tables::index~patch', 'Scaffolding', 'Scaffolding Patch Tables Index', $this->hash());
		ci('o_permission_model')->migration_add('url::/scaffolding/tables::index~delete', 'Scaffolding', 'Scaffolding Delete Tables Index', $this->hash());
		
		return true;
	}

	/* example down function */
	public function down()
	{
		echo $this->migration('down');

		ci('o_permission_model')->migration_remove($this->hash());
		
		return true;
	}
} /* end migration */
