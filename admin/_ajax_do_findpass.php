<?php

include(dirname( __FILE__ ) . "/includes.php");

header("Content-Type:application/json; charset=UTF-8");

$user_email_ = _POST('user_email');

if(!strlen($user_email_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청값이 잘못되었습니다.',
	));
	pb_admin_end();	
}

$user_data_ = pb_user_by_user_email($user_email_);

if(!isset($user_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '존재하지 않는 계정',
		'error_message' => '해당 이메일로 가입한 이력이 없습니다.',
	));
	pb_admin_end();		
}

pb_user_send_email_for_findpass($user_email_);

echo json_encode(array(
	'success' => true,
));

pb_admin_end();

?>