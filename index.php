<?php
	
//웹서버가 모든 요청을 이 파일로 보내는 경우(nginx 기본 try_files 등) 관리자 주소는 관리자 화면으로 넘긴다.
//Apache 는 .htaccess 가 관리자 주소를 admin/index.php 로 바로 보내므로 여기까지 오지 않는다
$_pb_request_path_ = rawurldecode((string)strtok(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '', '?'));
$_pb_admin_base_ = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php')), '/').'/admin/';
if(strpos($_pb_request_path_, $_pb_admin_base_) === 0){
	unset($_pb_request_path_, $_pb_admin_base_);
	require(dirname( __FILE__ ) . '/admin/index.php');
	exit;
}
unset($_pb_request_path_, $_pb_admin_base_);

require(dirname( __FILE__ ) . '/defined.php');
require(PB_DOCUMENT_PATH . 'includes/initialize.php');

pb_hook_do_action("pb_started");

$current_slug_ = pb_current_slug();

if(strlen($current_slug_ ?? "")){
	$rewrite_ = pb_current_rewrite();
	$rewrite_handler_ = isset($rewrite_["rewrite_handler"]) ? $rewrite_["rewrite_handler"] : pb_hook_apply_filters("pb_rewrite_common_handler", "pb_rewrite_common_handler");
	$current_path_ = call_user_func_array($rewrite_handler_, array(pb_rewrite_path(), $rewrite_));

	if(isset($current_path_)){
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