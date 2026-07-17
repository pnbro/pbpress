<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan002 part006 — 블로그 목록 / 카테고리 필터 / 검색
 * page-handlers/blog.php의 _pb_rewrite_handler_blog()가 이 파일로 라우팅한다.
 * global 계약: $pb_blog_category_slug(선택), $pb_blog_key(선택), $pb_blog_view_fallback(선택)
 * ============================================================ */

global $pb_blog_category_slug, $pb_blog_key, $pb_blog_view_fallback;
if(!isset($pb_blog_category_slug)){
	$pb_blog_category_slug = null;
}

pb_theme_header("other");

/* ---------- part007 view.php 준비 전 안전 폴백(상세 요청이었던 경우) ---------- */
if(!empty($pb_blog_view_fallback)){
	?>
	<div class="container sample-blog-page">
		<div class="alert alert-info">
			<?=sprintf(__('요청하신 글(%s) 상세 화면은 아직 준비 중입니다.', PB_THEME_DOMAIN), '<strong>'.htmlspecialchars((string)$pb_blog_key).'</strong>')?>
		</div>
		<a class="btn btn-primary" href="<?=pb_blog_url()?>"><?=__('블로그 목록으로 돌아가기', PB_THEME_DOMAIN)?></a>
	</div>
	<?php
	pb_theme_footer();
	return;
}

/* ---------- 검색어 / 페이지 파라미터 ---------- */
$current_keyword_ = isset($_GET['keyword']) ? trim((string)$_GET['keyword']) : '';
$current_page_ = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$page_limit_ = 12;

/* ---------- 현재 카테고리 조회(있으면 breadcrumb/칩/조건에 재사용) ---------- */
$current_category_data_ = null;
if(strlen((string)$pb_blog_category_slug)){
	$current_category_data_ = pb_post_category_by_slug('blog', $pb_blog_category_slug);
}

/* ---------- 카테고리 칩 목록(블로그 post_type 전체) ---------- */
$blog_categories_ = pb_post_category_list(array(
	'type' => 'blog',
	'orderby' => 'post_categories.title ASC',
));

/* ---------- 목록 조회 조건(chips/검색폼과 동일 규칙을 AJAX와 공유) ----------
 * _sample_blog_build_conditions() 내부에서 slug→id 변환을 위해 카테고리를 한 번 더
 * 조회한다(위 $current_category_data_ 와 별개). breadcrumb/칩 표시용 조회와
 * 조건 빌더용 조회가 중복되지만, 관리 화면이 아닌 프론트 목록 1회 요청 범위라
 * 성능상 문제되지 않아 코드 재사용(공유 빌더) 쪽을 택했다.
 * ---------- */
$conditions_ = _sample_blog_build_conditions($current_keyword_, (string)$pb_blog_category_slug);

$total_count_ = (int)pb_post_list(array_merge($conditions_, array('justcount' => true)));

$page_offset_ = ($current_page_ - 1) * $page_limit_;
$conditions_['orderby'] = 'posts.reg_date DESC';
$conditions_['limit'] = array($page_offset_, $page_limit_);
$results_ = pb_post_list($conditions_);

$post_types_ = pb_post_types();
$no_results_text_ = (string)@$post_types_['blog']['label']['no_results'];
$no_results_text_ = strlen($no_results_text_) ? $no_results_text_ : __("검색된 글이 없습니다.", PB_THEME_DOMAIN);

/* ---------- querystring 유지 헬퍼(카테고리/검색어 보존) ---------- */
function _sample_blog_extra_query($keyword_){
	return strlen($keyword_) ? array('keyword' => $keyword_) : array();
}
?>

