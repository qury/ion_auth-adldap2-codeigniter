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

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><b>Groups</b></h3>
				</div>


				<table  class="table table-responsive table-striped table-hover">
					<thead>
						<tr>
							<th>[id]</th>
							<th>[name]</th>
							<th>[description]</th>
							<th>[Action]</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($members as $row): ?>
							<tr>
								<td>
									<?php echo $row['id']; ?>
								</td>
								<td>
									<b><?php echo $row['name']; ?></b>
								</td>
								<td>
									<a href="<?php echo site_url('adauth/edit_group/' . $row['id']); ?>">
										<?php echo $row['description']; ?>
									</a>

								</td>
								<td>
									<div class="btn-group btn-group-xs">
										<a class="btn btn-xs btn-success"  href="<?= site_url('adauth/group_members/' . $row['id']) ?>">
											<i class="fa fa-list-ul"></i>
										</a>
										<a class="btn btn-xs btn-warning"  href="<?= site_url('adauth/edit_group/' . $row['id']) ?>">
											<i class="fa fa-edit"></i>
										</a>
										<a class="btn btn-xs btn-danger"  href="<?= site_url('adauth/delete_group/' . $row['id']) ?>">
											<i class="fa fa-trash-o"></i>
										</a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>


				<div class="panel-footer">

					<div class="text-center">
						<a class="btn btn-sm btn-primary" href='<?php echo site_url('adauth/create_group'); ?>'>Create new group</a>
					</div>

				</div>
			</div>






		</div>
	</div>
</div>