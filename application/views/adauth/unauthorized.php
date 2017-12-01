
<div class="section">
	<div class="container">
        <div class="row">
			<div class="col-md-12">
				<div class="alert alert-danger alert-dismissable">
					<strong>Unauthorized Access!</strong>
					<br>Unfortunatelly you lack the necessary autorization to display this page.</div>
			</div>
        </div>
		<div class="row">
			<div class="col-md-12 text-center">
				<div class="btn-group btn-group-justified btn-group-lg">
					<?php if ($this->ion_auth->logged_in()): ?>
						<?php $id = $this->ion_auth->user()->row()->id; ?>
						<a href="<?= site_url('adauth/edit_user/' . $id) ?>" class="btn btn-default">Profile</a>
					<?php endif; ?>

					<a href="<?= site_url('adauth/login') ?>" class="btn btn-default">Login</a>
					<a href="<?= site_url('adauth/logout') ?>" class="btn btn-default">Logout</a>
					<a href="<?= site_url('adauth/index') ?>" class="btn btn-default">Index</a>
				</div>
			</div>
        </div>
	</div>
</div>
<div class="section">
	<div class="container">
        <div class="row">
			<div class="col-md-12">
				<div class="well">
					<h2>Session Information</h2>
					<pre>
						<?php print_r($session); ?>
					</pre>
				</div>
				<div class="well">
					<h2>User Information</h2>
					<pre>	<?php print_r($user); ?></pre>
				</div>
			</div>
        </div>
	</div>
</div>
