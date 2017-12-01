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
			<?php if (isset($message) && !is_null($message)): ?>

				<div class="alert alert-danger alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<div id="infoMessage"><?php echo $message; ?></div>
				</div>
			<?php endif; ?>


			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Members of Group: <b><?= $group[0]['name'] ?></b></h3>
					<span><i><?= $group[0]['description'] ?></i></span>
				</div>


				<table  class="table table-responsive table-striped table-hover">
					<thead>
						<tr>
							<th>[id]</th>
							<th>[username]</th>
							<th>[created_on]</th>
							<th>[last_login]</th>
							<th>[active]</th>
							<th>[action]</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($members as $row): ?>
							<tr id="row_<?php echo $row['id']; ?>">
								<td>
									<?php echo $row['id']; ?>
								</td>
								<td>
									<b><?php echo $row['username']; ?></b>
								</td>


								<?php if (isset($row['created_on']) && !is_null($row['created_on'])): ?>
									<td> <?php echo unix_to_human(htmlspecialchars($row['created_on'], ENT_QUOTES, 'UTF-8')); ?></td>
								<?php else: ?>
									<td></td>
								<?php endif; ?>
								<?php if (isset($row['last_login']) && !is_null($row['last_login'])): ?>
									<td> <?php echo unix_to_human(htmlspecialchars($row['last_login'], ENT_QUOTES, 'UTF-8')); ?></td>
								<?php else: ?>
									<td></td>
								<?php endif; ?>




								<td><?php echo ($row['active']) ? anchor("adauth/deactivate/" . $row['id'], lang('index_active_link')) : anchor("adauth/activate/" . $row['id'], lang('index_inactive_link')); ?></td>


								<td>
									<a class="btn btn-xs btn-link"  href="<?php echo site_url('adauth/edit_user/' . $row['id']); ?>">
										<i class="fa fa-edit"></i>
									</a>

									<a class="btn btn-xs btn-link text-red"  title='Remove user from group'  href="<?= site_url('adauth/remove_group_member/' . $row['id'] . '/' . $group_id) ?>">
										<i class="fa fa-trash"></i>
									</a>


								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>


				<div class="panel-footer">

					<div class="text-center">
						<div class="">
							<a class="btn btn-default btn-xs" href='<?php echo site_url('adauth/create_group'); ?>'>Create new group</a>
							<a class="btn btn-default btn-xs" href='<?php echo site_url('adauth/create_user'); ?>'>Create new user</a>
							<a class="btn btn-default btn-xs" href='<?php echo site_url('adauth/list_groups'); ?>'>List groups</a>
						</div>
					</div>

				</div>
			</div>






		</div>
	</div>
</div>