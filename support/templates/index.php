<?php pear::extends('_templates/orange_admin') ?>

<?php pear::section('section_container') ?>

<div class="row">
  <div class="col-md-6"><h3><i class="fa fa-{fa_icon}"></i> <?=$controller_titles ?></h3></div>
  <div class="col-md-6">
  	<div class="pull-right">
  		{index_right_header}
  	</div>
  </div>
</div>

<div class="row">
		<table class="table table-sticky-header table-search table-sort table-hover">
			<thead>
				<tr class="panel-default">
					<th class="panel-heading {index_header_class}">{index_label}</th>
					<th class="panel-heading text-center nosort">Actions</th>
				</tr>
			</thead>
		<tbody>
			<?php foreach ($records as $row) {
	?>
			<tr>
				<td class="{index_column_class}">
					{html}
				</td>
				<td class="text-center actions">
					{html}
				</td>
			</tr>
			<?php
} ?>
		</tbody>
	</table>
</div>

<?php pear::end() ?>
