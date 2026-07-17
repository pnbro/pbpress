<?php
if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/* ============================================================
 * devplan002 part007 — 블로그 댓글 저장소 + AJAX 핸들러
 * ------------------------------------------------------------
 * 코어에 전용 댓글 테이블/API가 없어(guide MCP 확인 결과) 기존 게시물 메타
 * 인프라(pb_post_meta_*, posts_meta 테이블)를 재사용해 댓글을 저장한다.
 * posts_meta.meta_value 는 VARCHAR(500) 한도이므로
 *   "{user_id}|{content}" 형식으로 인코딩하고 content는 400자로 제한한다.
 * 신규 물리 테이블을 만들지 않으므로 별도 설치/마이그레이션이 필요 없다.
 * ============================================================ */

define('SAMPLE_BLOG_COMMENT_META_NAME', 'blog_comment');
define('SAMPLE_BLOG_COMMENT_CONTENT_MAX', 400);

function _sample_blog_comment_is_logged_in(){
	// pb_current_user_id()는 미로그인 시 -1(sentinel)을 반환한다(null/false 아님) — 반드시 값 비교로 체크.
	return pb_current_user_id() != -1;
}

function _sample_blog_login_url(){
	if(function_exists('pb_login_url')){
		return pb_login_url();
	}
	return pb_home_url('login');
}

function _sample_blog_comment_encode($user_id_, $content_){
	return $user_id_.'|'.$content_;
}

function _sample_blog_comment_decode($meta_value_){
	$parts_ = explode('|', (string)$meta_value_, 2);
	return array(
		'user_id' => isset($parts_[0]) ? $parts_[0] : null,
		'content' => isset($parts_[1]) ? $parts_[1] : '',
	);
}

function sample_blog_comment_list($post_id_){
	$rows_ = pb_post_meta_list(array(
		'post_id' => $post_id_,
		'meta_name' => SAMPLE_BLOG_COMMENT_META_NAME,
		'orderby' => 'posts_meta.id ASC',
	));

	$comments_ = array();
	foreach($rows_ as $row_){
		$decoded_ = _sample_blog_comment_decode($row_['meta_value']);
		$author_data_ = strlen($decoded_['user_id']) ? pb_user_simply_data($decoded_['user_id']) : null;
		$author_name_ = (isset($author_data_['user_name']) && strlen($author_data_['user_name']))
			? $author_data_['user_name']
			: '탈퇴회원';

		$comments_[] = array(
			'id' => $row_['id'],
			'user_id' => $decoded_['user_id'],
			'content' => $decoded_['content'],
			'reg_date' => isset($row_['reg_date_ymdhi']) ? $row_['reg_date_ymdhi'] : '',
			'author_name' => $author_name_,
		);
	}

	return $comments_;
}

function sample_blog_comment_render_item($comment_){
	$current_user_id_ = pb_current_user_id();
	$is_owner_ = ($current_user_id_ != -1) && ((string)$current_user_id_ === (string)$comment_['user_id']);
	$initial_ = mb_strtoupper(mb_substr($comment_['author_name'], 0, 1));

	ob_start();
	?>
	<li class="blog-comment" data-comment-id="<?=(int)$comment_['id']?>">
		<div class="avatar avatar-sm" aria-hidden="true"><?=htmlspecialchars($initial_)?></div>
		<div class="blog-comment__body">
			<div class="blog-comment__meta">
				<span class="blog-comment__author fw-medium"><?=htmlspecialchars($comment_['author_name'])?></span>
				<span class="blog-comment__time text-muted text-sm"><?=htmlspecialchars($comment_['reg_date'])?></span>
				<?php if($is_owner_): ?>
				<button type="button" class="btn btn-link btn-sm blog-comment__delete" data-blog-comment-delete="<?=(int)$comment_['id']?>">삭제</button>
				<?php endif; ?>
			</div>
			<p class="blog-comment__content"><?=nl2br(htmlspecialchars($comment_['content']))?></p>
		</div>
	</li>
	<?php
	return ob_get_clean();
}

function sample_blog_comment_render_empty(){
	return '<li class="blog-comment-empty text-muted">아직 댓글이 없습니다.</li>';
}

