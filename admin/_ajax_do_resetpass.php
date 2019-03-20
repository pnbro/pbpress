<?

include(dirname( __FILE__ ) . "/includes.php");

header("Content-Type:application/json; charset=UTF-8");

$request_data_ = isset($_POST['request_data']) ? $_POST['request_data'] : null;

if(!isset($request_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청값이 잘못되었습니다.',
	));
	pb_admin_end();	
}

if(!pb_session_verify_instance_token("pbpress_admin_resetpass", $request_data_['_request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청토큰이 잘못되었습니다.',
	));
	pb_admin_end();	
}

$user_email_ = $request_data_['user_email'];
$vkey_ = $request_data_['vkey'];
$user_pass_ = pb_crypt_decrypt($request_data_['user_pass']);

$user_data_ = pb_user_by_user_email($user_email_);

if(!isset($user_data_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => "비밀번호 재설정 실패",
		'error_message' => "회원정보가 없습니다.",
	));
	pb_admin_end();	
}

$check_vkey_ = pb_user_check_findpass_validation_key($user_data_['ID'], $vkey_);

if(pb_is_error($check_vkey_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => $check_vkey_->error_title(),
		'error_message' => $check_vkey_->error_message(),
	));
	pb_admin_end();		
}

$user_pass_ = pb_crypt_hash($user_pass_);

pb_user_update($user_data_['ID'], array(
	'USER_PASS' => $user_pass_,
));

pb_user_remove_findpass_validation_key($user_data_['ID']);

echo json_encode(array(
	'success' => true,
	'redirect_url' => pb_admin_login_url(),
));

pb_admin_end();

?>