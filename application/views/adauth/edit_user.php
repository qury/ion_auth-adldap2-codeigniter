<?php
$label_width = 'col-md-3';
$field_width = 'col-md-9';
$input_extra = 'class="form-control " ';
?>

<!-- View: adauth/edit_user -->

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
					<h1 class="panel-title"><?php echo lang('edit_user_heading'); ?></h1>
					<span><?php echo lang('edit_user_subheading'); ?></span>
				</div>
				<div class="panel-body">
					<?php echo form_open(uri_string(), array('class' => 'form-horizontal')); ?>


					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">First name:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
								<?php echo form_input($first_name, set_value('first_name'), $input_extra); ?>
                            </div>
                        </div>
                    </div>


					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Last name:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
								<?php echo form_input($last_name, set_value('last_name'), $input_extra); ?>
                            </div>
                        </div>
                    </div>

					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Company:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-building" aria-hidden="true"></i></span>
								<?php echo form_input($company, set_value('company'), $input_extra); ?>
                            </div>
                        </div>
                    </div>

					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Phone:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone" aria-hidden="true"></i></span>
								<?php echo form_input($phone, set_value('phone'), $input_extra); ?>
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
								<?php echo form_password($password, set_value('password'), $input_extra); ?>
                            </div>
                        </div>
                    </div>


					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Password confirm:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock" aria-hidden="true"></i></span>
								<?php echo form_password($password_confirm, set_value('password_confirm'), $input_extra); ?>
                            </div>
                        </div>
                    </div>


					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Domain:</label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-building-o" aria-hidden="true"></i></span>
								<?php echo form_dropdown($domain['name'], $domain['list'], $domain['value'], $input_extra); ?>
                            </div>
                        </div>
                    </div>
					<div class="form-group">
                        <div class="<?= $label_width ?>">
                            <label class="control-label">Theme:</label><br><br>
							<label class="control-label"><i>navbar</i></label>
                        </div>
                        <div class="<?= $field_width ?>">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-paint-brush" aria-hidden="true"></i></span>
								<?php echo form_dropdown($themes['name'], $themes['list'], $themes['value'], $input_extra); ?>
                            </div>
							<div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-paint-brush" aria-hidden="true"></i></span>
								<?php echo form_dropdown($navbar['name'], $navbar['list'], $navbar['value'], $input_extra); ?>
                            </div>
                        </div>
                    </div>


					<?php if ($this->ion_auth->is_admin()): ?>
						<div class="form-group">
							<div class="<?= $label_width ?>">
								<label class="control-label">Member of groups:</label>
							</div>
							<div class="<?= $field_width ?>">
								<?php foreach ($groups as $group): ?>
									<label class="checkbox">
										<?php
										$gID	 = $group['id'];
										$checked = null;
										$item	 = null;
										foreach ($currentGroups as $grp)
										{
											if ($gID == $grp->id)
											{
												$checked = ' checked="checked"';
												break;
											}
										}
										?>
										<input type="checkbox" name="groups[]" value="<?php echo $group['id']; ?>"<?php echo $checked; ?>>
										<?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8'); ?>
									</label>
								<?php endforeach ?>
							</div>
						</div>


					<?php endif ?>

					<?php echo form_hidden('id', $user->id); ?>
					<?php echo form_hidden($csrf); ?>


					<div class="form-group text-center">
						<?php if ($this->ion_auth->is_admin() && $user->id != $_SESSION['user_id']): ?>
							<a class="btn btn-danger btn-sm" href="<?= site_url('adauth/delete_user/' . $user->id) ?>">
								Delete User
							</a> |
						<?php endif ?>
						<?php echo form_submit('submit', lang('edit_user_submit_btn'), 'class="btn btn-primary btn-sm "'); ?>

                    </div>

					<?php echo form_close(); ?>
				</div>
			</div>

		</div>
	</div>
</div>