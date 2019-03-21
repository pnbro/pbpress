<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_user_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
		SELECT   USERS.ID ID

				,USERS.USER_LOGIN USER_LOGIN
				,USERS.USER_EMAIL USER_EMAIL
				,USERS.USER_PASS USER_PASS
				,USERS.USER_NAME USER_NAME

				,USERS.STATUS STATUS
				,".pb_query_gcode_dtl_name("U0001", "USERS.STATUS")." STATUS_NAME

				,USERS.FINDPASS_VKEY FINDPASS_VKEY
				,USERS.FINDPASS_VKEY_EXP_DATE FINDPASS_VKEY_EXP_DATE

				,USERS.REG_DATE REG_DATE
				,DATE_FORMAT(USERS.REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS
				,DATE_FORMAT(USERS.REG_DATE, '%Y.%m.%d %H:%i') REG_DATE_YMDHI
				,DATE_FORMAT(USERS.REG_DATE, '%Y.%m.%d') REG_DATE_YMD

				,USERS.MOD_DATE MOD_DATE
				,DATE_FORMAT(USERS.MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
				,DATE_FORMAT(USERS.MOD_DATE, '%Y.%m.%d %H:%i') MOD_DATE_YMDHI
				,DATE_FORMAT(USERS.MOD_DATE, '%Y.%m.%d') MOD_DATE_YMD

				".pb_hook_apply_filters('pb_user_list_select',"",$conditions_)."

	FROM USERS

	".pb_hook_apply_filters('pb_user_list_join',"",$conditions_)."
	
	WHERE 1 ";

	if(isset($conditions_['ID']) && strlen($conditions_['ID'])){
		$query_ .= " AND USERS.ID = '".mysql_real_escape_string($conditions_['ID'])."' ";
	}
	if(isset($conditions_['user_login']) && strlen($conditions_['user_login'])){
		$query_ .= " AND USERS.USER_LOGIN = '".mysql_real_escape_string($conditions_['user_login'])."' ";
	}
	if(isset($conditions_['user_email']) && strlen($conditions_['user_email'])){
		$query_ .= " AND USERS.USER_EMAIL = '".mysql_real_escape_string($conditions_['user_email'])."' ";
	}
	if(isset($conditions_['status']) && strlen($conditions_['status'])){
		$query_ .= " AND USERS.STATUS = '".mysql_real_escape_string($conditions_['status'])."' ";
	}

	$query_ .= ' '.pb_hook_apply_filters('pb_user_list_where',"",$conditions_)." ";

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

    if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
        $query_ .= " ".$conditions_['orderby']." ";
    }else{
    	$query_ .= " ORDER BY REG_DATE DESC";
    }

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters('pb_user_list', $pbdb->select($query_));
}

function pb_user_by($by_, $val_){
	$data_ = pb_user_list(array($by_ => $val_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}

function pb_user($id_){
	return pb_user_by("ID", $id_);
}

function pb_user_by_user_login($user_login_){
	return pb_user_by("user_login", $user_login_);
}

function pb_user_by_user_email($user_email_){
	return pb_user_by("user_email", $user_email_);
}


function _pb_user_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_user_parse_fields",array(

		'USER_LOGIN' => '%s',
		'USER_PASS' => '%s',
		'USER_EMAIL' => '%s',
		'USER_NAME' => '%s',
		'STATUS' => '%s',
			
		'FINDPASS_VKEY' => '%s',
		'FINDPASS_VKEY_EXP_DATE' => '%s',

		'REG_DATE' => '%s',
		'MOD_DATE' => '%s',
		
	)), $data_);
}

