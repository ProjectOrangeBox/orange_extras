<?php pear::extends('_templates/orange_admin') ?>

<?php pear::section('section_container') ?>

<?=pear::open_multipart($controller_path, ['class'=>'form-horizontal','method'=>$form_method,'data-success'=>'Record Saved|blue'], ['id'=>$record->id]) ?>
	<div class="row">
		<div class="col-md-6"><h3><i class="fa fa-{fa_icon}"></i> <?=$ci_title_prefix ?> <?=$controller_title ?></h3></div>
		<div class="col-md-6"><div class="pull-right">{detail_right_header}</div></div>
	</div>

	<hr>
	{details_columns}

	<!-- Submit Button -->
	<div class="form-group">
		<div class="col-md-12">
			<div class="pull-right">
				<?=pear::button(null, 'Save', ['class'=>'js-button-submit keymaster-s btn btn-primary']) ?>
			</div>
		</div>
	</div>
<?=pear::close() ?>

<?php pear::end() ?>
