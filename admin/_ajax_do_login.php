<?

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

if(!pb_session_verify_instance_token("pbpress_admin_login", $login_data_['_request_chip'])){
	echo json_encode(array(
		'success' => false,
		'error_title' => '에러발생',
		'error_message' => '요청토큰이 잘못되었습니다.',
	));
	pb_admin_end();	
}

$user_login_ = $login_data_['user_login'];
$user_pass_ = pb_crypt_decrypt($login_data_['user_pass']);

$bool_ = pb_user_login_by_both($user_login_, $user_pass_);

if(pb_is_error($bool_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => "로그인실패",
		'error_message' => $bool_->error_message(),
	));
	pb_admin_end();	
}

echo json_encode(array(
	'success' => true,
));

pb_admin_end();

?>