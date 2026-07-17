<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan002 part006 — 콘텐츠: 블로그 목록 / 카테고리 / 검색
 * Rewrite: /blog (public)
 *   - /blog                      : 목록(검색바+카테고리 칩+카드그리드+무한스크롤)
 *   - /blog/category/{slug}      : 카테고리 필터 목록
 *   - /blog?keyword=...          : 키워드 검색(쿼리스트링, 목록 라우트와 결합 가능)
 *   - /blog/{key}                : 상세 — pages/blog/view.php 를 include(part007 담당).
 *                                   view.php가 아직 없으면 목록 화면에 안내문구를 띄우는
 *                                   안전 폴백으로 대체한다(404 대신 UX 유지).
 *
 * functions.php 통합 시 아래 1줄 추가 필요:
 *   include pb_current_theme_path()."page-handlers/blog.php";
 *
 * ------------------------------------------------------------
 * 중요(병렬개발 주의): functions.php에 등록된 'blog' post_type은 코어
 * includes/post/post-builtin-rewrite.php 의 _pb_post_types_to_rewrite()가
 * pb_init 훅(우선순위 기본값 10)에서 pb_post_types()를 순회하며
 *   pb_rewrite_register($key_, array('rewrite_handler' => '_pb_post_hook_for_rewrite_handler'))
 * 를 자동으로 (재)호출한다. pb_rewrite_register()는 병합이 아니라 완전 덮어쓰기이므로,
 * 아무 조치 없이 이 파일에서 pb_init 이전(=functions.php 로드 시점)에만 'blog' 라우트를
 * 등록하면 core가 나중에 그 등록을 지워버리고 단일글 전용 핸들러로 바꿔버린다.
 * → 본 파일은 pb_init에 우선순위 20(코어의 10보다 늦음)으로 재등록 훅을 걸어
 *   최종 승자가 되도록 한다. 그리고 병렬(early) 접근을 위해 functions.php 로드
 *   시점에도 1회 즉시 등록해 pb_init 이전 조회에도 안전하게 만든다.
 * ============================================================ */

function _pb_blog_register_rewrite(){
	pb_rewrite_register('blog', array(
		'title' => "블로그",
		'public' => true,
		'rewrite_handler' => '_pb_rewrite_handler_blog',
	));
}
_pb_blog_register_rewrite();
pb_hook_add_action('pb_init', '_pb_blog_register_rewrite', 20);

/**
 * /blog 라우트 핸들러.
 *
 * $rewrite_path_ 형태:
 *   ['blog']                        → 목록
 *   ['blog','category']             → 목록(슬러그 누락, 목록으로 안전 폴백)
 *   ['blog','category','{slug}']    → 카테고리 필터 목록
 *   ['blog','{key}']                → 상세(단, 두번째 세그먼트가 'category'가 아닐 때)
 *
 * part007 연동 규약(중요 — part007은 이 계약만 지키면 됨):
 *  - 상세 요청 시 global $pb_blog_key 에 요청 slug(원문, urldecode됨)를 담아둔다.
 *  - pages/blog/view.php 가 존재하면 그 파일을 include 경로로 반환한다.
 *    view.php는 자체적으로 pb_post_by_slug('blog', $pb_blog_key)로 데이터를 조회하고,
 *    pb_theme_header()/pb_theme_footer()로 레이아웃을 감싸는 책임을 진다(이 핸들러는
 *    라우팅만 담당하고 렌더링에는 관여하지 않는다).
 *  - view.php가 없으면(part007 미착수) global $pb_blog_view_fallback = true 로 표시하고
 *    pages/blog/list.php 로 되돌려, list.php가 "상세 준비중" 안내를 그 자리에서 보여준다.
 *
 * 알려진 제약: 두번째 세그먼트 'category'는 예약어다. 실제 글 slug가 정확히
 * "category"인 경우 카테고리 라우트로 오인될 수 있다(희박한 충돌, part007/010에서 필요시
 * pb_post_rewrite_slug 유일성 로직과 별개로 예약어 처리를 추가 검토).
 */
function _pb_rewrite_handler_blog($rewrite_path_){
	global $pb_blog_category_slug, $pb_blog_key, $pb_blog_view_fallback;

	$segment_count_ = count($rewrite_path_);

	// /blog
	if($segment_count_ <= 1){
		return pb_current_theme_path()."pages/blog/list.php";
	}

	// /blog/category[/{slug}]
	if($rewrite_path_[1] === 'category'){
		$pb_blog_category_slug = ($segment_count_ >= 3 && strlen($rewrite_path_[2])) ? urldecode($rewrite_path_[2]) : null;
		return pb_current_theme_path()."pages/blog/list.php";
	}

	// /blog/{key} — 상세(part007)
	$pb_blog_key = urldecode($rewrite_path_[1]);

	$view_path_ = pb_current_theme_path()."pages/blog/view.php";
	if(file_exists($view_path_)){
		// part007 view.php와의 실제 연동 규약: global $pb_blog_data 에 조회된 게시물 배열을
		// 담아 넘긴다(코어 _pb_post_hook_for_rewrite_handler가 $pbpost를 세팅하는 것과 동일한
		// 패턴). 존재하지 않는 글이면 null → view.php의 자체 "찾을 수 없음" 처리에 위임한다.
		global $pb_blog_data;
		$pb_blog_data = is_numeric($pb_blog_key) ? pb_post((int)$pb_blog_key) : pb_post_by_slug('blog', $pb_blog_key);

		return $view_path_;
	}

	// 안전 폴백: part007이 view.php를 아직 만들지 않은 시점에도 404 대신 안내 화면 표시
	$pb_blog_view_fallback = true;
	return pb_current_theme_path()."pages/blog/list.php";
}

/* ---------- URL 헬퍼(list.php / blog-components.php / part007 view.php 공용) ---------- */
function pb_blog_url($query_ = array()){
	return pb_home_url('blog', $query_);
}
function pb_blog_category_url($slug_, $query_ = array()){
	return pb_home_url('blog/category/'.rawurlencode($slug_), $query_);
}
function pb_blog_view_url($key_){
	return pb_home_url('blog/'.rawurlencode($key_));
}

/* ---------- part006 전용 css/js 주입 ----------
 * header.php/footer.php는 편집 금지 대상이므로, 코어가 제공하는 pb_head/pb_foot
 * 액션 훅(includes/common/functions.php)에 'blog' 라우트에서만 태그를 추가하는
 * 방식으로 자산을 로드한다(part005 mypage.php와 동일 패턴, part007 view.php도
 * 같은 'blog' slug 하에서 서비스되므로 이 한 곳의 등록으로 함께 커버된다).
 * ---------- */
function _pb_blog_head_assets(){
	if(!pb_is_current_slug('blog')){
		return;
	}
	?>
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/blog.css">
	<?php
}
pb_hook_add_action('pb_head', '_pb_blog_head_assets');

function _pb_blog_foot_assets(){
	if(!pb_is_current_slug('blog')){
		return;
	}
	?>
	<script type="text/javascript" src="<?=pb_current_theme_url()?>lib/js/features/blog.js"></script>
	<?php
}
pb_hook_add_action('pb_foot', '_pb_blog_foot_assets');

/* ---------- 컴포넌트/검색조건/AJAX 핸들러(본 part 전용, 분리 파일) ---------- */
include pb_current_theme_path()."includes/blog-components.php";

?>
