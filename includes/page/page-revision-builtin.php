<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_hook_add_action('pb_page_update', "_pb_page_update_hook_for_revision");
function _pb_page_update_hook_for_revision($page_id_){
	pb_page_revision_write_from($page_id_);
}

pb_hook_add_filter('pb_adminpage_manage_page_rewrite_handler', '_pb_adminpage_manage_page_rewrite_handler_for_revision');
function _pb_adminpage_manage_page_rewrite_handler_for_revision($path_, $sub_action_, $rewrite_path_){

	global $pbpage;

	if($sub_action_ === "revision"){
		$page_id_ = isset($rewrite_path_[2]) ? $rewrite_path_[2] : -1;
		$pbpage = pb_page($page_id_);

		if(!isset($pbpage)){
			return new PBError(503, "잘못된 접근", "존재하지 않는 페이지입니다.");
		}

		global $pbpage_meta_map;
		$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

		return PB_DOCUMENT_PATH."includes/page/views/revision.php";
	}

	return $path_;
}

//리비젼 iframe 추가
pb_rewrite_register('__page-revision', array(
	'rewrite_handler' => '_pb_rewrite_handler_for_revision_iframe',
));
function _pb_rewrite_handler_for_revision_iframe($rewrite_path_){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_redirect_404();
		pb_end();
	}	

	if(count($rewrite_path_) < 2){
		pb_redirect_404();
		pb_end();
	}
	
	$revision_id_ = isset($rewrite_path_[1]) ? $rewrite_path_[1] : -1;
	$revision_data_ = pb_page_revision($revision_id_);

	if(!isset($revision_data_)){
		pb_redirect_404();
		pb_end();	
	}

	$original_page_data_ = pb_page($revision_data_['page_id']);


	global $pbpage, $pbpage_meta_map;
	$pbpage = $original_page_data_;

	$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

	$page_path_ = pb_current_theme_path()."page.php";

	if(!file_exists($page_path_)){
		$page_path_ = PB_DOCUMENT_PATH . 'includes/page/views/page.php';
	}

	$pbpage['page_html'] = $revision_data_['page_html'];

	return $page_path_;
}


//페이지 에디팅 영역에 서브메뉴추가
function _pb_manage_page_listtable_subaction_hook_for_revision($item_){
	?>

	<a href="<?=pb_admin_url("manage-page/revision/".$item_['id'])?>">리비젼</a>

	<?php

}
pb_hook_add_action("pb_manage_page_listtable_subaction", '_pb_manage_page_listtable_subaction_hook_for_revision');


function _pb_page_edit_form_after_hook_for_revision($page_data_){

	$revision_statement_ = pb_page_revision_statement(array("page_id" => $page_data_['id']));
	?>
	<div class="panel panel-default" id="pb-page-edit-form-revision-panel">
		<div class="panel-heading" role="tab">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" href="#pb-page-edit-form-revision-panel-body" aria-expanded="true" aria-controls="collapseOne">리비젼</a>
			</h4>
		</div>
		<div id="pb-page-edit-form-revision-panel-body" class="panel-collapse collapse in" role="tabpanel">
			<div class="panel-body">
				<label ><?=number_format($revision_statement_->count())?>개의 리비젼</label>
				<div><a href="<?=pb_admin_url("manage-page/revision/".$page_data_['id'])?>" class="btn btn-block btn-default">리비젼보기</a></div>
			</div>
		</div>
	</div>

	<?php 
}
pb_hook_add_action("pb_page_edit_form_control_panel_after", '_pb_page_edit_form_after_hook_for_revision');

?>