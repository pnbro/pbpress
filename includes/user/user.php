<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $users_do;
$users_do = pbdb_data_object("users", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'user_pass' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "nn" => true, "comment" => "패스워드"),
	'user_login' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "nn" => true, "index" => true, "comment" => "로그인ID"),
	'user_email' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "nn" => true, "index" => true, "comment" => "이메일"),
	'user_name' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "nn" => true, "comment" => "사용자명"),
	'status' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 5, "nn" => true, "index" => true, "comment" => "상태"),
	'findpass_vkey' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "index" => true, "comment" => "암호찾기 - 인증키"),
	'findpass_vkey_exp_date' => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "암호찾기 - 키만료일자"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"사용자");

$users_do->add_legacy_field_filter("pb_user_parse_fields", array()); // for legacy

function pb_user_statement($conditions_ = array()){
	global $users_do;

	$statement_ = $users_do->statement();

	$statement_->add_field(
		pb_query_gcode_dtl_name("U0001", "users.status")." status_name",
		"DATE_FORMAT(users.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(users.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(users.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(users.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(users.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(users.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_user_list_select', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_user_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_user_list_where', '', $conditions_);

	$statement_->add_conditions_from_data($conditions_, array(
		'id' => array(PBDB_SS::COND_COMPARE, 'users.id', "=", PBDB::TYPE_NUMBER),
		'user_login' => array(PBDB_SS::COND_COMPARE, 'users.user_login', "=", PBDB::TYPE_STRING),
		'user_email' => array(PBDB_SS::COND_COMPARE, 'users.user_email', "=", PBDB::TYPE_STRING),
		'status' => array(PBDB_SS::COND_IN, "users.status"),
		'keyword' => array(PBDB_SS::COND_LIKE, array(
			'users.user_login',
			'users.user_email',
			'users.user_name',
		)),
	));

	return pb_hook_apply_filters('pb_user_statement', $statement_, $conditions_);
}
function pb_user_list($conditions_ = array()){
	$statement_ = pb_user_statement($conditions_);
	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
    }

    $orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
    $limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;
    
	return pb_hook_apply_filters('pb_user_list', $statement_->select($orderby_, $limit_));
}

function pb_user_by($by_, $val_){
	$data_ = pb_user_list(array($by_ => $val_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}

function pb_user($id_){
	return pb_user_by("id", $id_);
}

function pb_user_by_user_login($user_login_){
	return pb_user_by("user_login", $user_login_);
}

function pb_user_by_user_email($user_email_){
	return pb_user_by("user_email", $user_email_);
}

function pb_user_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_add", $raw_data_);
	global $users_do;
	$inserted_id_ = $users_do->insert($raw_data_);
	pb_hook_do_action("pb_user_added", $inserted_id_);
	return $inserted_id_;
}
function _pb_user_before_add_common($raw_data_){
	$exists_check1_ = pb_user_by_user_login($raw_data_['user_login']);
	if(isset($exists_check1_)){
		return new PBError(-1, "사용자추가실패", "이미 존재하는 사용자아이디입니다.");
	}

	$exists_check2_ = pb_user_by_user_email($raw_data_['user_email']);
	if(isset($exists_check2_)){
		return new PBError(-1, "사용자추가실패", "이미 존재하는 이메일입니다.");
	}

	return $raw_data_;
}
pb_hook_add_filter('pb_user_before_add', "_pb_user_before_add_common");

function pb_user_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $users_do, $pbdb;
	$users_do->update($id_, $raw_data_);

	pb_hook_do_action("pb_user_updated", $id_);
}

function pb_user_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $users_do;
	$users_do->delete($id_);
	pb_hook_do_action("pb_user_deleted", $id_);
}

define('PB_USER_SESSION_KEY', "_PB_USER_SESSION_");

function pb_user_create_session($user_data_){
	pb_session_put(PB_USER_SESSION_KEY, $user_data_);
	pb_hook_do_action("pb_user_session_created", $user_data_);
	return true;
}
function pb_user_login($id_, $plain_password_){
	$user_data_ = pb_user($id_);
	if(!isset($user_data_)) return new PBError(-1, "로그인실패", "사용자정보가 없습니다.");

	$result_ = pb_hook_apply_filters('pb_user_check_login', true, $user_data_, $plain_password_);

	if(pb_is_error($result_)){
		return $result_;
	}

	return pb_user_create_session($user_data_);
}
function pb_user_login_by_both($both_, $plain_password_){
	$user_data_ = pb_user_by_user_login($both_);
	if(!isset($user_data_)){
		$user_data_ = pb_user_by_user_email($both_);
		if(!isset($user_data_)){
			return new PBError(-1, "로그인실패", "사용자정보가 없습니다.");
		}
	}

	$result_ = pb_hook_apply_filters('pb_user_check_login', true, $user_data_, $plain_password_);

	if(pb_is_error($result_)){
		return $result_;
	}

	return pb_user_create_session($user_data_);
}
function _pb_user_check_login_common($result_, $user_data_, $plain_password_){
	if($user_data_['user_pass'] !== pb_crypt_hash($plain_password_)){
		return new PBError(-1, "로그인실패", "비밀번호가 정확하지 않습니다.");
	}

	if($user_data_['status'] !== "00003"){
		return new PBError(-2, "로그인실패", "로그인할 수 없는 상태입니다.");
	}

	return true;
}
pb_hook_add_filter('pb_user_check_login', "_pb_user_check_login_common");

