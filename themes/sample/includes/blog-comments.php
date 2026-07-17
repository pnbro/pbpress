<?php
if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/* ============================================================
 * devplan004 part005(축소범위) — 블로그 댓글 정식 DO 테이블화
 * ------------------------------------------------------------
 * before(devplan002 part007): 전용 댓글 테이블이 없어 posts_meta에
 *   "{user_id}|{content}" 문자열을 인코딩해 저장했다. posts_meta.meta_value가
 *   VARCHAR(500)이라 content를 400자로 제한해야 했고, 삭제 시에도
 *   meta_name 문자열 매칭에만 의존했다.
 * after(본 파일): includes/demo-backend.php의 sample_guestbook DO 선언
 *   패턴을 그대로 재사용해 sample_board_comments 정식 테이블을 둔다.
 *   post_id/user_id/content/reg_date가 진짜 컬럼으로 분리되므로 문자열
 *   인코딩/디코딩 헬퍼(_sample_blog_comment_encode/decode)가 사라지고,
 *   content 제한도 400자 → 1000자(VARCHAR(1000))로 완화된다.
 *
 * 테이블 생성 트리거: 아래 pbdb_data_object() 호출은 "선언"만 한다.
 *   실제 CREATE TABLE은 관리자 화면의 "테이블 재설치"
 *   (admin/_ajax_reinstall_tables.php → $pbdb->install_tables())를
 *   실행하는 시점에 만들어진다(includes/demo-backend.php와 동일 규약).
 *
 * 회귀 방지: AJAX 액션명(user-blog-comment-list/add/delete)과
 *   pages/blog/comments.php · lib/js/features/blog-detail.js가 기대하는
 *   JS 계약(list: {html,count}, add: {html}, delete: 성공만)은 그대로
 *   유지한다. sample_blog_comment_render_item()/render_empty()의
 *   마크업도 변경하지 않는다.
 * ============================================================ */

define('SAMPLE_BLOG_COMMENT_CONTENT_MAX', 1000);

global $sample_board_comments_do;
$sample_board_comments_do = pbdb_data_object("sample_board_comments", array(
	"id"       => array("type" => PBDB_DO::TYPE_INT,      "nn" => true, "pk" => true, "ai" => true, "comment" => "PK"),
	"post_id"  => array("type" => PBDB_DO::TYPE_INT,      "nn" => true, "index" => true, "comment" => "대상 게시물 id"),
	"user_id"  => array("type" => PBDB_DO::TYPE_INT,      "nn" => true, "comment" => "작성 회원 id"),
	"content"  => array("type" => PBDB_DO::TYPE_VARCHAR,  "length" => 1000, "nn" => true, "comment" => "댓글 내용"),
	"reg_date" => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "작성일시"),
), "샘플테마 블로그 댓글");

function sample_board_comments_do(){
	global $sample_board_comments_do;
	return $sample_board_comments_do;
}

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

function sample_blog_comment_list($post_id_){
	$statement_ = sample_board_comments_do()->statement();
	$statement_->add_field("DATE_FORMAT(sample_board_comments.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi");
	$statement_->add_compare_condition('post_id', $post_id_, '=', PBDB::TYPE_NUMBER);
	$rows_ = $statement_->select('id ASC');

	$comments_ = array();
	foreach($rows_ as $row_){
		$author_data_ = strlen($row_['user_id']) ? pb_user_simply_data($row_['user_id']) : null;
		$author_name_ = (isset($author_data_['user_name']) && strlen($author_data_['user_name']))
			? $author_data_['user_name']
			: __('탈퇴회원', PB_THEME_DOMAIN);

		$comments_[] = array(
			'id' => $row_['id'],
			'user_id' => $row_['user_id'],
			'content' => $row_['content'],
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

	$inserted_id_ = sample_board_comments_do()->insert(array(
		'post_id' => $post_id_,
		'user_id' => $user_id_,
		'content' => $content_,
		'reg_date' => pb_current_time(),
	));

	$author_data_ = pb_user_simply_data($user_id_);
	$comment_ = array(
		'id' => $inserted_id_,
		'user_id' => $user_id_,
		'content' => $content_,
		'reg_date' => date('Y.m.d H:i'),
		'author_name' => (isset($author_data_['user_name']) && strlen($author_data_['user_name'])) ? $author_data_['user_name'] : __('회원', PB_THEME_DOMAIN),
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

	$row_statement_ = sample_board_comments_do()->statement();
	$row_statement_->add_compare_condition('id', $comment_id_, '=', PBDB::TYPE_NUMBER);
	$row_ = $row_statement_->get_first_row();

	if(!isset($row_)){
		return pb_ajax_error('댓글을 찾을 수 없습니다.');
	}

	$current_user_id_ = pb_current_user_id();

	if((string)$row_['user_id'] !== (string)$current_user_id_){
		return pb_ajax_error('본인 댓글만 삭제할 수 있습니다.');
	}

	sample_board_comments_do()->delete($comment_id_);

	return pb_ajax_success();
}

?>
