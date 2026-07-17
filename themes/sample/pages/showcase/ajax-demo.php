<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan002 part009 → devplan004 part004 — /showcase, /showcase/ajax-demo
 * "UI킷 카탈로그(토스트·모달·드로어·탭·무한스크롤)"에서
 * "pbpress 코어 API 라이브 카탈로그"로 재구성.
 *
 * 6개 코어 API 섹션(① DO+쿼리빌더 / ② 훅 확장 / ③ rewrite 라우터 /
 * ④ AJAX+CSRF / ⑤ 페이지빌더 요소 / ⑥ 권한 게이팅) + 축소된 UI 유틸
 * 부속 섹션(⑦) 1개로 구성한다. 각 섹션은 "라이브 동작" + "실제 코드
 * 발췌(view-source)"를 함께 보여줘 개발자가 바로 따라 하게 한다.
 *
 * 신규 백엔드는 만들지 않는다. 기존에 검증된 엔드포인트만 재사용:
 *   - sample-guestbook-list / sample-guestbook-add (includes/demo-backend.php)
 *   - sample-theme-ajax-test (includes/ajax-test.php)
 *   - sample_theme_metrics() (includes/demo-backend.php)
 *
 * view-source 패널의 코드는 실제 파일에서 그대로 발췌한 것이며,
 * 출력 시 htmlspecialchars()로 이스케이프해 <pre><code> 안에 넣는다
 * (마크업 깨짐/XSS 방지). heredoc 대신 배열+implode를 쓰는 이유는
 * 이 테마 전역에 heredoc 사용 사례가 없어 문법 리스크를 피하기 위함.
 *
 * header.php/footer.php는 편집 금지이므로, 이 페이지 전용 CSS/JS는
 * pb_theme_header()/pb_theme_footer() 사이에서 직접 <link>/<script>로 로드한다.
 * ============================================================ */

function _showcase_src($lines_){
	return implode("\n", $lines_);
}

/* ① 선언형 DO + 쿼리빌더 — includes/demo-backend.php 발췌 */
$showcase_src_do_ = _showcase_src(array(
	'// includes/demo-backend.php',
	'$sample_guestbook_do = pbdb_data_object("sample_guestbook", array(',
	'    "id"       => array("type" => PBDB_DO::TYPE_INT,      "nn" => true, "pk" => true, "ai" => true, "comment" => "PK"),',
	'    "writer"   => array("type" => PBDB_DO::TYPE_VARCHAR,   "length" => 60,  "nn" => true, "comment" => "작성자 표시명"),',
	'    "user_id"  => array("type" => PBDB_DO::TYPE_INT,       "comment" => "로그인 회원 연동(비회원 NULL)"),',
	'    "content"  => array("type" => PBDB_DO::TYPE_VARCHAR,   "length" => 500, "nn" => true, "comment" => "내용"),',
	'    "reg_date" => array("type" => PBDB_DO::TYPE_DATETIME,  "comment" => "작성일시"),',
	'), "샘플테마 방명록 데모");',
	'',
	'// includes/demo-backend.php — _sample_guestbook_ajax_list()',
	'$statement_ = sample_guestbook_do()->statement();',
	'if(strlen($keyword_)){',
	'    $statement_->add_like_condition(array(\'content\', \'writer\'), $keyword_);',
	'}',
	'$total_ = $statement_->count();',
	'',
	'$list_statement_ = sample_guestbook_do()->statement();',
	'if(strlen($keyword_)){',
	'    $list_statement_->add_like_condition(array(\'content\', \'writer\'), $keyword_);',
	'}',
	'$rows_ = $list_statement_->select("id DESC", array($offset_, $limit_));',
));

/* ② 훅 확장 — includes/blog-components.php 발췌 */
$showcase_src_hook_ = _showcase_src(array(
	'// includes/blog-components.php',
	'pb_hook_add_filter(\'pb_post_statement\', \'_sample_blog_post_statement_category_filter\', 20);',
	'function _sample_blog_post_statement_category_filter($statement_, $conditions_){',
	'    if(!isset($conditions_[\'blog_category_id\']) || !strlen((string)$conditions_[\'blog_category_id\'])){',
	'        return $statement_;',
	'    }',
	'',
	'    global $posts_category_values_do;',
	'',
	'    $join_cond_ = pbdb_ss_conditions();',
	'    $join_cond_->add_compare("blog_cat_filter.post_id", "posts.id");',
	'',
	'    $statement_->add_join_statement("INNER JOIN", $posts_category_values_do->statement(), "blog_cat_filter", $join_cond_, array());',
	'    $statement_->add_in_condition("blog_cat_filter.category_id", $conditions_[\'blog_category_id\']);',
	'',
	'    return $statement_;',
	'}',
));

