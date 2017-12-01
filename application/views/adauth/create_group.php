
<?php
$label_width			 = 'col-md-3';
$field_width			 = 'col-md-9';
$input_extra			 = 'class="form-control " ';
$input_extra_required	 = 'class="form-control" required ';
?>

<!-- View: adauth/create_group -->
<?php
$label_width			 = 'col-md-3';
$field_width			 = 'col-md-9';
$input_extra			 = 'class="form-control " ';
$input_extra_required	 = 'class="form-control" required ';
?>

<!-- View: adauth/create_user -->

<div class="container">
    <div class="row">

		<div class="col-md-8 col-md-offset-2">
			<?php if (isset($message) && !empty($message)): ?>
				<div id="infoMessage" class="alert alert-danger">
					<?php echo $message; ?>
				</div>
			<?php endif; ?>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h1 class="panel-title"><?php echo lang('create_group_heading'); ?></h1>
					<span><?php echo lang('create_group_subheading'); ?></span>
				</div>
				<div class="panel-body">
					<?php echo form_open(site_url('adauth/create_group'), array('class' => 'form-horizontal')); ?>


					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label"><?= lang('create_group_name_label', 'group_name') ?></label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group alert-danger">
                                <span class="input-group-addon"><i class="fa fa-users" aria-hidden="true"></i></span>
								<?php echo form_input($group_name, set_value('group_name'), $input_extra_required); ?>
                            </div>
                        </div>
                    </div>

					<div class="form-group">
                        <div class="<?= $label_width ?>">
							<label class="control-label"><?= lang('create_group_desc_label', 'group_name') ?></label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-list" aria-hidden="true"></i></span>
								<?php echo form_input($description, set_value('description'), $input_extra); ?>
                            </div>
                        </div>
                    </div>





					<div class="form-group text-center">
						<?php echo form_submit('submit', lang('create_group_submit_btn'), 'class="btn btn-primary btn-sm "'); ?>
                    </div>

					<?php echo form_close(); ?>
				</div>
			</div>

		</div>
	</div>
</div>








