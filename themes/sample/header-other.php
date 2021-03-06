<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Bare - Start Bootstrap Template</title>

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/bootstrap.min.css">

	<?php pb_head(); ?>

</head>

<body>

	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-light bg-light static-top">
		<div class="container">	
			<a class="navbar-brand" href="#">Start Bootstrap</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarResponsive">
				<?php
					$main_menu_id_ = pb_option_value("sample_theme_menu_id");
					pb_menu_render(array(
						'menu_slug' => $main_menu_id_,
						'walker' => 'PBMenuWalker_sample_mainmenu',
					)); ?>

			</div>
		</div>
	</nav>