<div class="container sample-blog-page">

	<ul class="breadcrumb">
		<li><a href="<?=pb_home_url()?>"><?=__('홈', PB_THEME_DOMAIN)?></a></li>
		<?php if(isset($current_category_data_)){ ?>
			<li><a href="<?=pb_blog_url()?>"><?=__('블로그', PB_THEME_DOMAIN)?></a></li>
			<li><?=htmlspecialchars((string)$current_category_data_['title'])?></li>
		<?php }else{ ?>
			<li><?=__('블로그', PB_THEME_DOMAIN)?></li>
		<?php } ?>
	</ul>

	<div class="sample-blog-page__header">
		<h1><?=__('블로그', PB_THEME_DOMAIN)?></h1>
		<p class="text-muted"><?=sprintf(__('pbpress %s post_type 쇼케이스 — 목록·카테고리·검색·무한스크롤', PB_THEME_DOMAIN), '<code>blog</code>')?></p>
	</div>

	<form class="sample-blog-search field" method="get" action="<?=pb_blog_url()?>">
		<label class="label" for="sample-blog-keyword"><?=__('검색', PB_THEME_DOMAIN)?></label>
		<div class="flex gap-2 flex-wrap">
			<input class="input" type="text" id="sample-blog-keyword" name="keyword" placeholder="<?=__('제목/작성자로 검색', PB_THEME_DOMAIN)?>" value="<?=htmlspecialchars($current_keyword_)?>">
			<button type="submit" class="btn btn-primary"><?=__('검색', PB_THEME_DOMAIN)?></button>
			<?php if(strlen($current_keyword_)){ ?>
				<a class="btn btn-ghost" href="<?=pb_blog_url()?>"><?=__('초기화', PB_THEME_DOMAIN)?></a>
			<?php } ?>
		</div>
	</form>

	<?php if(count($blog_categories_)){ ?>
	<div class="sample-blog-chips" role="group" aria-label="<?=__('카테고리 필터', PB_THEME_DOMAIN)?>">
		<a class="chip <?=!isset($current_category_data_) ? 'is-active' : ''?>" href="<?=pb_blog_url(_sample_blog_extra_query($current_keyword_))?>"><?=__('전체', PB_THEME_DOMAIN)?></a>
		<?php foreach($blog_categories_ as $cat_){ ?>
			<a class="chip <?=(isset($current_category_data_) && (string)$current_category_data_['id'] === (string)$cat_['id']) ? 'is-active' : ''?>" href="<?=pb_blog_category_url($cat_['slug'], _sample_blog_extra_query($current_keyword_))?>"><?=htmlspecialchars((string)$cat_['title'])?></a>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if(!count($results_)){ ?>

		<div class="sample-blog-empty">
			<p class="text-muted text-center"><?=htmlspecialchars($no_results_text_)?></p>
		</div>

	<?php }else{ ?>

		<div class="sample-blog-grid grid" id="sample-blog-list-items">
			<?php foreach($results_ as $row_){ ?>
				<div class="col-12 col-sm-6 col-lg-4 sample-blog-grid__col">
					<?php sample_blog_card($row_); ?>
				</div>
			<?php } ?>
		</div>

		<?php if(($page_offset_ + count($results_)) < $total_count_){ ?>
		<div class="sample-blog-grid skeleton-row grid" id="sample-blog-skeleton" hidden>
			<?php for($i_ = 0; $i_ < 3; ++$i_){ ?>
				<div class="col-12 col-sm-6 col-lg-4">
					<div class="card sample-blog-card">
						<div class="sample-blog-card__thumb skeleton"></div>
						<div class="card-body">
							<div class="skeleton skeleton-text" style="width:40%"></div>
							<div class="skeleton skeleton-text" style="width:90%"></div>
							<div class="skeleton skeleton-text" style="width:60%"></div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

		<div class="sample-blog-sentinel"
			id="sample-blog-sentinel"
			data-category="<?=htmlspecialchars((string)$pb_blog_category_slug)?>"
			data-limit="<?=$page_limit_?>"
			aria-hidden="true"></div>
		<?php } ?>

		<?php
		$total_pages_ = max(1, (int)ceil($total_count_ / $page_limit_));
		if($total_pages_ > 1){
		?>
		<nav class="sample-blog-pagination" id="sample-blog-pagination-fallback" aria-label="<?=__('페이지네이션(점진적 향상 — JS 미지원 환경 폴백)', PB_THEME_DOMAIN)?>">
			<ul class="pagination">
				<li class="<?=($current_page_ <= 1) ? 'disabled' : ''?>">
					<?php
					$prev_query_ = _sample_blog_extra_query($current_keyword_);
					if($current_page_ - 1 > 1) $prev_query_['page'] = $current_page_ - 1;
					$prev_url_ = isset($current_category_data_) ? pb_blog_category_url($current_category_data_['slug'], $prev_query_) : pb_blog_url($prev_query_);
					?>
					<a href="<?=$prev_url_?>" aria-label="<?=__('이전 페이지', PB_THEME_DOMAIN)?>"><?=__('이전', PB_THEME_DOMAIN)?></a>
				</li>
				<?php for($p_ = 1; $p_ <= $total_pages_; ++$p_){ ?>
					<li class="<?=($p_ === $current_page_) ? 'active' : ''?>">
						<?php
						$page_query_ = _sample_blog_extra_query($current_keyword_);
						if($p_ > 1) $page_query_['page'] = $p_;
						$page_url_ = isset($current_category_data_) ? pb_blog_category_url($current_category_data_['slug'], $page_query_) : pb_blog_url($page_query_);
						?>
						<a href="<?=$page_url_?>"><?=$p_?></a>
					</li>
				<?php } ?>
				<li class="<?=($current_page_ >= $total_pages_) ? 'disabled' : ''?>">
					<?php
					$next_query_ = _sample_blog_extra_query($current_keyword_);
					$next_query_['page'] = $current_page_ + 1;
					$next_url_ = isset($current_category_data_) ? pb_blog_category_url($current_category_data_['slug'], $next_query_) : pb_blog_url($next_query_);
					?>
					<a href="<?=$next_url_?>" aria-label="<?=__('다음 페이지', PB_THEME_DOMAIN)?>"><?=__('다음', PB_THEME_DOMAIN)?></a>
				</li>
			</ul>
		</nav>
		<?php
		}
	} ?>

</div>

<?php pb_theme_footer(); ?>