function pb_user_logout(){
	$user_data_ = pb_current_user();
	if(!isset($user_data_)) return false;

	pb_session_put(PB_USER_SESSION_KEY, null);
	pb_hook_do_action("pb_user_session_removed", $user_data_);
	return true;
}

function pb_current_user($fetch_ = false){
	$check_data_ = pb_session_get(PB_USER_SESSION_KEY);
	if(!isset($check_data_)) return null;
	$result_ = null;
	if($fetch_) $result_ = pb_user($check_data_['id']);
	else $result_ = $check_data_;
	return pb_hook_apply_filters('pb_current_user', $result_);
}
function pb_current_user_id(){
	$user_data_ = pb_session_get(PB_USER_SESSION_KEY);
	if(!isset($user_data_)) return -1;

	return $user_data_['id'];
}

function pb_is_user_logged_in(){
	$current_user_ = pb_current_user();
	return (isset($current_user_));
}

//사용자 등록
function pb_user_register($data_ = array()){
	if(!isset($data_['user_login']) || !isset($data_['user_email']) || !isset($data_['user_name']) || !isset($data_['user_pass'])){
		return new PBError(-1, "등록실패", "필수정보가 누락되었습니다.");
	}

	$data_['user_pass'] = pb_crypt_hash($data_['user_pass']);
	return pb_user_add($data_);
}


define("PB_USER_FINDPASS_VKEY_NOTVALID", -1);
define("PB_USER_FINDPASS_VKEY_EXPIRED", -2);
define("PB_USER_FINDPASS_VKEY_NOTFOUND", -3);

//암호변경용 키 메일 발송
function pb_user_send_email_for_findpass($user_email_){
	$user_data_ = pb_user_by_user_email($user_email_);

	if(!isset($user_data_) || empty($user_data_)){
		return new PBError(PB_USER_FINDPASS_VKEY_NOTFOUND, '가입이력없음', '해당 이메일로 가입이력이 존재하지 않습니다.');
	}

	$user_id_ = $user_data_['id'];
	$validation_key_ = pb_user_gen_findpass_validation_key($user_id_);

	$validation_url_ = pb_make_url(pb_home_url("admin/resetpass.php"), array(
		'user_email' => $user_email_,
		'vkey' => $validation_key_,

	));

	$mail_content_ = pb_hook_apply_filters('pb-user-findpass-email-content', "");

	if(!strlen($mail_content_)){
		$mail_content_ = '<a href="'.$validation_url_.'">새로운 비밀번호 설정</a>';
	}

	$mail_title_ = pb_hook_apply_filters('pb-user-findpass-email-title', "[".pb_option_value('site_name')."] 비밀번호 재설정 안내");

	return pb_mail_template_send($user_email_, $mail_title_, array(
		'content' => $mail_content_,
	));
}

function pb_user_gen_findpass_validation_key($user_id_){
	$validation_key_ = pb_random_string(20);
	$expire_datetime_ = date('Y-m-d H:i:s', strtotime(pb_current_time(). ' + 1 days'));

	pb_user_update($user_id_, array(
		"findpass_vkey" => $validation_key_,
		"findpass_vkey_exp_date" => $expire_datetime_,
	));

	return $validation_key_;
}

function pb_user_check_findpass_validation_key($user_id_, $validation_key_){
	$user_ = pb_user($user_id_);

	if(!isset($user_) || empty($user_)){
		return new PBError(PB_USER_FINDPASS_VKEY_NOTFOUND, '가입이력없음', '해당 이메일로 가입이력이 존재하지 않습니다.');
	}

	$stored_validation_key_ = $user_['findpass_vkey'];

	if($validation_key_ !== $stored_validation_key_){
		return new PBError(PB_USER_FINDPASS_VKEY_NOTVALID, '잘못된 인증키', '비밀번호인증키가 잘못되었습니다.');
	}

	global $pbdb;

	$exprie_day_count_ = $pbdb->get_var("SELECT 
		users.findpass_vkey_exp_date - NOW() CHK
		FROM users
		WHERE id = {$user_id_}");

	if($exprie_day_count_ < 0){
		return new PBError(PB_USER_FINDPASS_VKEY_EXPIRED, '만료된 인증키', '비밀번호인증키가 만료되었습니다.');
	}

	return true;
}

function pb_user_remove_findpass_validation_key($user_id_){
	pb_user_update($user_id_, array(
		"findpass_vkey" => null,
		"findpass_vkey_exp_date" => null
	));
}

include(PB_DOCUMENT_PATH . 'includes/user/user-meta.php');
include(PB_DOCUMENT_PATH . 'includes/user/user-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/user/user-authority.php');
__iinclude(PB_DOCUMENT_PATH . 'includes/user/user-adminpage.php');

?>