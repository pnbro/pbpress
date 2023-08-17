<?php 		
		
	$theme_404_path_ = pb_current_theme_path()."404.php";

	if(file_exists($theme_404_path_)){
		include($theme_404_path_);
		pb_end();
	}


?><!DOCTYPE html>
<html lang="ko">
<head>
   
    <title>404</title>

    <meta charset="utf8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pb-admin.css">
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/404.css">

</head>

<body class="page-pbpress-404">

	<div class="content-group">
		<h1 class="title">404</h1>
		<div class="subject">Page not found.</div>
		<div class="copyrights"><?=pb_hook_apply_filters('adminpage_footer_copyrights', '© 2019 Paul&Bro Company All Rights Reserved.')?></div>
	</div>

	

</body>
</html>