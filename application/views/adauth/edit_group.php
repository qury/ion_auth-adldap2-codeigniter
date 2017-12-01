<?php
$label_width = 'col-md-3';
$field_width = 'col-md-9';
$input_extra = 'class="form-control " ';
?>

<!-- View: adauth/edit_group -->

<div class="container">
    <div class="row">
        <br><br>
		<div class="col-md-8 col-md-offset-2">
			<h1><?php echo lang('edit_group_heading'); ?></h1>
			<p><?php echo lang('edit_group_subheading'); ?></p>

			<div id="infoMessage"><?php echo $message; ?></div>

			<?php echo form_open(current_url(), array('class' => 'form-horizontal')); ?>

			<div class="form-group">
				<div class="<?= $label_width ?>">
					<label class="control-label">Group Name:</label>
				</div>
				<div class="<?= $field_width ?>">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user fa" aria-hidden="true"></i></span>
						<?php echo form_input($group_name, set_value('group_name'), $input_extra); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="<?= $label_width ?>">
					<label class="control-label">Group Description:</label>
				</div>
				<div class="<?= $field_width ?>">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user fa" aria-hidden="true"></i></span>
						<?php echo form_input($group_description, set_value('group_description'), $input_extra); ?>
					</div>
				</div>
			</div>
			<div class="form-group text-center">
				<?php echo form_submit('submit', lang('edit_group_submit_btn'), 'class="btn btn-primary btn-sm "'); ?>
			</div>

			<?php echo form_close(); ?>
		</div>
	</div>
</div>