<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan002 part005 — 회원: 마이페이지 (Mypage 패턴)
 *
 * 코어(pbpress)에는 pb_mypage_* 헬퍼가 존재하지 않아(search_api 확인 결과 0건)
 * 이 테마가 자체적으로 정의한다(가이드 §5 Mypage패턴의 디렉토리/헬퍼 규약을 그대로 구현).
 *
 * 레이아웃: 이 sample 테마에는 header-mypage.php/footer-mypage.php 가 없다
 * (part001 산출물 확인 결과, header-*.php 는 header.php/header-other.php 뿐).
 * 따라서 가이드의 pb_theme_header('mypage') 전용 wrapper 대신, 일반 페이지
 * 방식(pb_theme_header() 직접 호출 + 뷰가 직접 container로 감싸는 방식)으로
 * 좌측 aside + 우측 콘텐츠 셸을 자체 구성한다. (pages/mypage/*.php 참고)
 *
 * 로그인 필수: pb_is_user_logged_in() 사용(코어에 pb_is_logined()는 없음, search_api 확인).
 * 로그인 URL: part004(회원 로그인)가 pb_login_url()을 정의할 예정 — 있으면 사용,
 * 없으면 pb_home_url('login', ...)로 안전하게 폴백한다(병렬 개발 안전).
 * ============================================================ */

/* ---------- 메뉴 등록/조회 헬퍼 ---------- */
function pb_mypage_add_menu($key_, $data_, $priority_ = 100){
	global $pb_mypage_menulist;
	if(!isset($pb_mypage_menulist)){
		$pb_mypage_menulist = array();
	}

	$data_['priority'] = $priority_;
	$pb_mypage_menulist[$key_] = $data_;
}

function pb_mypage_menu_data(){
	global $pb_mypage_menulist;
	if(!isset($pb_mypage_menulist)){
		$pb_mypage_menulist = array();
	}

	$menulist_ = $pb_mypage_menulist;
	uasort($menulist_, function($a_, $b_){
		$pa_ = isset($a_['priority']) ? $a_['priority'] : 100;
		$pb_ = isset($b_['priority']) ? $b_['priority'] : 100;
		return $pa_ - $pb_;
	});

	return pb_hook_apply_filters('pb_mypage_menu_data', $menulist_);
}

function pb_mypage_url($key_ = null){
	if(!strlen($key_) || $key_ === 'home'){
		return pb_home_url('mypage');
	}
	return pb_home_url('mypage/'.$key_);
}

function pb_mypage_rewrite_path(){
	$rewrite_path_ = pb_rewrite_path();
	return array_slice((array)$rewrite_path_, 1);
}

function pb_mypage_current_key(){
	global $pb_mypage_current_menu_key;
	return isset($pb_mypage_current_menu_key) ? $pb_mypage_current_menu_key : 'home';
}

function pb_mypage_avatar_letter($str_){
	$str_ = trim((string)$str_);
	if(!strlen($str_)){
		return '?';
	}
	return mb_strtoupper(mb_substr($str_, 0, 1, 'UTF-8'), 'UTF-8');
}

/* ---------- 로그인 URL(part004 종속, 없으면 폴백) ---------- */
function _pb_mypage_login_redirect_url(){
	if(function_exists('pb_login_url')){
		return pb_login_url();
	}
	return pb_home_url('login', array('redirect_url' => pb_mypage_url()));
}

/* ---------- 라우트 핸들러 ---------- */
function _pb_rewrite_handler_mypage($rewrite_path_, $rewrite_data_ = null){
	if(!pb_is_user_logged_in()){
		pb_redirect(_pb_mypage_login_redirect_url());
		pb_end();
	}

	$menu_key_ = (isset($rewrite_path_[1]) && strlen($rewrite_path_[1])) ? $rewrite_path_[1] : 'home';

	$menulist_ = pb_mypage_menu_data();

	if(!isset($menulist_[$menu_key_])){
		return new PBError(404, __("잘못된 접근"), __("존재하지 않는 마이페이지입니다.", PB_THEME_DOMAIN));
	}

	global $pb_mypage_current_menu_key, $pb_mypage_current_menu;
	$pb_mypage_current_menu_key = $menu_key_;
	$pb_mypage_current_menu = $menulist_[$menu_key_];

	$menu_data_ = $menulist_[$menu_key_];

	if(isset($menu_data_['rewrite_handler']) && is_callable($menu_data_['rewrite_handler'])){
		return call_user_func($menu_data_['rewrite_handler']);
	}

	if(isset($menu_data_['page'])){
		return $menu_data_['page'];
	}

	return new PBError(404, __("잘못된 접근"), __("존재하지 않는 마이페이지입니다.", PB_THEME_DOMAIN));
}

pb_rewrite_register('mypage', array(
	'title' => '마이페이지',
	'public' => false,
	'rewrite_handler' => '_pb_rewrite_handler_mypage',
));

/* ---------- part005 전용 css/js 주입 ----------
 * header.php/footer.php는 편집 금지 대상이므로, 코어가 제공하는
 * pb_head/pb_foot 액션 훅(includes/common/functions.php)에 마이페이지
 * 라우트에서만 태그를 추가하는 방식으로 자산을 로드한다.
 * (참고: includes/common/crypt.php의 pb_hook_add_action("pb_head", ...) 동일 패턴)
 * ---------- */
function _pb_mypage_head_assets(){
	if(!pb_is_current_slug('mypage')){
		return;
	}
	?>
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/mypage.css">
	<?php
}
pb_hook_add_action('pb_head', '_pb_mypage_head_assets');

function _pb_mypage_foot_assets(){
	if(!pb_is_current_slug('mypage')){
		return;
	}
	?>
	<script type="text/javascript" src="<?=pb_current_theme_url()?>lib/js/features/mypage.js"></script>
	<?php
}
pb_hook_add_action('pb_foot', '_pb_mypage_foot_assets');

/* ---------- 서브핸들러 자동 로딩 ---------- */
foreach(glob(PB_THEME_PATH.'page-handlers/mypage/*.php') as $f_){
	include_once $f_;
}

?>
