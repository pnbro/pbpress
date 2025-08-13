<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_authority_task_add_type('manage_user', array(
	'name' => __('사용자관리'),
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_user");

abstract class __PB_USER_STATUS extends PBConstClass{
	const WAIT = "00001";
	const NORMAL = "00003";
	const UNAVAILABLE = "00009";

	static public function names(){

		global $_pb_user_status_names;
		if(isset($_pb_user_status_names)) return $_pb_user_status_names;

		$_pb_user_status_names = array(
			__PB_USER_STATUS::WAIT => __('등록대기'),
			__PB_USER_STATUS::NORMAL => __('정상등록'),
			__PB_USER_STATUS::UNAVAILABLE => __('사용불가'),
		);

		return $_pb_user_status_names;
	}
}

abstract class PB_USER_STATUS extends PBConstClass{

	const WAIT = "00001";
	const NORMAL = "00003";
	const UNAVAILABLE = "00009";

	static $_binded_const = '__PB_USER_STATUS';

	static public function bind($const_){
		static::$_binded_const = $const_;
	}
	static public function binded_const($const_){
		return static::$_binded_const;
	}

	static public function codes(){
		return call_user_func(array(static::$_binded_const, "codes"));
	}
	
	static public function names(){
		return call_user_func(array(static::$_binded_const, "names"));
	}
}



pb_hook_add_action('user_password_not_corrected', function($user_data_){
	$pw_not_corrected_count_ = pb_user_meta_value($user_data_['id'], 'pw_not_corrected_count', 0);
	if(!is_numeric($pw_not_corrected_count_)){
		$pw_not_corrected_count_ = 0;
	}

	pb_user_meta_update($user_data_['id'], 'pw_not_corrected_count', ++$pw_not_corrected_count_);

	global $pb_config; 

	if($pb_config->login_max_fail_count() <= $pw_not_corrected_count_){
		pb_user_meta_update($user_data_['id'], 'login_unabled_expire_date', strtotime("+".$pb_config->login_failed_to_ban_time()." minutes"));
		pb_user_meta_update($user_data_['id'], 'pw_not_corrected_count', $pb_config->login_max_fail_count());
	}
});

pb_hook_add_action('pb_user_session_created', function($user_data_){
	pb_user_meta_update($user_data_['id'], 'pw_not_corrected_count', 0);
	pb_user_meta_update($user_data_['id'], 'login_unabled_expire_date', null);
});

pb_hook_add_action('pb_user_password_changed', function($user_id_){
	pb_user_meta_update($user_id_, 'pw_not_corrected_count', 0);
	pb_user_meta_update($user_id_, 'login_unabled_expire_date', null);
});

pb_hook_add_filter('pb_user_check_login', function($result_, $user_data_, $plain_password_){

	global $pb_config; 

	$pw_not_corrected_count_ = pb_user_meta_value($user_data_['id'], 'pw_not_corrected_count', 0);

	if($pb_config->login_max_fail_count() <= $pw_not_corrected_count_){
		$login_unabled_datetime_ = (int)(pb_user_meta_value($user_data_['id'], 'login_unabled_expire_date'));
		$current_time_ = time();

		if($login_unabled_datetime_ > $current_time_){
			return new PBError(-7, __("로그인실패"), sprintf(__("로그인시도 횟수 제한으로 %s분동안 로그인이 제한됩니다."), round(abs($login_unabled_datetime_ - $current_time_) / 60)));
		}
	}

	return $result_;
});

pb_hook_add_filter('user_password_not_corrected_text', function($text_, $user_data_){
	$pw_not_corrected_count_ = pb_user_meta_value($user_data_['id'], 'pw_not_corrected_count', 0);
	global $pb_config; 
	$login_max_fail_count_ = $pb_config->login_max_fail_count();

	if($login_max_fail_count_ > 0){
		return sprintf(__("비밀번호가 정확하지 않습니다. (%s/%s)"), $pw_not_corrected_count_, $login_max_fail_count_);
	}else return $text_;
});

?>