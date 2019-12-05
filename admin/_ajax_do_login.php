<?php

include(dirname( __FILE__ ) . "/includes.php");

header("Content-Type:application/json; charset=UTF-8");

$login_data_ = isset($_POST['login_data']) ? $_POST['login_data'] : null;

if(!isset($login_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청값이 잘못되었습니다.',
	));
	pb_admin_end();	
}

if(!pb_verify_request_token("pbpress_admin_login", $login_data_['_request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청토큰이 잘못되었습니다.',
	));
	pb_admin_end();	
}

$user_login_ = $login_data_['user_login'];
$user_pass_ = pb_crypt_decrypt($login_data_['user_pass']);

global $pbdb;

$user_data_ = $pbdb->get_first_row("SELECT users.id id

	,users.user_login user_login
	,users.user_email user_email
	,users.user_pass user_pass
	,users.user_name user_name
	
	,users.status status
	,".pb_query_gcode_dtl_name("U0001", "users.status")." status_name

	,users.findpass_vkey findpass_vkey
	,users.findpass_vkey_exp_date findpass_vkey_exp_date

	FROM users
	WHERE 1
	AND (users.user_login = '".pb_database_escape_string($user_login_)."' OR users.user_email = '".pb_database_escape_string($user_login_)."' )");

if(!isset($user_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => "로그인실패",
		'error_message' => "사용자정보가 없습니다.",
	));
	pb_admin_end();	
}

if($user_data_['user_pass'] !== pb_crypt_hash($user_pass_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => "로그인실패",
		'error_message' => "암호가 정확하지 않습니다.",
	));
	pb_admin_end();	
}

if($user_data_['status'] !== "00003"){
	echo json_encode(array(
		'success' => false,
		'error_title' => "로그인실패",
		'error_message' => "로그인할 수 없는 상태입니다.",
	));
	pb_admin_end();	
}

pb_user_create_session($user_data_);

echo json_encode(array(
	'success' => true,
));

pb_admin_end();

?>