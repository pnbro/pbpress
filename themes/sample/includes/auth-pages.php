<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1');
}

/* ============================================================
 * devplan002 part004 — 회원: 로그인 / 회원가입 / 비밀번호 재설정
 *
 * 라우트: /login, /signup, /resetpass (모두 public, 로그인 상태면 홈 redirect)
 * AJAX  : user-do-login, user-check-email-exists, user-do-signup,
 *         user-request-resetpass, user-do-resetpass
 *
 * 코어 확인 결과(guide MCP + includes/user/user.php, includes/common/*.php 직접 열람):
 * - pb_is_logined() / pb_login_url() 은 코어에 존재하지 않는다(전체 grep 확인).
 *   header.php(part003)가 이미 function_exists('pb_login_url')/('pb_is_logined')
 *   가드로 이 이름을 호출하도록 준비되어 있으므로, 여기서 테마 레벨 헬퍼로 정의해
 *   실제로는 코어 pb_is_user_logged_in()에 위임한다.
 * - PB.crypt.fixed_encrypt()도 존재하지 않는다. 실제 JS API는 PB.crypt.encrypt(plain)
 *   (RSA, lib/dev/concat-lib/src/pb/modules/pb.crypt.js) 이므로 이것을 사용한다.
 * - pb_user_encrypt_columns() 도 코어에 없다(가이드 예시 표현). 실제로는
 *   pb_user_register()가 내부에서 pb_crypt_hash()로 비밀번호를 해시 저장한다.
 * - 코어 pb_user_add()는 pb_user_before_add 필터(중복 아이디/이메일 체크) 결과를
 *   버그성으로 무시하고 그대로 insert한다(includes/user/user.php:112-118 확인).
 *   따라서 회원가입 AJAX에서 이메일/로그인ID 중복을 직접 선체크한다.
 * - pb_user_send_email_for_findpass()는 관리자 재설정 페이지(admin/resetpass.php)로
 *   링크를 고정 생성하므로 테마 프론트 라우트(/resetpass)로 보낼 수 없다.
 *   대신 하위 함수 pb_user_gen_findpass_validation_key() + pb_mail_template_send()를
 *   직접 조합해 프론트 URL로 메일을 발송한다.
 * ============================================================ */


/* ------------------------------------------------------------
 * 로그인 상태 / URL 헬퍼 (테마 레벨. header.php part003 연동 지점)
 * ------------------------------------------------------------ */
function pb_is_logined(){
	return pb_is_user_logged_in();
}
function pb_login_url($params_ = array()){
	return pb_home_url("login", $params_);
}
function pb_signup_url($params_ = array()){
	return pb_home_url("signup", $params_);
}
function pb_resetpass_url($params_ = array()){
	return pb_home_url("resetpass", $params_);
}


/* ------------------------------------------------------------
 * 라우트 등록
 * ------------------------------------------------------------ */
pb_rewrite_register('login', array(
	'title' => __('로그인'),
	'public' => true,
	'rewrite_handler' => '_sample_theme_auth_rewrite_handler_login',
));
function _sample_theme_auth_rewrite_handler_login($rewrite_path_, $rewrite_data_){
	if(pb_is_logined()){
		pb_redirect(pb_home_url());
		pb_end();
	}

	return pb_current_theme_path()."pages/auth/login.php";
}

pb_rewrite_register('signup', array(
	'title' => __('회원가입', PB_THEME_DOMAIN),
	'public' => true,
	'rewrite_handler' => '_sample_theme_auth_rewrite_handler_signup',
));
function _sample_theme_auth_rewrite_handler_signup($rewrite_path_, $rewrite_data_){
	if(pb_is_logined()){
		pb_redirect(pb_home_url());
		pb_end();
	}

	return pb_current_theme_path()."pages/auth/signup.php";
}

pb_rewrite_register('resetpass', array(
	'title' => __('비밀번호 재설정'),
	'public' => true,
	'rewrite_handler' => '_sample_theme_auth_rewrite_handler_resetpass',
));
function _sample_theme_auth_rewrite_handler_resetpass($rewrite_path_, $rewrite_data_){
	if(pb_is_logined()){
		pb_redirect(pb_home_url());
		pb_end();
	}

	global $sample_auth_resetpass_state_;
	$sample_auth_resetpass_state_ = array(
		'step' => 'request',
		'user_email' => '',
		'vkey' => '',
		'error' => null,
	);

	$user_email_ = _GET('user_email');
	$vkey_ = _GET('vkey');

	if(strlen($user_email_) && strlen($vkey_)){
		$user_data_ = pb_user_by_user_email($user_email_);
		$check_ = isset($user_data_)
			? pb_user_check_findpass_validation_key($user_data_['id'], $vkey_)
			: new PBError(-3, __('가입이력없음'), __('해당 이메일로 가입이력이 존재하지 않습니다.'));

		if(pb_is_error($check_)){
			$sample_auth_resetpass_state_['step'] = 'invalid';
			$sample_auth_resetpass_state_['error'] = $check_->error_message();
		}else{
			$sample_auth_resetpass_state_['step'] = 'form';
			$sample_auth_resetpass_state_['user_email'] = $user_email_;
			$sample_auth_resetpass_state_['vkey'] = $vkey_;
		}
	}

	return pb_current_theme_path()."pages/auth/resetpass.php";
}


