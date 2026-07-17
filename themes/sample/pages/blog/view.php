<?php
if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/* ============================================================
 * devplan002 part007 — 블로그 상세 뷰
 * ------------------------------------------------------------
 * 진입 규약 (part006 page-handlers/blog.php 전제):
 *   /blog/{key} 요청 시 blog.php가 게시물을 조회해 본 파일을 include 한다.
 *   guide MCP의 products 패턴(/products/view/{key} → global $pb_product_data → view.php)을
 *   따라 blog.php도 `global $pb_blog_data`에 게시물 배열을 세팅해 넘겨줄 것으로 가정한다.
 *
 * 방어적 폴백(우선순위):
 *   1) global $pb_blog_data (blog.php가 세팅했다고 가정하는 규약값)
 *   2) pb_current_post()  (core 전역 $pbpost — blog.php가 재사용했을 경우)
 *   3) URL(/blog/{key})에서 직접 재조회 — pb_rewrite_path()[1]을 key로 사용,
 *      숫자면 pb_post(id), 아니면 pb_post_by_slug('blog', slug)
 *
 * blog.php가 실제로 구현되면 위 우선순위 중 어느 경로로든 정상 동작해야 하며,
 * part006 완료 후 실제 변수명을 확인해 이 우선순위 목록을 갱신할 것(하단 참고).
 * ============================================================ */

global $pb_blog_data;

$post_data_ = null;

if(isset($pb_blog_data) && is_array($pb_blog_data) && isset($pb_blog_data['id'])){
	$post_data_ = $pb_blog_data;
}else{
	$current_post_ = pb_current_post();
	if(isset($current_post_) && is_array($current_post_) && isset($current_post_['type']) && $current_post_['type'] === 'blog'){
		$post_data_ = $current_post_;
	}else{
		$rewrite_path_ = pb_rewrite_path();
		$key_ = isset($rewrite_path_[1]) ? $rewrite_path_[1] : null;

		if(strlen($key_)){
			$post_data_ = is_numeric($key_) ? pb_post((int)$key_) : pb_post_by_slug('blog', $key_);
		}
	}
}

$blog_list_url_ = pb_home_url('blog');

$not_found_ = (!isset($post_data_) || !is_array($post_data_) || !isset($post_data_['id'])
	|| $post_data_['type'] !== 'blog'
	|| (isset($post_data_['status']) && $post_data_['status'] !== PB_POST_STATUS::PUBLISHED));

pb_theme_header();
?>
<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/prose.css">
<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/blog-detail.css">

<div class="container blog-detail">

<?php if($not_found_): ?>

	<div class="alert alert-warning mt-6">
		게시물을 찾을 수 없습니다.
	</div>
	<p class="mt-4">
		<a class="btn btn-ghost" href="<?=htmlspecialchars($blog_list_url_)?>">&larr; 블로그 목록으로</a>
	</p>

<?php else:

	$category_rows_ = pb_post_category_values($post_data_['id']);
	$featured_image_url_ = pb_post_featured_image_url($post_data_['id']);
	$post_html_ = pb_post_html($post_data_['id']);
	$reg_date_ = isset($post_data_['reg_date_ymd']) ? $post_data_['reg_date_ymd'] : '';
	$writer_name_ = isset($post_data_['wrt_name']) && strlen($post_data_['wrt_name']) ? $post_data_['wrt_name'] : '관리자';

	$prev_post_ = pb_prev_post($post_data_['id']);
	$next_post_ = pb_next_post($post_data_['id']);

	if(!function_exists('_sample_blog_view_url_')){
		function _sample_blog_view_url_($post_){
			$key_ = isset($post_['slug']) && strlen($post_['slug']) ? $post_['slug'] : $post_['id'];
			return pb_home_url('blog/'.$key_);
		}
	}
