<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<title>Ion.info</title>


		<!-- Bootstrap core CSS -->
		<?php if ($this->ion_auth->logged_in()): ?>
			<link href="<?= base_url('assets/css/' . $_SESSION['theme']) ?>" rel="stylesheet">
		<?php else: ?>
			<?php $this->load->config('adauth'); ?>
			<link href="<?= base_url('assets/css/' . $this->config->item('bootstrap_theme', 'adauth')) ?>" rel="stylesheet">
		<?php endif; ?>

		<!-- Font-awsome styles for this template -->
		<link href="<?= base_url('assets/css/font-awesome.min.css') ?>" rel="stylesheet">


	</head>

	<body>