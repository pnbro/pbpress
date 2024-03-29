<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_page_register_adminpage($results_){

	/*$results_['page'] = array(
		'name' => '페이지관리',
		'type' => 'directory',
		'sort' => 5,
	);*/
	$results_['manage-page'] = array(
		'name' => __('페이지관리'),
		'type' => 'menu',
		// 'directory' => 'page',
		'rewrite_handler' => "_pb_page_rewrite_handler_for_adminpage",
		'authority_task' => 'manage_page',
		'subpath' => null,
		'sort' => 5,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_page_register_adminpage');

pb_rewrite_register('__page-live-edit', array(
	'rewrite_handler' => '_page_rewrite_handler_for_live_edit',
));
function _page_rewrite_handler_for_live_edit(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		return new PBError(403, __("잘못된 접근"), __("접근 권한이 없습니다."));
	}

	$rewrite_path_ = pb_rewrite_path();

	if(count($rewrite_path_) !== 2) return new PBError(503, __("잘못된 접근"), __("잘못된 요청입니다.")); 

	global $pbdb, $pbpage;

	$page_id_ = isset($rewrite_path_[1]) ? $rewrite_path_[1] : -1;
	$pbpage = pb_page($page_id_);

	if(!isset($pbpage)){
		return new PBError(503, __("잘못된 접근"), __("존재하지 않는 페이지입니다."));
	}

	global $pbpage_meta_map;
	$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

	return PB_DOCUMENT_PATH . 'includes/page/live-editor/edit.php';
}

pb_rewrite_register('__page-live-edit-preview', array(
	'rewrite_handler' => '_page_rewrite_handler_for_live_edit_previewer',
));
function _page_rewrite_handler_for_live_edit_previewer(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		return new PBError(403, __("잘못된 접근"), __("접근 권한이 없습니다."));
	}

	return PB_DOCUMENT_PATH . 'includes/page/views/live-edit-previewer.php';
}

__iinclude(PB_DOCUMENT_PATH . "includes/page/views/tables.php");

function _pb_page_rewrite_handler_for_adminpage($rewrite_path_){
	
	if(count($rewrite_path_) < 2){
		return PB_DOCUMENT_PATH."includes/page/views/list.php";
	}

	$sub_action_ = $rewrite_path_[1];

	if($sub_action_ === "add"){
		return PB_DOCUMENT_PATH."includes/page/views/edit.php";		
	}

	global $pbpage;

	if($sub_action_ === "edit"){
		$page_id_ = isset($rewrite_path_[2]) ? $rewrite_path_[2] : -1;
		$pbpage = pb_page($page_id_);

		if(!isset($pbpage)){
			return new PBError(503, __("잘못된 접근"), __("존재하지 않는 페이지입니다."));
		}

		global $pbpage_meta_map;
		$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

		return PB_DOCUMENT_PATH."includes/page/views/edit.php";
	}

	$other_page_ = pb_hook_apply_filters('pb_adminpage_manage_page_rewrite_handler', null, $sub_action_, $rewrite_path_);

	if(pb_is_error($other_page_)){
		return $other_page_;
	}

	if(strlen($other_page_)){
		return $other_page_;
	}

	return new PBError(503, __("잘못된 접근"), __("요청정보가 잘못됬습니다."));
}

function _pb_page_ajax_edit(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$page_data_ = _POST('page_data');

	if(!isset($page_data_)){
		pb_ajax_error(__("잘못된 요청"), __("필수 요청값이 누락되었습니다."));
	}

	$page_id_ = null;
	$page_data_['page_html'] = stripslashes($page_data_['page_html']);

	if(!strlen($page_data_['id'])){
		$page_data_['wrt_id'] = pb_current_user_id();
		$page_id_ = pb_page_write($page_data_);

		if(pb_is_error($page_id_)){
			pb_ajax_error($page_id_->error_title(), $page_id_->error_message());
		}
	}else{
		$page_id_ = $page_data_['id'];
		$result_ = pb_page_edit($page_id_, $page_data_);

		if(pb_is_error($result_)){
			pb_ajax_error($result_->error_title(), $result_->error_message());
		}
	}

	if(isset($page_data_['actived_editor_id'])){
		pb_user_meta_update(pb_current_user_id(), "page_actived_editor_id_".$page_id_, $page_data_['actived_editor_id']);	
	}

	pb_ajax_success(array(
		'page_id' => $page_id_,
		'redirect_url' => pb_admin_url("manage-page/edit/".$page_id_),
	));
}
pb_add_ajax('edit-page', "_pb_page_ajax_edit");

function _pb_page_ajax_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$page_id_ = _POST('page_id');

	if(!strlen($page_id_)){
		pb_ajax_error(__("잘못된 요청"), __("필수 요청값이 누락되었습니다."));
	}

	$page_data_ = pb_page($page_id_);
	
	if(!isset($page_data_)){
		pb_ajax_error(__("잘못된 요청"), __("페이지정보가 존재하지 않습니다."));
	}

	pb_page_delete($page_id_);

	echo json_encode(array(
		'success' => true,
		'redirect_url' => pb_admin_url("manage-page"),
	));
	pb_end();

}
pb_add_ajax('delete-page', "_pb_page_ajax_delete");

function _pb_page_ajax_update_slug(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$page_id_ = _POST('page_id');
	$slug_ = _POST('slug');

	if(!strlen($page_id_)){
		pb_ajax_error(__("잘못된 요청"), __("필수 요청값이 누락되었습니다."));
	}

	pb_page_edit($page_id_, array("slug" => $slug_));
	$page_data_ = pb_page($page_id_);

	pb_ajax_success(array(
		'slug' => $page_data_['slug'],
	));
}
pb_add_ajax('update-page-slug', "_pb_page_ajax_update_slug");

function _pb_page_ajax_update_status(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$page_id_ = _POST('page_id');
	$status_ = _POST('status');
	$page_data_ = pb_page($page_id_);

	if(!strlen($page_id_) || !isset($page_data_)){
		pb_ajax_error(__("잘못된 요청"), __("필수 요청값이 누락되었습니다."));
	}

	pb_page_update($page_id_, array(
		'status' => $status_,
		'mod_date' => pb_current_time(),
	));

	pb_ajax_success();
}
pb_add_ajax('change-page-status', "_pb_page_ajax_update_status");

function _pb_page_ajax_register_front_page(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$page_id_ = _POST('page_id');
	$status_ = _POST('status');
	$page_data_ = pb_page($page_id_);

	if(!strlen($page_id_) || !isset($page_data_)){
		pb_ajax_error(__("잘못된 요청"), __("필수 요청값이 누락되었습니다."));
	}

	pb_change_front_page($page_id_);

	pb_ajax_success();

}
pb_add_ajax('register-front-page', "_pb_page_ajax_register_front_page");

function _pb_page_ajax_unregister_front_page(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	pb_change_front_page(null);

	pb_ajax_success();

}
pb_add_ajax('unregister-front-page', "_pb_page_ajax_unregister_front_page");

?>