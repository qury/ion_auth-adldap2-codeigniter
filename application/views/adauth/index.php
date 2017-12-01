<?php
$label_width = 'col-md-3';
$field_width = 'col-md-9';
$input_extra = 'class="form-control " ';
?>


<?php if (isset($message)): ?>
	<div class="container">
		<div class="section">
			<div class="row">

				<div class="alert alert-warning alert-dismissible text-center" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<div id="infoMessage text-center"><?php echo $message; ?></div>
				</div>

			</div>
		</div>
	</div>
<?php endif; ?>

<div class="container">
	<div class="section">
		<div class="row">

			<h1><?php echo lang('index_heading'); ?></h1>
			<p><?php echo lang('index_subheading'); ?></p>

			<div id="infoMessage"><?php echo $message; ?></div>

			<table id='usertbl' class="table table-bordered table-condensed table-striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>identity</th>
						<th><?php echo lang('index_fname_th'); ?></th>
						<th><?php echo lang('index_lname_th'); ?></th>
						<th>last_login</th>
						<th><?php echo lang('index_groups_th'); ?></th>
						<th><?php echo lang('index_status_th'); ?></th>
						<th><?php echo lang('index_action_th'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $user): ?>
						<tr>
							<td><?php echo htmlspecialchars($user->id, ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo date('Y-m-d H:m', $user->last_login); ?></td>

							<td>
								<?php foreach ($user->groups as $group): ?>
									<?php echo anchor("adauth/edit_group/" . $group->id, htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8')); ?>
								<?php endforeach ?>
							</td>
							<td><?php echo ($user->active) ? anchor("adauth/deactivate/" . $user->id, lang('index_active_link')) : anchor("adauth/activate/" . $user->id, lang('index_inactive_link')); ?></td>
							<td><?php echo anchor("adauth/edit_user/" . $user->id, 'Edit'); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p><?php echo anchor('adauth/create_user', lang('index_create_user_link')) ?> | <?php echo anchor('adauth/create_group', lang('index_create_group_link')) ?>| <?php echo anchor('adauth/list_groups', 'List groups') ?></p>
		</div>
	</div>
</div>

<!-- Customer CSS and Javascript for this view: adauth/index -->
<link href="<?= base_url('assets/css/dataTables.bootstrap.min.css'); ?>" type="text/css" rel="stylesheet" />
<script src="<?= base_url('assets/js/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/dataTables.bootstrap.min.js'); ?>"></script>

<script>
    $(document).ready(function () {
        $('table').DataTable();
    });
</script>

