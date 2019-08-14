<?php
	
require(dirname( __FILE__ ) . '/defined.php');
require(PB_DOCUMENT_PATH . 'includes/initialize.php');

pb_hook_do_action("pb_started");

$current_slug_ = pb_current_slug();

if(strlen($current_slug_)){
	$rewrite_ = pb_current_rewrite();
	$rewrite_handler_ = isset($rewrite_["rewrite_handler"]) ? $rewrite_["rewrite_handler"] : "pb_rewrite_common_handler";
	$current_path_ = call_user_func_array($rewrite_handler_, array(pb_rewrite_path(), $rewrite_));

	if(isset($rewrite_)){
		if(pb_is_error($current_path_)){
			pb_redirect_error($current_path_->error_code(), $current_path_->error_message(), $current_path_->error_title());
			pb_end();
		}

		include($current_path_);
		pb_end();
	}else{
		pb_redirect_404();
		pb_end();
	}
}

$index_path_ = pb_hook_apply_filters('pb_home_path', pb_current_theme_path()."index.php");

if(file_exists($index_path_)){
	include($index_path_);	
}

pb_end();

?>