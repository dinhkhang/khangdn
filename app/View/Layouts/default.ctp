<!DOCTYPE html>
<html>
<head>
	<?= $this->element('meta_tag'); ?>
	<title><?= $this->fetch('title'); ?></title>
	<?php
		echo $this->Html->css(array(
			'http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700&subset=vietnamese,latin,latin-ext',
			'font-awesome.min.css',
			'flipclock.css',
			'default.css',
			'style.css',
		));
		echo $this->Html->script(array(
			'jquery-1.11.2.min.js',
			'flipclock.min.js',
			'zebra_datepicker.js',
			'custom.js',
		));

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<center>
		<div class="bg">
			<?= $this->element('header/banner'); ?>
			<?= $this->fetch('content'); ?>
			<?= $this->element('footer'); ?>
		</div>
	</center>
</body>
</html>
