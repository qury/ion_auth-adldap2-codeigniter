
<?php
$label_width = 'col-md-3';
$field_width = 'col-md-9';
$input_extra = 'class="form-control " ';
?>
<div class="container">
    <div class="row">
        <br><br>
		<div class="col-md-6 col-md-offset-3">


			<div class="panel panel-primary">
                <div class="panel-heading">
                    <h1 class="panel-title text-center"><b><?= lang('deactivate_heading') ?></h1>
                </div>
                <div class="panel-body">
					<div class="alert alert-warning text-center">
						<?= sprintf(lang('deactivate_subheading'), $user->username) ?>					
					</div>
					<?php echo form_open("adauth/deactivate/" . $user->id, array('class' => 'form-horizontal')); ?>


					<div class="form-group">
						<div class="checkbox text-center">
							<label class="checkbox-inline">
								<input type="radio" name="confirm" value="yes" checked="checked" />Yes
							</label>
							<label class="checkbox-inline">
								<input type="radio" name="confirm" value="no" />No
							</label>
						</div>
                    </div>





					<?php echo form_hidden($csrf); ?>
					<?php echo form_hidden(array('id' => $user->id)); ?>

					
					
					<div class="form-group text-center">
                        <?php echo form_submit('submit', lang('deactivate_submit_btn'), 'class="btn btn-primary "'); ?>
                    </div>

					<?php echo form_close(); ?>

				</div>
			</div>





		</div>
	</div>
</div>