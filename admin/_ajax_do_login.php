<?php

include(dirname( __FILE__ ) . "/includes.php");

header("Content-Type:application/json; charset=UTF-8");

$login_data_ = _POST('login_data');

if(!isset($login_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => __('에러발생'),
		'error_message' => __('요청값이 잘못되었습니다.'),
	));
	pb_admin_end();	
}

if(!pb_verify_request_token("pbpress_admin_login", $login_data_['_request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => __('에러발생'),
		'error_message' => __('요청토큰이 잘못되었습니다.'),
	));
	pb_admin_end();	
}

$user_login_ = $login_data_['user_login'];
$user_pass_ = pb_crypt_decrypt($login_data_['user_pass']);

global $pbdb;

$result_ = pb_user_login_by_both($user_login_, $user_pass_);

if(pb_is_error($result_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => $result_->error_title(),
		'error_message' => $result_->error_message(),
	));
	pb_admin_end();	
}

pb_user_create_session(pb_user_simply_data($result_));

echo json_encode(array(
	'success' => true,
));

pb_admin_end();

?>