/* ③ rewrite 라우터 — page-handlers/showcase.php 발췌 */
$showcase_src_rewrite_ = _showcase_src(array(
	'// page-handlers/showcase.php',
	'pb_rewrite_register(\'showcase\', array(',
	'    \'title\' => "코어 API 라이브 카탈로그",',
	'    \'public\' => true,',
	'    \'rewrite_handler\' => "_sample_theme_showcase_rewrite_handler",',
	'));',
));

/* ④ AJAX + CSRF — includes/ajax-test.php, includes/demo-backend.php 발췌 */
$showcase_src_ajax_ = _showcase_src(array(
	'// includes/ajax-test.php — 정상 요청(읽기전용, 실 DB 조회)',
	'pb_add_ajax(\'sample-theme-ajax-test\', "_sample_theme_ajax_test");',
	'function _sample_theme_ajax_test(){',
	'    $do_ = sample_guestbook_do();',
	'    $total_ = $do_->statement()->count();',
	'    $recent_rows_ = $do_->statement()->select("id DESC", array(0, 3));',
	'    return pb_ajax_success(array(\'total\' => (int)$total_, \'recent\' => $recent_));',
	'}',
	'',
	'// includes/demo-backend.php — _sample_guestbook_ajax_add() : CSRF 검증부',
	'pb_add_ajax(\'sample-guestbook-add\', \'_sample_guestbook_ajax_add\');',
	'function _sample_guestbook_ajax_add(){',
	'    if(!pb_verify_request_token(\'sample-guestbook\', _POST(\'_request_chip\'))){',
	'        return pb_ajax_error(',
	'            __(\'잘못된 요청\', PB_THEME_DOMAIN),',
	'            __(\'요청이 만료되었거나 위조되었습니다. 새로고침 후 다시 시도해주세요.\', PB_THEME_DOMAIN)',
	'        );',
	'    }',
	'    // 검증 통과 후에만 실제 insert() 진행',
	'}',
));

/* ⑤ 페이지빌더 요소 등록 — includes/page-builder-element-test.php 발췌 */
$showcase_src_pagebuilder_ = _showcase_src(array(
	'// includes/page-builder-element-test.php',
	'class PBPageBuilderElement_image_slider extends PBPageBuilderElement{',
	'    function initialize(){',
	'        $this->add_edit_form("common", array($this, "render_admin_form"));',
	'    }',
	'    public function render($data_ = array(), $content_ = null){',
	'        // pbx-slider 등 자체 토큰 기반 클래스로 렌더(코어 .pb-* 충돌 방지)',
	'    }',
	'}',
	'',
	'pb_page_builder_add_element("image_slider", array(',
	'    \'name\' => "이미지 슬라이더",',
	'    \'icon\' => PB_LIBRARY_URL."img/page-builder/image.jpg",',
	'    \'element_object\' => "PBPageBuilderElement_image_slider",',
	'    \'edit_categories\' => array("common"),',
	'    \'loadable\' => true,',
	'    \'children\' => array("image_slider_item"),',
	'    \'category\' => "기본",',
	'));',
));

/* ⑥ 권한 게이팅 — includes/demo-backend.php 발췌 */
$showcase_src_authority_ = _showcase_src(array(
	'// includes/demo-backend.php — _sample_guestbook_ajax_list()',
	'$current_user_id_ = pb_current_user_id();          // 비로그인 = -1(sentinel)',
	'$is_logged_in_ = ($current_user_id_ != -1);',
	'$is_admin_ = $is_logged_in_ && pb_user_has_authority_task($current_user_id_, \'manage_site\');',
));

/* ⑥ 라이브 판별 — 이 페이지 자체에서 서버사이드로 바로 계산(별도 AJAX 불필요) */
$showcase_current_user_id_ = pb_current_user_id();
$showcase_is_logged_in_ = ($showcase_current_user_id_ != -1);
$showcase_is_admin_ = $showcase_is_logged_in_ && pb_user_has_authority_task($showcase_current_user_id_, 'manage_site');

/* ③ 라이브 지표 — 이 테마가 등록한 라우트 수 */
$showcase_metrics_ = sample_theme_metrics();

pb_theme_header();
?>

<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/showcase.css">

