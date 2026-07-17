<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1');
}

/* ============================================================
 * devplan002 part001 — 디자인 파운데이션 & 레이아웃 셸
 * Bootstrap 4 / jQuery 의존 제거됨(순수 바닐라).
 * - 신규 디자인시스템: lib/css/tokens.css, base.css, components.css, theme.css
 * - 신규 UI 유틸: lib/js/ui.js (전역 네임스페이스 window.SampleUI)
 * - header.php / header-other.php / footer.php 에서 bootstrap.min.css,
 *   bootstrap.bundle.min.js 의 <link>/<script> 참조를 제거함.
 * - lib/css/bootstrap.min.css, lib/js/bootstrap.bundle.min.js 파일 자체는
 *   회귀 안전을 위해 즉시 삭제하지 않고 보관 중이며 part010 통합검수 후 제거 예정.
 * - 재사용 클래스/토큰/JS API 목록: lib/css/README-tokens.md 참조.
 * ============================================================ */

define('PB_THEME_VERSION', "1.0.0");
define('PB_THEME_PATH', pb_current_theme_path());
define('PB_THEME_URL', pb_current_theme_url());

/* ============================================================
 * devplan002 part008 — 다국어(i18n)
 * 코어 다국어 방식(includes/common/multilingual.php) 그대로 재사용:
 *  - pb_lang_load_translations($domain, $dir) 로 테마 전용 도메인 등록
 *  - lang/{locale}.lng 파일(형식은 core lang/en_US.lng과 동일: ";키;=;값;;")
 *  - __('키', PB_THEME_DOMAIN) 로 조회, 미번역시 원문(키) 그대로 폴백
 *  - pb_current_locale()/pb_locale_update()는 세션 기반(PB_SESSION_CURRENT_LOCALE)
 * 코어에는 "언어 전환 UI/엔드포인트"가 없어(번역파일 로딩 API만 존재),
 * 코어가 _pblang.js 라우트에 쓰는 것과 동일한 패턴(pb_rewrite_register +
 * 훅에서 슬러그 감지 후 직접 처리+pb_end())으로 안전하게 확장.
 * 단, 코어의 pb_before_init(includes/common/multilingual.php)은 테마
 * functions.php가 include되기 "이전"(includes/initialize.php:13)에 이미
 * 발화되어 테마가 이 시점에 훅을 걸어도 잡히지 않는다(실측 확인).
 * 테마 functions.php는 initialize.php:43에서 include된 뒤 pb_init(47행)이
 * 발화되므로, 테마 라우트는 반드시 'pb_init'에 걸어야 정상 동작한다.
 * ============================================================ */
define('PB_THEME_DOMAIN', 'sample_theme');
pb_lang_load_translations(PB_THEME_DOMAIN, pb_current_theme_path().'lang');

define('PB_SAMPLE_THEME_LANG_SWITCH_SLUG', 'sample-lang-switch');

function pb_sample_theme_lang_list(){
	return array(
		'ko_KR' => '한국어',
		'en_US' => 'English',
	);
}

pb_rewrite_register(PB_SAMPLE_THEME_LANG_SWITCH_SLUG, array(
	'title' => '언어전환',
	'public' => true,
));

function _pb_sample_theme_lang_switch_hook(){
	if(!pb_is_current_slug(PB_SAMPLE_THEME_LANG_SWITCH_SLUG)) return;

	$lang_list_ = pb_sample_theme_lang_list();
	$to_ = _GET('to');

	if(isset($lang_list_[$to_])){
		pb_locale_update($to_);
	}

	$redirect_to_ = _GET('redirect_to');
	$redirect_to_ = ltrim((string)$redirect_to_, '/');
	pb_redirect(pb_home_url($redirect_to_));
	pb_end();
}
pb_hook_add_action('pb_init', '_pb_sample_theme_lang_switch_hook');

pb_rewrite_register('other-page', array(
	'title' => '다른페이지',
	'public' => true,
	'page' => pb_current_theme_path()."other-page.php",
));


pb_hook_add_filter('pb_post_types', function($results_){
	$results_['blog'] = array(
		'name' => '블로그',
		// 'use_category' => false,
		// 'use_single_post' => false,
		'label' => array(
			'list' => "블로그내역",
			'add' => "블로그추가",
			'update' => "블로그수정",
			'delete' => "블로그삭제",
			'button_add' => "블로그추가",
			'button_update' => "블로그수정",
			'before_delete' => "해당 블로그을 삭제합니다. 계속하시겠습니까?",
			'after_delete' => "블로그가 삭제되었습니다.",
			'no_results' => "검색된 글이 없습니다.",
		),
		'adminpage_sort' => 10,
	);

	return $results_;
});

/* devplan004 part002 — 백엔드 데모 기반(실 DO 테이블·실 AJAX/CRUD·실지표) */
include pb_current_theme_path()."includes/demo-backend.php";
include pb_current_theme_path()."includes/guestbook-demo.php";

include pb_current_theme_path()."includes/menu-render.php";
include pb_current_theme_path()."includes/manage-site.php";
include pb_current_theme_path()."includes/ajax-test.php";
include pb_current_theme_path()."includes/page-builder-element-test.php";

/* devplan003 — 홈 카드/기능/통계 파셜 */
include pb_current_theme_path()."includes/partials.php";

/* devplan002 part004 — 회원 로그인/가입/비번재설정 */
include pb_current_theme_path()."includes/auth-pages.php";

/* devplan002 part005 — 마이페이지 (서브핸들러는 mypage.php 내부 glob 자동로딩) */
include pb_current_theme_path()."page-handlers/mypage.php";

/* devplan002 part006 — 블로그 목록/카테고리/검색 */
include pb_current_theme_path()."page-handlers/blog.php";

/* devplan002 part007 — 블로그 상세/댓글 */
include pb_current_theme_path()."includes/blog-comments.php";

/* devplan002 part009 — 파일 갤러리 & AJAX 데모 */
include pb_current_theme_path()."page-handlers/showcase.php";
include pb_current_theme_path()."includes/showcase.php";

?>