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

if(!pb_verify_request_token("pbpress_manage_site", $_POST['request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청토큰이 잘못되었습니다.',
	));
	pb_admin_end();	
}

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

echo json_encode(array(
	'success' => true,
	'redirect_url' => pb_admin_url("manage-site"),
));

pb_admin_end();

?>