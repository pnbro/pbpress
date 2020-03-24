<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_hook_add_action('pb_post_update', "_pb_post_update_hook_for_revision");
function _pb_post_update_hook_for_revision($post_id_){
	pb_post_revision_write_from($post_id_);
}

pb_hook_add_filter('pb_adminpage_manage_post_rewrite_handler', '_pb_adminpage_manage_post_rewrite_handler_for_revision');
function _pb_adminpage_manage_post_rewrite_handler_for_revision($path_, $sub_action_, $rewrite_path_){

	global $pbpost;

	if($sub_action_ === "revision"){
		$post_id_ = isset($rewrite_path_[2]) ? $rewrite_path_[2] : -1;
		$pbpost = pb_post($post_id_);

		if(!isset($pbpost)){
			return new PBError(503, "잘못된 접근", "존재하지 않는 글입니다.");
		}

		global $pbpost_meta_map;
		$pbpost_meta_map = pb_post_meta_map($pbpost['id']);

		return PB_DOCUMENT_PATH."includes/post/views/revision.php";
	}

	return $path_;
}

//리비젼 iframe 추가
pb_rewrite_register('__post-revision', array(
	'rewrite_handler' => '_pb_rewrite_handler_for_post_revision_iframe',
));
function _pb_rewrite_handler_for_post_revision_iframe($rewrite_path_){
	if(count($rewrite_path_) < 2){
		pb_redirect_404();
		pb_end();
	}
	
	$revision_id_ = isset($rewrite_path_[1]) ? $rewrite_path_[1] : -1;
	$revision_data_ = pb_post_revision($revision_id_);

	if(!isset($revision_data_)){
		pb_redirect_404();
		pb_end();	
	}

	$original_post_data_ = pb_post($revision_data_['post_id']);
	$original_post_type_ = $original_post_data_['type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$original_post_type_}")){
		pb_redirect_404();
		pb_end();
	}	


	global $pbpost, $pbpost_meta_map;
	$pbpost = $original_post_data_;

	$pbpost_meta_map = pb_post_meta_map($pbpost['id']);

	$post_path_ = pb_current_theme_path()."post.php";

	if(!file_exists($post_path_)){
		$post_path_ = PB_DOCUMENT_PATH . 'includes/post/views/post.php';
	}

	$pbpost['post_html'] = $revision_data_['post_html'];

	return $post_path_;
}


//글 에디팅 영역에 서브메뉴추가
function _pb_manage_post_listtable_subaction_hook_for_revision($item_){
	$post_type_ = $item_['type'];
	?>

	<a href="<?=pb_admin_url("manage-{$post_type_}/revision/".$item_['id'])?>">리비젼</a>

	<?php

}
pb_hook_add_action("pb_manage_post_listtable_subaction", '_pb_manage_post_listtable_subaction_hook_for_revision');

function _pb_post_edit_form_after_hook_for_revision($post_data_){

	$revision_statement_ = pb_post_revision_statement(array("post_id" => $post_data_['id']));

	if(!strlen($post_data_['id'])) return; //post is new

	$post_type_ = $post_data_['type'];

	?>
	<div class="panel panel-default" id="pb-post-edit-form-revision-panel">
		<div class="panel-heading" role="tab">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" href="#pb-post-edit-form-revision-panel-body" aria-expanded="true" aria-controls="collapseOne">리비젼</a>
			</h4>
		</div>
		<div id="pb-post-edit-form-revision-panel-body" class="panel-collapse collapse in" role="tabpanel">
			<div class="panel-body">
				<label ><?=number_format($revision_statement_->count())?>개의 리비젼</label>
				<div><a href="<?=pb_admin_url("manage-{$post_type_}/revision/".$post_data_['id'])?>" class="btn btn-block btn-default">리비젼보기</a></div>
			</div>
		</div>
	</div>

	<?php 
}
pb_hook_add_action("pb_post_edit_form_control_panel_after", '_pb_post_edit_form_after_hook_for_revision');

?>