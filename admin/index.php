<?php
	
include(dirname( __FILE__ ) . "/includes.php");

if(!pb_is_user_logged_in()){
	pb_redirect(pb_admin_login_url(pb_admin_url()));
	pb_admin_end();
}

$admin_data_ = pb_current_user();
$current_adminpage_ = pb_current_adminpage();

if(!pb_user_has_authority_task($admin_data_['id'], "access_adminpage")){
	echo "Access denied";
	pb_admin_end();
}

$current_adminpage_slug_ = pb_current_adminpage_slug();
if(strlen($current_adminpage_slug_) && !isset($current_adminpage_)){
	pb_redirect_404();
	pb_admin_end();
}

$current_adminpage_slug_ = strlen($current_adminpage_slug_) ? $current_adminpage_slug_ : "dashboard";
$rewrite_handler_ = isset($current_adminpage_["rewrite_handler"]) ? $current_adminpage_["rewrite_handler"] : "pb_adminpage_rewrite_common_handler";
$current_adminpage_path_ = call_user_func_array($rewrite_handler_, array(pb_adminpage_rewrite_path(), $current_adminpage_));

pb_hook_do_action("pb_admin_started");

global $pb_config;

?><!DOCTYPE html>
<html>
<head>
	<title><?=pb_hook_apply_filters('pb-adminpage-title', "PBPress 관리자페이지")?></title>

	<meta charset="<?=$pb_config->charset?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<?php pb_admin_head(); ?>
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/adminpage.css">
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/adminpage.js"></script>
</head>

<body class="page-pbpress-adminpage page-<?=$current_adminpage_slug_?>">
	<?php include(PB_DOCUMENT_PATH."admin/header.php"); ?>
	<?php include(PB_DOCUMENT_PATH."admin/aside.php"); ?>
	
	<div class="adminpage-content-frame">
		<?php
			if(pb_is_error($current_adminpage_path_)){
				pb_adminpage_draw_error(503, $current_adminpage_path_->error_message(), $current_adminpage_path_->error_title());
			}else{
				include($current_adminpage_path_);
			}

		?>
	</div>
	<div class="copyrights"><?=pb_hook_apply_filters('adminpage_footer_copyrights', '© 2019 Paul&Bro Company All Rights Reserved.')?></div>
	
	<?php pb_admin_foot(); ?>
</body>
</html>
<?php pb_admin_end(); ?>