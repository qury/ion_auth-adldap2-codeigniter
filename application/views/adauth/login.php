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
                    <h1 class="panel-title text-center"><b><?= $this->config->item('site', 'adauth'); ?></b></h1>
                </div>


                <div class="panel-body">
					<?php echo form_open("adauth/login", array('class' => 'form-horizontal')); ?>
                    <div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Username:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
								<?php echo form_input($identity, set_value('identity'), $input_extra); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Password:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock" aria-hidden="true"></i></span>
								<?php echo form_input($password, set_value('password'), $input_extra); ?>
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Domain:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon "><i class="fa fa-cubes " aria-hidden="true"></i></span>
								<?php echo form_dropdown($domain, $domain_values, set_value('domain'), $input_extra); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-center">
						<?php echo form_submit('submit', lang('login_submit_btn'), 'class="btn btn-primary btn-sm "'); ?>
                    </div>

					<?php echo form_close(); ?>
                </div>
                <div class=" panel-footer text-center" style="">

                    <ol class="breadcrumb" style="margin:0;">

                        <li><i><?= anchor('adauth/logout', 'logout') ?></i></a></li>

                    </ol>
                </div>

            </div>
			<?php if (isset($message)): ?>
				<div class="alert alert-danger text-center">
					<div id="infoMessage text-center"><?php echo $message; ?></div>
				</div>
			<?php endif; ?>
        </div>

    </div>

</div>