?>

	<nav aria-label="이동 경로">
		<ul class="breadcrumb">
			<li><a href="<?=pb_home_url()?>">홈</a></li>
			<li><a href="<?=htmlspecialchars($blog_list_url_)?>">블로그</a></li>
			<li><?=htmlspecialchars($post_data_['post_title'])?></li>
		</ul>
	</nav>

	<article class="blog-detail__hero">
		<?php if(count($category_rows_) > 0): ?>
		<div class="blog-detail__badges flex flex-wrap gap-2 mb-2">
			<?php foreach($category_rows_ as $category_row_): ?>
			<span class="badge badge-primary"><?=htmlspecialchars($category_row_['category_title'])?></span>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<h1 class="blog-detail__title"><?=htmlspecialchars($post_data_['post_title'])?></h1>

		<div class="user-card blog-detail__meta">
			<div class="avatar avatar-sm" aria-hidden="true"><?=htmlspecialchars(mb_strtoupper(mb_substr($writer_name_, 0, 1)))?></div>
			<div>
				<div class="user-card-name"><?=htmlspecialchars($writer_name_)?></div>
				<div class="user-card-meta"><?=htmlspecialchars($reg_date_)?></div>
			</div>
		</div>

		<?php if(strlen($featured_image_url_)): ?>
		<div class="blog-detail__thumb mt-4">
			<img src="<?=htmlspecialchars($featured_image_url_)?>" alt="<?=htmlspecialchars($post_data_['post_title'])?>">
		</div>
		<?php endif; ?>
	</article>

	<div class="prose blog-detail__body">
		<?=$post_html_?>
	</div>

	<?php
	/* devplan004 part005(축소범위) — 리비전(수정이력) 노출
	 * 코어 includes/post/post-revision.php의 pb_post_revision_list()를 그대로 사용.
	 * 반환 필드는 pb_post_revision_statement()에서 확인: id, post_id, post_html,
	 * reg_date, reg_date_ymdhis/ymdhi/ymd(가공), post_type(조인). 지금까지 프론트
	 * 어디에서도 노출되지 않던 코어 자산이라 여기서 최초로 노출한다. */
	$post_revisions_ = pb_post_revision_list(array(
		'post_id' => $post_data_['id'],
		'orderby' => 'posts_revision.id DESC',
	));
	?>
	<section class="blog-detail__revisions card mt-8">
		<div class="card-body">
			<h2 class="blog-comments__title"><?=__('수정 이력', PB_THEME_DOMAIN)?></h2>
			<?php if(count($post_revisions_) <= 0): ?>
			<p class="text-muted mt-4"><?=__('수정 이력이 없습니다.', PB_THEME_DOMAIN)?></p>
			<?php else: ?>
			<div class="table-responsive mt-4">
				<table class="table">
					<thead>
						<tr>
							<th><?=__('리비전', PB_THEME_DOMAIN)?></th>
							<th><?=__('수정일시', PB_THEME_DOMAIN)?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($post_revisions_ as $revision_): ?>
						<tr>
							<td>#<?=(int)$revision_['id']?></td>
							<td class="text-muted"><?=htmlspecialchars(isset($revision_['reg_date_ymdhi']) ? $revision_['reg_date_ymdhi'] : '')?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<nav class="blog-detail__siblings" aria-label="이전/다음 글">
		<?php if(isset($prev_post_)): ?>
		<a class="blog-detail__sibling blog-detail__sibling--prev" href="<?=htmlspecialchars(_sample_blog_view_url_($prev_post_))?>">
			<span class="text-muted text-sm">이전 글</span>
			<span class="blog-detail__sibling-title"><?=htmlspecialchars($prev_post_['post_title'])?></span>
		</a>
		<?php endif; ?>
		<?php if(isset($next_post_)): ?>
		<a class="blog-detail__sibling blog-detail__sibling--next" href="<?=htmlspecialchars(_sample_blog_view_url_($next_post_))?>">
			<span class="text-muted text-sm">다음 글</span>
			<span class="blog-detail__sibling-title"><?=htmlspecialchars($next_post_['post_title'])?></span>
		</a>
		<?php endif; ?>
	</nav>

	<p class="blog-detail__back">
		<a class="btn btn-ghost" href="<?=htmlspecialchars($blog_list_url_)?>">&larr; 목록으로</a>
	</p>

	<?php include(PB_THEME_PATH.'pages/blog/comments.php'); ?>

<?php endif; ?>

</div>

<script src="<?=pb_current_theme_url()?>lib/js/features/blog-detail.js"></script>
<?php
pb_theme_footer();
?>