function pb_user_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_user_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("USERS", $data_, $format_);
	pb_hook_do_action("pb_user_added", $insert_id_);

	return $insert_id_;
}
function _pb_user_before_add_common($raw_data_){
	$exists_check1_ = pb_user_by_user_login($raw_data_['USER_LOGIN']);
	if(isset($exists_check1_)){
		return new PBError(-1, "사용자추가실패", "이미 존재하는 사용자아이디입니다.");
	}

	$exists_check2_ = pb_user_by_user_email($raw_data_['USER_EMAIL']);
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

	global $pbdb;

	$raw_data_ = _pb_user_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$pbdb->update("USERS", $data_, array("ID" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_user_updated", $id_);
}

function pb_user_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;
	$pbdb->delete("USERS", array("ID" => $id_), array("%d"));
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
	if($user_data_['USER_PASS'] !== pb_crypt_hash($plain_password_)){
		return new PBError(-1, "로그인실패", "비밀번호가 정확하지 않습니다.");
	}

	if($user_data_['STATUS'] !== "00003"){
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

function pb_current_user(){
	return pb_session_get(PB_USER_SESSION_KEY);
}
function pb_current_user_id(){
	$user_data_ = pb_current_user();
	if(!isset($user_data_)) return null;

	return $user_data_['ID'];
}

function pb_is_user_logged_in(){
	$current_user_ = pb_current_user();
	return (isset($current_user_));
}

//사용자 등록
function pb_user_register($data_ = array()){
	if(!isset($data_['USER_LOGIN']) || !isset($data_['USER_EMAIL']) || !isset($data_['USER_NAME']) || !isset($data_['USER_PASS'])){
		return new PBError(-1, "등록실패", "필수정보가 누락되었습니다.");
	}

	$data_['USER_PASS'] = pb_crypt_hash($data_['USER_PASS']);
	return pb_user_add($data_);
}


define("PB_USER_FINDPASS_VKEY_NOTVALID", -1);
define("PB_USER_FINDPASS_VKEY_EXPIRED", -2);
define("PB_USER_FINDPASS_VKEY_NOTFOUND", -3);

//암호변경용 키 메일 발송
function pb_user_send_email_for_findpass($user_email_){
	$user_data_ = pb_user_by_user_email($user_email_);

	if(!isset($user_data_) || empty($user_data_)){
		return new WP_Error(PB_USER_FINDPASS_VKEY_NOTFOUND, '가입이력없음', '해당 이메일로 가입이력이 존재하지 않습니다.');
	}

	$user_id_ = $user_data_['ID'];
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
		"FINDPASS_VKEY" => $validation_key_,
		"FINDPASS_VKEY_EXP_DATE" => $expire_datetime_,
	));

	return $validation_key_;
}

function pb_user_check_findpass_validation_key($user_id_, $validation_key_){
	$user_ = pb_user($user_id_);

	if(!isset($user_) || empty($user_)){
		return new PBError(PB_USER_FINDPASS_VKEY_NOTFOUND, '가입이력없음', '해당 이메일로 가입이력이 존재하지 않습니다.');
	}

	$stored_validation_key_ = $user_['FINDPASS_VKEY'];

	if($validation_key_ !== $stored_validation_key_){
		return new PBError(PB_USER_FINDPASS_VKEY_NOTVALID, '잘못된 인증키', '비밀번호인증키가 잘못되었습니다.');
	}

	global $pbdb;

	$exprie_day_count_ = $pbdb->get_var("SELECT 
		USERS.FINDPASS_VKEY_EXP_DATE - NOW() CHK
		FROM USERS
		WHERE ID = {$user_id_}");

	if($exprie_day_count_ < 0){
		return new PBError(PB_USER_FINDPASS_VKEY_EXPIRED, '만료된 인증키', '비밀번호인증키가 만료되었습니다.');
	}

	return true;
}

function pb_user_remove_findpass_validation_key($user_id_){
	pb_user_update($user_id_, array(
		"FINDPASS_VKEY" => null,
		"FINDPASS_VKEY_EXP_DATE" => null
	));
}


include(PB_DOCUMENT_PATH . 'includes/user/user-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/user/user-authority.php');
include(PB_DOCUMENT_PATH . 'includes/user/user-adminpage.php');

?>