/* ------------------------------------------------------------
 * 페이지 전용 애셋 — header.php/footer.php(다른 part 소유)를 건드리지 않고
 * pb_head/pb_foot 훅으로 로그인/가입/재설정 페이지에서만 주입한다.
 * (참고: includes/common/crypt.php의 _pb_crypt_load_scripts도 동일 패턴)
 * ------------------------------------------------------------ */
function _sample_theme_auth_is_current_page(){
	return in_array(pb_current_slug(), array('login', 'signup', 'resetpass'));
}
pb_hook_add_action('pb_head', function(){
	if(!_sample_theme_auth_is_current_page()) return;
	?><link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/auth.css">
<?php
});
pb_hook_add_action('pb_foot', function(){
	if(!_sample_theme_auth_is_current_page()) return;
	?><script src="<?=pb_current_theme_url()?>lib/js/features/auth.js"></script>
<?php
});


/* ------------------------------------------------------------
 * AJAX — 이메일 로그인
 * 흐름: PB.crypt.encrypt(pw) → user-do-login → pb_crypt_decrypt() → pb_user_login_by_both()
 * (pb_user_login()은 user id 조회 전용이라 이메일/아이디 겸용 로그인엔
 *  pb_user_login_by_both()가 정석 — 실사용처: admin/_ajax_do_login.php)
 * ------------------------------------------------------------ */
pb_add_ajax('user-do-login', '_sample_theme_auth_ajax_do_login');
function _sample_theme_auth_ajax_do_login(){
	$login_data_ = _POST('login_data');

	if(!isset($login_data_) || !pb_verify_request_token('sample-theme-auth-login', (string)@$login_data_['_request_chip'])){
		pb_ajax_error(__('에러발생'), __('요청값이 잘못되었습니다.'));
	}

	$user_email_ = strtolower(trim((string)@$login_data_['user_email']));
	$plain_password_ = pb_crypt_decrypt((string)@$login_data_['user_pass']);

	if(!strlen($user_email_) || $plain_password_ === false || !strlen($plain_password_)){
		pb_ajax_error(__('로그인실패'), __('이메일과 비밀번호를 확인하세요.', PB_THEME_DOMAIN));
	}

	$result_ = pb_user_login_by_both($user_email_, $plain_password_);

	if(pb_is_error($result_)){
		pb_ajax_error($result_);
	}

	pb_ajax_success(array('redirect_url' => pb_home_url()));
}


/* ------------------------------------------------------------
 * AJAX — 이메일 중복확인 (회원가입 폼에서 실시간 확인)
 * ------------------------------------------------------------ */
pb_add_ajax('user-check-email-exists', '_sample_theme_auth_ajax_check_email_exists');
function _sample_theme_auth_ajax_check_email_exists(){
	$user_email_ = strtolower(trim(_POST('user_email')));

	if(!strlen($user_email_)){
		pb_ajax_error(__('에러발생'), __('이메일을 입력하세요.', PB_THEME_DOMAIN));
	}

	$user_data_ = pb_user_by_user_email($user_email_);

	pb_ajax_success(array('exists' => isset($user_data_)));
}


/* ------------------------------------------------------------
 * AJAX — 회원가입
 * 흐름: user-check-email-exists(중복확인) → user-do-signup → 자동로그인
 * 이메일 로그인 전용 테마이므로 별도 아이디 없이 이메일을 user_login으로도 사용.
 * ------------------------------------------------------------ */
