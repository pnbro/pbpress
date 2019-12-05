<?php

include(dirname( __FILE__ ) . "/includes.php");

if(!pb_user_has_authority_task(pb_current_user_id(), "access_adminpage")){
	echo json_encode(array(
		'success' => false,
		'error_title' => '권한없음',
		'error_message' => '사이트관리 권한이 없습니다.',
	));
	pb_admin_end();	
}


global $pb_config;

header("Content-Type:application/json; charset=".$pb_config->charset);

$settings_data_ = isset($_POST['settings_data']) ? $_POST['settings_data'] : null;

if(!isset($settings_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청값이 잘못되었습니다.',
	));
	pb_admin_end();	
}

if(!pb_verify_request_token("pbpress_manage_site", $settings_data_['_request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청토큰이 잘못되었습니다.',
	));
	pb_admin_end();	
}

$site_name_ = $settings_data_['site_name'];
$site_desc_ = $settings_data_['site_desc'];

pb_option_update("site_name", $site_name_);
pb_option_update("site_desc", $site_desc_);

pb_hook_do_action('pb-admin-update-site-settings', $settings_data_);

echo json_encode(array(
	'success' => true,
	'redirect_url' => pb_admin_url("manage-site"),
));

pb_admin_end();

?>