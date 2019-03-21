<?php

include(dirname( __FILE__ ) . "/includes.php");

global $pb_config;

header("Content-Type:application/json; charset=".$pb_config->charset);

$request_data_ = isset($_POST['request_data']) ? $_POST['request_data'] : null;

if(!isset($request_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청값이 잘못되었습니다.',
	));
	pb_admin_end();	
}

if(!pb_session_verify_instance_token("pbpress_install", $request_data_['_request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청토큰이 잘못되었습니다.',
	));
	pb_admin_end();	
}

$site_name_ = $request_data_['site_name'];
$site_desc_ = $request_data_['site_desc'];
$timezone_ = $request_data_['timezone'];

global $pbdb;
$pbdb->install_tables();

if(!$pbdb->exists_table("OPTIONS")){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '테이블 설치 중, 에러가 발생했습니다.',
	));
	pb_admin_end();
}

pb_option_update("site_name", $site_name_);
pb_option_update("site_desc", $site_desc_);
pb_option_update("timezone", $timezone_);
$theme_list_ = pb_theme_list();

if(!strlen($timezone_)){
	$timezone_ = @date_default_timezone_get();
	$timezone_ = strlen($timezone_) ? $timezone_ : "Asia/Seoul";
}

if(count($theme_list_) > 0){
	foreach($theme_list_ as $theme_ => $theme_data_){
		pb_switch_theme($theme_);
		pb_theme_install_tables();
		break;
	}
}

$common_rewrite_bool_ = pb_install_rewrite();
	
if(pb_is_error($common_rewrite_bool_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => $common_rewrite_bool_->error_message(),
	));
	pb_admin_end();
}

$admin_rewrite_bool_ = pb_install_admin_rewrite();
if(pb_is_error($admin_rewrite_bool_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => $admin_rewrite_bool_->error_message(),
	));
	pb_admin_end();
}

$user_login_ = $request_data_['user_login'];
$user_email_ = $request_data_['user_email'];
$user_name_ = $request_data_['user_name'];
$user_pass_ = pb_crypt_decrypt($request_data_['user_pass']);

$admin_id_ = pb_user_add(array(
	'USER_LOGIN' => $user_login_,
	'USER_EMAIL' => $user_email_,
	'USER_NAME' => $user_name_,
	'USER_PASS' => pb_crypt_hash($user_pass_),
	'STATUS' => "00003",
	'REG_DATE' => pb_current_time(),
));

pb_user_grant_authority($admin_id_, PB_AUTHORITY_SLUG_ADMINISTRATOR);
pb_user_create_session(pb_user($admin_id_));

echo json_encode(array(
	'success' => true,
));

pb_admin_end();

?>