pb_add_ajax('user-do-signup', '_sample_theme_auth_ajax_do_signup');
function _sample_theme_auth_ajax_do_signup(){
	$signup_data_ = _POST('signup_data');

	if(!isset($signup_data_) || !pb_verify_request_token('sample-theme-auth-signup', (string)@$signup_data_['_request_chip'])){
		pb_ajax_error(__('에러발생'), __('요청값이 잘못되었습니다.'));
	}

	$user_email_ = strtolower(trim((string)@$signup_data_['user_email']));
	$user_name_ = trim((string)@$signup_data_['user_name']);
	$plain_password_ = pb_crypt_decrypt((string)@$signup_data_['user_pass']);

	if(!strlen($user_email_) || !strlen($user_name_) || $plain_password_ === false || strlen($plain_password_) < 4){
		pb_ajax_error(__('가입실패', PB_THEME_DOMAIN), __('입력값을 확인하세요.', PB_THEME_DOMAIN));
	}

	// 코어 pb_user_add()가 중복체크 필터 결과를 실제로 반영하지 않으므로(위 주석 참조) 직접 선체크한다.
	$exists_email_ = pb_user_by_user_email($user_email_);
	if(isset($exists_email_)){
		pb_ajax_error(__('가입실패', PB_THEME_DOMAIN), __('이미 가입된 이메일입니다.', PB_THEME_DOMAIN));
	}

	$exists_login_ = pb_user_by_user_login($user_email_);
	if(isset($exists_login_)){
		pb_ajax_error(__('가입실패', PB_THEME_DOMAIN), __('이미 가입된 이메일입니다.', PB_THEME_DOMAIN));
	}

	$inserted_id_ = pb_user_register(array(
		'user_login' => $user_email_,
		'user_email' => $user_email_,
		'user_name' => $user_name_,
		'user_pass' => $plain_password_, // pb_user_register 내부에서 pb_crypt_hash() 처리(평문 저장 아님)
		'status' => PB_USER_STATUS::NORMAL, // 데모 테마: 이메일인증 절차 없이 즉시 로그인 가능 상태로 가입
	));

	if(pb_is_error($inserted_id_)){
		pb_ajax_error($inserted_id_);
	}

	// 가입 직후 자동 로그인(데모 편의) — 방금 저장한 평문 비밀번호로 재검증 후 세션 생성
	pb_user_login_by_both($user_email_, $plain_password_);

	pb_ajax_success(array('redirect_url' => pb_home_url()));
}


/* ------------------------------------------------------------
 * AJAX — 비밀번호 재설정 1단계: 이메일 → vkey 메일 발송
 * ------------------------------------------------------------ */
pb_add_ajax('user-request-resetpass', '_sample_theme_auth_ajax_request_resetpass');
function _sample_theme_auth_ajax_request_resetpass(){
	$user_email_ = strtolower(trim(_POST('user_email')));

	if(!strlen($user_email_)){
		pb_ajax_error(__('에러발생'), __('이메일을 입력하세요.', PB_THEME_DOMAIN));
	}

	$user_data_ = pb_user_by_user_email($user_email_);

	if(!isset($user_data_)){
		pb_ajax_error(__('발송실패', PB_THEME_DOMAIN), __('해당 이메일로 가입이력이 존재하지 않습니다.'));
	}

	$validation_key_ = pb_user_gen_findpass_validation_key($user_data_['id']);

	$reset_url_ = pb_resetpass_url(array(
		'user_email' => $user_email_,
		'vkey' => $validation_key_,
	));

	$mail_title_ = sprintf(__("[%s] 비밀번호 재설정 안내", PB_THEME_DOMAIN), pb_option_value('site_name'));
	$mail_content_ = '<a href="'.$reset_url_.'">'.__('새로운 비밀번호 설정').'</a>';

	pb_mail_template_send($user_email_, $mail_title_, array(
		'content' => $mail_content_,
	));

	pb_ajax_success();
}


/* ------------------------------------------------------------
 * AJAX — 비밀번호 재설정 2단계: vkey 검증 후 비밀번호 변경
 * ------------------------------------------------------------ */
pb_add_ajax('user-do-resetpass', '_sample_theme_auth_ajax_do_resetpass');
function _sample_theme_auth_ajax_do_resetpass(){
	$resetpass_data_ = _POST('resetpass_data');

	if(!isset($resetpass_data_) || !pb_verify_request_token('sample-theme-auth-resetpass', (string)@$resetpass_data_['_request_chip'])){
		pb_ajax_error(__('에러발생'), __('요청값이 잘못되었습니다.'));
	}

	$user_email_ = strtolower(trim((string)@$resetpass_data_['user_email']));
	$vkey_ = (string)@$resetpass_data_['vkey'];
	$plain_password_ = pb_crypt_decrypt((string)@$resetpass_data_['user_pass']);

	$user_data_ = pb_user_by_user_email($user_email_);

	if(!isset($user_data_)){
		pb_ajax_error(__('실패', PB_THEME_DOMAIN), __('해당 이메일로 가입이력이 존재하지 않습니다.'));
	}

	$check_ = pb_user_check_findpass_validation_key($user_data_['id'], $vkey_);

	if(pb_is_error($check_)){
		pb_ajax_error($check_);
	}

	if($plain_password_ === false || strlen($plain_password_) < 4){
		pb_ajax_error(__('실패', PB_THEME_DOMAIN), __('비밀번호를 확인하세요.', PB_THEME_DOMAIN));
	}

	pb_user_change_password($user_data_['id'], $plain_password_);

	pb_ajax_success(array('redirect_url' => pb_login_url()));
}

?>
