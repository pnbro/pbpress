<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 회원정보 수정 */
pb_mypage_add_menu('change-myinfo', array(
	'title' => '회원정보 수정',
	'page' => PB_THEME_PATH.'pages/mypage/change-myinfo.php',
), 110);

/* ---------- AJAX: 회원정보 수정 (코어 pb_user_update 사용) ---------- */
pb_add_ajax('user-mypage-update-myinfo', '_pb_mypage_ajax_update_myinfo');

function _pb_mypage_ajax_update_myinfo(){
	if(!pb_is_user_logged_in()){
		pb_ajax_error(__("권한없음"), __("로그인이 필요합니다."));
	}

	$user_id_ = pb_current_user_id();

	$user_name_ = trim((string)@$_POST['user_name']);
	$user_email_ = strtolower(trim((string)@$_POST['user_email']));
	$new_password_ = (string)@$_POST['new_password'];
	$new_password_confirm_ = (string)@$_POST['new_password_confirm'];

	if(!strlen($user_name_)){
		pb_ajax_error(__("입력오류"), __("이름을 입력하세요."));
	}

	if(!strlen($user_email_) || !filter_var($user_email_, FILTER_VALIDATE_EMAIL)){
		pb_ajax_error(__("입력오류"), __("올바른 이메일을 입력하세요."));
	}

	// 이메일 중복 체크(본인 제외). 코어 users 테이블은 user_email에 unique 제약이 없어
	// theme 레벨에서 방어적으로 체크한다(pb_user_add의 pb_user_before_add 필터와 동일 취지).
	$exists_ = pb_user_by_user_email($user_email_);
	if(isset($exists_) && (int)$exists_['id'] !== (int)$user_id_){
		pb_ajax_error(__("저장실패"), __("이미 사용중인 이메일입니다."));
	}

	$update_data_ = array(
		'user_name' => $user_name_,
		'user_email' => $user_email_,
	);

	if(strlen($new_password_)){
		if($new_password_ !== $new_password_confirm_){
			pb_ajax_error(__("입력오류"), __("새 비밀번호가 일치하지 않습니다."));
		}
		if(strlen($new_password_) < 6){
			pb_ajax_error(__("입력오류"), __("비밀번호는 6자 이상이어야 합니다."));
		}
		$update_data_['user_pass'] = pb_crypt_hash($new_password_);
	}

	$result_ = pb_user_update($user_id_, $update_data_);

	if(pb_is_error($result_)){
		pb_ajax_error($result_);
	}

	pb_ajax_success(array(
		'message' => __("회원정보가 저장되었습니다."),
		'user_name' => $user_name_,
		'user_email' => $user_email_,
	));
}

?>
