<?php 		
		
	$theme_error_path_ = pb_current_theme_path()."error.php";

	if(file_exists($theme_error_path_)){
		include($theme_error_path_);
		pb_end();
	}


?><!DOCTYPE html>
<html lang="ko">
<head>
   
    <title>ERROR!</title>

    <meta charset="utf8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pb-admin.css">
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/error.css">

</head>

<body class="page-pbpress-error">

	<div class="content-group">
		<h1 class="title"><?=$error_title?></h1>
		<div class="subject"><?=$error_message?></div>
		<div class="copyrights"><?=pb_hook_apply_filters('adminpage_footer_copyrights', '© 2019 Paul&Bro Company All Rights Reserved.')?></div>
	</div>

	

</body>
</html>