<?php
if ($this->ion_auth->logged_in())
{
	$navbar = $_SESSION['navbar'];
}
else
{
	$navbar = $this->config->item('bootstrap_navbar', 'adauth');
}
?>

<!-- Static navbar -->
<nav class="navbar navbar-<?= $navbar ?> navbar-static-top">
	<div class="container">
        <div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?= site_url('adauth') ?>"><?= $this->config->item('site', 'adauth') ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li class="active"><a href="<?= site_url('/') ?>">Home</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<?php if ($this->ion_auth->logged_in()): ?>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
							<?= $this->ion_auth->user()->row()->first_name ?>
							<span class="caret"></span>
						</a>
					<?php else: ?>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
					<?php endif; ?>

					<ul class="dropdown-menu">
						<li><a href="#">Action</a></li>
						<?php if ($this->ion_auth->logged_in()): ?>
							<li role="separator" class="divider"></li>
							<li><a href="<?= site_url('adauth/edit_user/' . $this->ion_auth->user()->row()->id); ?>">Profile</a></li>
							<li><a href="<?= site_url('adauth/logout'); ?>">Logout</a></li>
						<?php else: ?>
							<li><a href="<?= site_url('adauth/login'); ?>">Login</a></li>
						<?php endif; ?>
					</ul>
				</li>
			</ul>
        </div><!--/.nav-collapse -->
	</div>
</nav>