/* ------------------------------------------------------------
 * AJAX: user-blog-comment-list — 댓글 목록 HTML 재조회
 * ------------------------------------------------------------ */
pb_add_ajax('user-blog-comment-list', '_sample_blog_ajax_comment_list');
function _sample_blog_ajax_comment_list(){
	$post_id_ = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
	if($post_id_ <= 0){
		return pb_ajax_error('잘못된 요청입니다.');
	}

	$post_data_ = pb_post($post_id_);
	if(!isset($post_data_) || $post_data_['type'] !== 'blog'){
		return pb_ajax_error('게시물을 찾을 수 없습니다.');
	}

	$comments_ = sample_blog_comment_list($post_id_);

	ob_start();
	if(count($comments_) <= 0){
		echo sample_blog_comment_render_empty();
	}else{
		foreach($comments_ as $comment_){
			echo sample_blog_comment_render_item($comment_);
		}
	}
	$html_ = ob_get_clean();

	return pb_ajax_success(array(
		'html' => $html_,
		'count' => count($comments_),
	));
}

/* ------------------------------------------------------------
 * AJAX: user-blog-comment-add — 댓글 작성(로그인 필수)
 * ------------------------------------------------------------ */
pb_add_ajax('user-blog-comment-add', '_sample_blog_ajax_comment_add');
function _sample_blog_ajax_comment_add(){
	if(!_sample_blog_comment_is_logged_in()){
		return pb_ajax_error('로그인이 필요합니다.');
	}

	$post_id_ = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
	$content_ = isset($_POST['content']) ? trim($_POST['content']) : '';

	if($post_id_ <= 0){
		return pb_ajax_error('잘못된 요청입니다.');
	}
	if(!strlen($content_)){
		return pb_ajax_error('댓글 내용을 입력하세요.');
	}
	if(mb_strlen($content_) > SAMPLE_BLOG_COMMENT_CONTENT_MAX){
		$content_ = mb_substr($content_, 0, SAMPLE_BLOG_COMMENT_CONTENT_MAX);
	}

	$post_data_ = pb_post($post_id_);
	if(!isset($post_data_) || $post_data_['type'] !== 'blog'){
		return pb_ajax_error('게시물을 찾을 수 없습니다.');
	}

	$user_id_ = pb_current_user_id();
	$meta_value_ = _sample_blog_comment_encode($user_id_, $content_);
	$inserted_id_ = pb_post_meta_update($post_id_, SAMPLE_BLOG_COMMENT_META_NAME, $meta_value_, false);

	$author_data_ = pb_user_simply_data($user_id_);
	$comment_ = array(
		'id' => $inserted_id_,
		'user_id' => $user_id_,
		'content' => $content_,
		'reg_date' => date('Y.m.d H:i'),
		'author_name' => (isset($author_data_['user_name']) && strlen($author_data_['user_name'])) ? $author_data_['user_name'] : '회원',
	);

	return pb_ajax_success(array(
		'html' => sample_blog_comment_render_item($comment_),
	));
}

/* ------------------------------------------------------------
 * AJAX: user-blog-comment-delete — 댓글 삭제(작성 본인만)
 * ------------------------------------------------------------ */
pb_add_ajax('user-blog-comment-delete', '_sample_blog_ajax_comment_delete');
function _sample_blog_ajax_comment_delete(){
	if(!_sample_blog_comment_is_logged_in()){
		return pb_ajax_error('로그인이 필요합니다.');
	}

	$comment_id_ = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
	if($comment_id_ <= 0){
		return pb_ajax_error('잘못된 요청입니다.');
	}

	$row_ = pb_post_meta_data($comment_id_);
	if(!isset($row_) || $row_['meta_name'] !== SAMPLE_BLOG_COMMENT_META_NAME){
		return pb_ajax_error('댓글을 찾을 수 없습니다.');
	}

	$decoded_ = _sample_blog_comment_decode($row_['meta_value']);
	$current_user_id_ = pb_current_user_id();

	if((string)$decoded_['user_id'] !== (string)$current_user_id_){
		return pb_ajax_error('본인 댓글만 삭제할 수 있습니다.');
	}

	global $pbdb;
	$pbdb->delete('posts_meta', array('id' => $comment_id_));

	return pb_ajax_success();
}

?>