<div class="container">

	<ul class="breadcrumb">
		<li><a href="<?=pb_home_url()?>"><?=__("홈")?></a></li>
		<li><?=__("쇼케이스")?></li>
	</ul>

	<h1><?=__("코어 API 라이브 카탈로그", PB_THEME_DOMAIN)?></h1>
	<p class="text-muted"><?=__("pbpress 코어가 제공하는 API(선언형 DO·훅 확장·rewrite 라우터·AJAX+CSRF·페이지빌더 요소·권한 게이팅)를 실제 동작 결과와 실제 코드로 함께 보여줍니다. 프론트 UI킷은 마지막 부속 섹션으로 축소했습니다.", PB_THEME_DOMAIN)?></p>

	<!-- ================= ① 선언형 DO + 쿼리빌더 ================= -->
	<section class="showcase-section" id="core-do-query">
		<div class="showcase-section__head">
			<h2><?=__("① 선언형 DO + 쿼리빌더", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("표 하나를 배열로 선언하면 테이블 생성과 CRUD가 자동으로 준비됩니다. 아래 검색창은 실제 sample_guestbook 테이블을 pbdb_ss 쿼리빌더로 조회합니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="showcase-callout">
			<div class="showcase-callout__item showcase-callout__item--before">
				<span class="showcase-callout__tag"><?=__("Before", PB_THEME_DOMAIN)?></span>
				<p><?=__("예전엔 댓글을 posts_meta에 문자열로 욱여넣었지만(400자 제한)", PB_THEME_DOMAIN)?></p>
			</div>
			<div class="showcase-callout__item showcase-callout__item--after">
				<span class="showcase-callout__tag"><?=__("After", PB_THEME_DOMAIN)?></span>
				<p><?=__("DO 선언 하나로 테이블·CRUD가 끝난다.", PB_THEME_DOMAIN)?></p>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="flex gap-2 flex-wrap mb-4">
					<input type="text" class="input" id="showcase-guestbook-search-input" placeholder="<?=__("검색어(작성자·내용)", PB_THEME_DOMAIN)?>">
					<button type="button" class="btn btn-primary" id="showcase-guestbook-search-btn"><?=__("검색", PB_THEME_DOMAIN)?></button>
				</div>
				<div class="showcase-guestbook-list" id="showcase-guestbook-list" data-state="loading">
					<span class="text-sm text-muted"><?=__("불러오는 중...", PB_THEME_DOMAIN)?></span>
				</div>
			</div>
		</div>

		<div class="showcase-source-panel">
			<div class="showcase-source-panel__label"><?=__("view-source: includes/demo-backend.php", PB_THEME_DOMAIN)?></div>
			<pre class="showcase-source"><code><?=htmlspecialchars($showcase_src_do_, ENT_QUOTES)?></code></pre>
		</div>
	</section>

	<!-- ================= ② 훅 확장 (pb_post_statement 필터) ================= -->
	<section class="showcase-section" id="core-hook">
		<div class="showcase-section__head">
			<h2><?=__("② 훅 확장 (pb_post_statement 필터)", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("코어 파일을 한 줄도 고치지 않고, 필터 훅에 콜백을 걸어 게시물 쿼리에 조건(카테고리 JOIN)을 주입합니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="card">
			<div class="card-body">
				<p class="mb-4"><?=__("블로그 목록이 이 필터를 실제로 사용합니다. 카테고리를 선택하면 위 필터가 적용된 쿼리로 다시 조회됩니다.", PB_THEME_DOMAIN)?></p>
				<a class="btn btn-ghost" href="<?=pb_home_url("blog")?>"><?=__("블로그에서 확인", PB_THEME_DOMAIN)?></a>
			</div>
		</div>

		<div class="showcase-source-panel">
			<div class="showcase-source-panel__label"><?=__("view-source: includes/blog-components.php", PB_THEME_DOMAIN)?></div>
			<pre class="showcase-source"><code><?=htmlspecialchars($showcase_src_hook_, ENT_QUOTES)?></code></pre>
		</div>

		<p class="text-sm text-muted mt-3"><?=__("코어 파일 0줄 수정으로 게시물 쿼리에 조건을 얹었다.", PB_THEME_DOMAIN)?></p>
	</section>

	<!-- ================= ③ rewrite 라우터 ================= -->
	<section class="showcase-section" id="core-rewrite">
		<div class="showcase-section__head">
			<h2><?=__("③ rewrite 라우터", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("pb_rewrite_register() 한 번으로 URL 라우트가 등록되고, 핸들러 함수가 뷰 파일 경로를 반환합니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="card">
			<div class="card-body">
				<p><?=__("지금 이 /showcase 페이지 자체가 rewrite로 등록된 라우트입니다.", PB_THEME_DOMAIN)?></p>
				<p class="fw-medium mt-2"><?=sprintf(__("이 테마가 등록한 라우트 %d개", PB_THEME_DOMAIN), (int)@$showcase_metrics_['routes'])?></p>
			</div>
		</div>

		<div class="showcase-source-panel">
			<div class="showcase-source-panel__label"><?=__("view-source: page-handlers/showcase.php", PB_THEME_DOMAIN)?></div>
			<pre class="showcase-source"><code><?=htmlspecialchars($showcase_src_rewrite_, ENT_QUOTES)?></code></pre>
		</div>
	</section>

	<!-- ================= ④ AJAX + CSRF ================= -->
	<section class="showcase-section" id="core-ajax-csrf">
		<div class="showcase-section__head">
			<h2><?=__("④ AJAX + CSRF", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("코어 PB.post로 AJAX를 호출하고, 요청 위조 방지 토큰(pb_verify_request_token)이 없으면 서버가 거부하는 것까지 라이브로 확인합니다. 둘 다 DB에 부작용이 없는 안전한 데모입니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="grid showcase-catalog-grid">
			<div class="col-12 col-md-6">
				<div class="card">
					<div class="card-header"><?=__("정상 AJAX 요청", PB_THEME_DOMAIN)?></div>
					<div class="card-body">
						<p class="text-sm text-muted mb-3"><?=__("sample-theme-ajax-test 액션을 호출해 실제 DB 건수를 조회합니다(읽기전용).", PB_THEME_DOMAIN)?></p>
						<button type="button" class="btn btn-primary btn-sm" id="showcase-ajax-ok-btn"><?=__("정상 AJAX 요청", PB_THEME_DOMAIN)?></button>
						<div class="showcase-ajax-result mt-3" id="showcase-ajax-ok-result" data-state="idle">
							<span><?=__("버튼을 눌러 실행하세요.", PB_THEME_DOMAIN)?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6">
				<div class="card">
					<div class="card-header"><?=__("위조 요청 차단 확인", PB_THEME_DOMAIN)?></div>
					<div class="card-body">
						<p class="text-sm text-muted mb-3"><?=__("CSRF 토큰 없이 sample-guestbook-add를 호출합니다. 삽입되지 않고 서버가 거부하는 응답만 확인하므로 안전합니다.", PB_THEME_DOMAIN)?></p>
						<button type="button" class="btn btn-ghost btn-sm" id="showcase-ajax-csrf-btn"><?=__("위조 요청 차단 확인", PB_THEME_DOMAIN)?></button>
						<div class="showcase-ajax-result mt-3" id="showcase-ajax-csrf-result" data-state="idle">
							<span><?=__("버튼을 눌러 실행하세요.", PB_THEME_DOMAIN)?></span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="showcase-source-panel">
			<div class="showcase-source-panel__label"><?=__("view-source: includes/ajax-test.php, includes/demo-backend.php", PB_THEME_DOMAIN)?></div>
			<pre class="showcase-source"><code><?=htmlspecialchars($showcase_src_ajax_, ENT_QUOTES)?></code></pre>
		</div>

		<p class="text-sm text-muted mt-3"><?=__("CSRF 토큰이 없으면 서버가 거부한다.", PB_THEME_DOMAIN)?></p>
	</section>

	<!-- ================= ⑤ 페이지빌더 요소 등록 ================= -->
	<section class="showcase-section" id="core-page-builder">
		<div class="showcase-section__head">
			<h2><?=__("⑤ 페이지빌더 요소 등록", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("PBPageBuilderElement를 상속하고 pb_page_builder_add_element()로 등록하면, 관리자 페이지빌더 편집기에 새 요소가 나타납니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="card">
			<div class="card-body">
				<p><?=__("이 테마는 \"이미지 슬라이더\" 요소를 커스텀으로 등록했습니다. 관리자 페이지빌더에서 \"이미지 슬라이더\" 요소로 직접 확인할 수 있습니다(관리자 화면이라 프론트 라이브 데모는 제한됩니다).", PB_THEME_DOMAIN)?></p>
				<p class="text-sm text-muted mt-2"><?=sprintf(__("코어 .pb-* 클래스와 충돌하지 않도록 프론트 렌더 마크업은 %s 접두 클래스를 사용합니다.", PB_THEME_DOMAIN), '<code class="sample-code-inline">pbx-</code>')?></p>
			</div>
		</div>

		<div class="showcase-source-panel">
			<div class="showcase-source-panel__label"><?=__("view-source: includes/page-builder-element-test.php", PB_THEME_DOMAIN)?></div>
			<pre class="showcase-source"><code><?=htmlspecialchars($showcase_src_pagebuilder_, ENT_QUOTES)?></code></pre>
		</div>
	</section>

	<!-- ================= ⑥ 권한 게이팅 ================= -->
	<section class="showcase-section" id="core-authority">
		<div class="showcase-section__head">
			<h2><?=__("⑥ 권한 게이팅", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("pb_current_user_id()로 로그인 여부를, pb_user_has_authority_task()로 관리자 권한(manage_site)을 판별해 화면 노출을 제어합니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="card">
			<div class="card-body">
				<p class="mb-3">
					<?=__("현재 상태:", PB_THEME_DOMAIN)?>
					<?php if(!$showcase_is_logged_in_): ?>
						<span class="showcase-badge showcase-badge--muted"><?=__("비로그인", PB_THEME_DOMAIN)?></span>
					<?php elseif($showcase_is_admin_): ?>
						<span class="showcase-badge showcase-badge--admin"><?=__("관리자", PB_THEME_DOMAIN)?></span>
					<?php else: ?>
						<span class="showcase-badge showcase-badge--member"><?=__("일반 회원", PB_THEME_DOMAIN)?></span>
					<?php endif; ?>
				</p>

				<?php if($showcase_is_admin_): ?>
					<div class="alert alert-info showcase-admin-only">
						<?=__("이 영역은 manage_site 권한자만 봅니다.", PB_THEME_DOMAIN)?>
					</div>
				<?php else: ?>
					<div class="text-sm text-muted showcase-admin-only">
						<?=__("manage_site 권한을 가진 관리자로 로그인하면 이 자리에 관리자 전용 블록이 나타납니다.", PB_THEME_DOMAIN)?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="showcase-source-panel">
			<div class="showcase-source-panel__label"><?=__("view-source: includes/demo-backend.php", PB_THEME_DOMAIN)?></div>
			<pre class="showcase-source"><code><?=htmlspecialchars($showcase_src_authority_, ENT_QUOTES)?></code></pre>
		</div>
	</section>

	<!-- ================= ⑦ (부속) UI 유틸 — 축소 ================= -->
	<section class="showcase-section" id="ui-kit">
		<div class="showcase-section__head">
			<h2><?=__("부속 — 화면 UI 유틸(SampleUI)", PB_THEME_DOMAIN)?></h2>
			<p class="showcase-section__desc"><?=__("이건 pbpress 코어가 아니라 테마가 데모를 담는 배관입니다. 토스트와 모달만 남기고 나머지(드로어·탭·무한스크롤)는 걷어냈습니다.", PB_THEME_DOMAIN)?></p>
		</div>

		<div class="card">
			<div class="card-header"><?=__("토스트 & 모달 (SampleUI)", PB_THEME_DOMAIN)?></div>
			<div class="card-body showcase-toast-buttons">
				<button type="button" class="btn btn-ghost btn-sm" data-toast="<?=__("기본 토스트입니다.", PB_THEME_DOMAIN)?>"><?=__("기본", PB_THEME_DOMAIN)?></button>
				<button type="button" class="btn btn-ghost btn-sm" data-toast="<?=__("성공 토스트입니다.", PB_THEME_DOMAIN)?>" data-toast-type="success"><?=__("성공", PB_THEME_DOMAIN)?></button>
				<button type="button" class="btn btn-ghost btn-sm" data-toast="<?=__("에러 토스트입니다.", PB_THEME_DOMAIN)?>" data-toast-type="danger"><?=__("에러", PB_THEME_DOMAIN)?></button>
				<button type="button" class="btn btn-ghost btn-sm" data-modal-open="showcase-demo-modal"><?=__("모달 열기", PB_THEME_DOMAIN)?></button>
			</div>
		</div>
	</section>

</div>

<!-- 부속 UI 유틸 — 모달 데모 -->
<div class="modal" id="showcase-demo-modal" aria-hidden="true">
	<div class="modal-backdrop"></div>
	<div class="modal-dialog">
		<div class="modal-header">
			<h3 class="modal-title"><?=__("데모 모달", PB_THEME_DOMAIN)?></h3>
			<button type="button" class="modal-close" data-modal-close aria-label="<?=__("닫기")?>">&times;</button>
		</div>
		<div class="modal-body">
			<p><?=__("part001 컴포넌트 카탈로그의 .modal 컴포넌트를 그대로 재사용한 데모 모달입니다.", PB_THEME_DOMAIN)?></p>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-ghost" data-modal-close><?=__("닫기", PB_THEME_DOMAIN)?></button>
		</div>
	</div>
</div>

<script src="<?=pb_current_theme_url()?>lib/js/features/showcase.js"></script>

<?php pb_theme_footer(); ?>
