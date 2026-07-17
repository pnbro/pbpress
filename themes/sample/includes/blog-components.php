<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan003 part006 — 블로그 카드 파셜 / 검색·카테고리 조건 / 무한스크롤 AJAX
 * (blog-components.php는 코어 기반 로직 유지, 컴포넌트 시스템만 평범한 함수로 교체)
 * ============================================================ */

/* ------------------------------------------------------------
 * sample_blog_card — 블로그 카드 파셜
 * CSS변수로 썸네일, null안전(@), id자동생성. 마크업은 기존과 동일.
 * ------------------------------------------------------------ */
if(!function_exists('sample_blog_card')){
	function sample_blog_card($data_, $parameter_ = array()){
		$class_ = @$parameter_['class'];
		$id_ = @strlen($parameter_['id']) ? $parameter_['id'] : "pb-blog-post-".pb_random_string(5);

		$title_ = (string)@$data_['post_title'];
		$excerpt_ = (string)@$data_['post_short'];
		$date_ = (string)@$data_['reg_date_ymd'];
		$slug_ = (string)@$data_['slug'];
		$link_ = pb_blog_view_url($slug_);

		$has_image_ = @strlen($data_['featured_image_path']) > 0;
		$image_url_ = $has_image_ ? pb_filebase_url($data_['featured_image_path']) : null;

		$categories_ = (isset($data_['id']) && strlen($data_['id'])) ? pb_post_category_values($data_['id']) : array();

		?><a href="<?=$link_?>" class="card sample-blog-card <?=$class_?>" id="<?=$id_?>">
	<div class="sample-blog-card__thumb"<?php if($image_url_){ ?> style="--sample-blog-thumb-url:url(<?=$image_url_?>);"<?php } ?>>
		<?php if(!$image_url_){ ?><span class="sample-blog-card__thumb-empty">No Image</span><?php } ?>
	</div>
	<div class="card-body sample-blog-card__body">
		<?php if(count($categories_)){ ?>
		<div class="sample-blog-card__badges">
			<?php foreach($categories_ as $cat_){ ?>
				<span class="badge badge-primary"><?=htmlspecialchars((string)@$cat_['category_title'])?></span>
			<?php } ?>
		</div>
		<?php } ?>
		<h3 class="sample-blog-card__title"><?=htmlspecialchars((string)$title_)?></h3>
		<?php if(strlen($excerpt_)){ ?><p class="sample-blog-card__excerpt text-muted"><?=htmlspecialchars($excerpt_)?></p><?php } ?>
		<div class="sample-blog-card__meta text-sm text-muted"><?=htmlspecialchars((string)$date_)?></div>
	</div>
</a><?php
		if(@strlen($parameter_['after_html'])){
			echo $parameter_['after_html'];
		}
	}
}

/* ------------------------------------------------------------
 * 카테고리 필터 — pb_post_statement 필터훅으로 확장(코어 파일 수정 없음).
 * 조건키: conditions_['blog_category_id'] (post_categories.id)
 * ------------------------------------------------------------ */
pb_hook_add_filter('pb_post_statement', '_sample_blog_post_statement_category_filter', 20);
function _sample_blog_post_statement_category_filter($statement_, $conditions_){
	if(!isset($conditions_['blog_category_id']) || !strlen((string)$conditions_['blog_category_id'])){
		return $statement_;
	}

	global $posts_category_values_do;

	$join_cond_ = pbdb_ss_conditions();
	$join_cond_->add_compare("blog_cat_filter.post_id", "posts.id");

	$statement_->add_join_statement("INNER JOIN", $posts_category_values_do->statement(), "blog_cat_filter", $join_cond_, array());
	$statement_->add_in_condition("blog_cat_filter.category_id", $conditions_['blog_category_id']);

	return $statement_;
}

/* ------------------------------------------------------------
 * 검색조건 빌더(목록 SSR / AJAX 공용)
 * ------------------------------------------------------------ */
function _sample_blog_build_conditions($keyword_ = '', $category_slug_ = ''){
	$conditions_ = array(
		'type' => 'blog',
		'status' => PB_POST_STATUS::PUBLISHED,
	);

	if(strlen($keyword_)){
		$conditions_['keyword'] = $keyword_;
	}

	if(strlen($category_slug_)){
		$category_data_ = pb_post_category_by_slug('blog', $category_slug_);
		// 존재하지 않는 슬러그는 0(불가능 id)으로 강제해 "결과없음"을 보장한다.
		$conditions_['blog_category_id'] = isset($category_data_) ? $category_data_['id'] : 0;
	}

	return $conditions_;
}

/* ------------------------------------------------------------
 * 무한스크롤 AJAX — user-load-blog-list
 * 계약: SampleUI.infiniteScroll(part001)의 payload는 {offset, limit, ...params}.
 * 응답은 §7 스켈레톤&AJAX통합 패턴대로 ob_start()로 카드 HTML을 미리 문자열로
 * 만들어 반환한다. 단, ui.js의 실제 infiniteScroll 구현은 response.items 를
 * "행 데이터 배열"이 아니라 "renderItem()에 그대로 넘길 값의 배열"로 다루므로,
 * 여기서는 items의 각 원소를 이미 렌더링된 카드 HTML 문자열로 채워 반환하고
 * 클라이언트(blog.js)의 renderItem은 항등함수로 그대로 삽입한다.
 * ------------------------------------------------------------ */
pb_add_ajax('user-load-blog-list', '_sample_blog_ajax_load_list');
function _sample_blog_ajax_load_list(){
	$offset_ = isset($_POST['offset']) ? max(0, (int)$_POST['offset']) : 0;
	$limit_ = isset($_POST['limit']) ? (int)$_POST['limit'] : 12;
	$limit_ = ($limit_ > 0 && $limit_ <= 60) ? $limit_ : 12;

	$keyword_ = isset($_POST['keyword']) ? trim((string)$_POST['keyword']) : '';
	$category_slug_ = isset($_POST['category']) ? trim((string)$_POST['category']) : '';

	$conditions_ = _sample_blog_build_conditions($keyword_, $category_slug_);
	$conditions_['orderby'] = 'posts.reg_date DESC';
	$conditions_['limit'] = array($offset_, $limit_);

	$results_ = pb_post_list($conditions_);

	$items_ = array();
	foreach($results_ as $row_){
		ob_start();
		?><div class="col-12 col-sm-6 col-lg-4 sample-blog-grid__col"><?php sample_blog_card($row_); ?></div><?php
		$items_[] = ob_get_clean();
	}

	pb_ajax_success(array(
		'items' => $items_,
		'count' => count($items_),
	));
}

?>
