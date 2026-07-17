<?php
if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/* ============================================================
 * devplan002 part007 — 블로그 댓글 목록 + 작성 폼 (partial)
 * view.php가 $post_data_(게시물 배열)를 세팅한 뒤 include 한다.
 * 댓글 조회/렌더 함수는 includes/blog-comments.php 참조(functions.php에서
 * `include pb_current_theme_path()."includes/blog-comments.php";` 필요).
 * ============================================================ */

$blog_comments_ = sample_blog_comment_list($post_data_['id']);
$is_logged_in_ = _sample_blog_comment_is_logged_in();
?>
<section class="blog-comments card mt-8" id="blog-comments" data-post-id="<?=(int)$post_data_['id']?>">
	<div class="card-body">
		<h2 class="blog-comments__title">
			<?=__('댓글', PB_THEME_DOMAIN)?> <span class="blog-comments__count text-muted" data-comment-count><?=count($blog_comments_)?></span>
		</h2>

		<?php if($is_logged_in_): ?>
		<form class="blog-comment-form mt-4" data-validate data-blog-comment-form novalidate>
			<div class="field">
				<label class="label sr-only" for="blog-comment-content"><?=__('댓글 내용', PB_THEME_DOMAIN)?></label>
				<textarea
					id="blog-comment-content"
					name="content"
					class="textarea"
					rows="3"
					placeholder="<?=__('댓글을 입력하세요', PB_THEME_DOMAIN)?>"
					maxlength="<?=SAMPLE_BLOG_COMMENT_CONTENT_MAX?>"
					data-required
					data-required-message="<?=__('댓글 내용을 입력하세요.', PB_THEME_DOMAIN)?>"
					data-max="<?=SAMPLE_BLOG_COMMENT_CONTENT_MAX?>"
					data-max-message="<?=sprintf(__('댓글은 최대 %d자까지 입력할 수 있습니다.', PB_THEME_DOMAIN), SAMPLE_BLOG_COMMENT_CONTENT_MAX)?>"
				></textarea>
			</div>
			<div class="flex justify-end mt-2">
				<button type="submit" class="btn btn-primary btn-sm"><?=__('댓글 등록', PB_THEME_DOMAIN)?></button>
			</div>
		</form>
		<?php else: ?>
		<div class="alert alert-info blog-comment-login-prompt mt-4">
			<?=sprintf(__('댓글을 작성하려면 %s이 필요합니다.', PB_THEME_DOMAIN), '<a href="'.htmlspecialchars(_sample_blog_login_url()).'">'.__('로그인').'</a>')?>
		</div>
		<?php endif; ?>

		<ul class="blog-comment-list mt-6" id="blog-comment-list" data-blog-comment-list>
			<?php
			if(count($blog_comments_) <= 0){
				echo sample_blog_comment_render_empty();
			}else{
				foreach($blog_comments_ as $comment_){
					echo sample_blog_comment_render_item($comment_);
				}
			}
			?>
		</ul>
	</div>
</section>
