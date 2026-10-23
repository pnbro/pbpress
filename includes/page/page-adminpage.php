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
	// 비로그인 → 로그인 페이지로 리다이렉트 (redirect_url 포함)
	if(!pb_is_user_logged_in()){
		pb_redirect(pb_admin_login_url($_SERVER['REQUEST_URI']));
		pb_end();
	}

	// 로그인은 됐으나 권한 없음 → 에러 페이지
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

pb_chunk_text_register(
	'edit-page-chunked',
	function(){
		if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
			pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
		}
	},
	function($merged_text_, $params_){
		$page_data_ = isset($params_['page_data']) ? $params_['page_data'] : null;

		if(!isset($page_data_)){
			return new PBError(400, __("잘못된 요청"), __("필수 요청값이 누락되었습니다."));
		}

		$page_data_['page_html'] = $merged_text_;

		$page_id_ = null;

		if(!strlen($page_data_['id'])){
			$page_data_['wrt_id'] = pb_current_user_id();
			$page_id_ = pb_page_write($page_data_);

			if(pb_is_error($page_id_)){
				return $page_id_;
			}
		}else{
			$page_id_ = $page_data_['id'];
			$result_ = pb_page_edit($page_id_, $page_data_);

			if(pb_is_error($result_)){
				return $result_;
			}
		}

		if(isset($page_data_['actived_editor_id'])){
			pb_user_meta_update(pb_current_user_id(), "page_actived_editor_id_".$page_id_, $page_data_['actived_editor_id']);
		}

		return array(
			'page_id' => $page_id_,
			'redirect_url' => pb_admin_url("manage-page/edit/".$page_id_),
		);
	}
);

// Each preview is a session-bound snapshot: refresh and other tabs never consume it.
function _pb_page_store_preview($page_html_, $params_ = array()){
	$previews_ = pb_session_get('_page_preview_snapshots');
	if(!is_array($previews_)) $previews_ = array();
	$now_ = time();
	foreach($previews_ as $key_ => $preview_){
		if(!is_array($preview_) || ($preview_['expires_at'] ?? 0) <= $now_
			|| (string)($preview_['user_id'] ?? '') !== (string)pb_current_user_id()) unset($previews_[$key_]);
	}
	// Retain the five most recent snapshots for at most two hours.
	while(count($previews_) >= 5) array_shift($previews_);
	$token_ = bin2hex(random_bytes(16));
	$preview_ = array('page_html' => $page_html_, 'user_id' => pb_current_user_id(), 'expires_at' => $now_ + 7200);
	foreach(array('page_id', 'page_title', 'page_slug', 'page_status') as $key_){
		if(isset($params_[$key_]) && is_scalar($params_[$key_])) $preview_[$key_] = (string)$params_[$key_];
	}
	$previews_[$token_] = $preview_;
	pb_session_put('_page_preview_snapshots', $previews_);
	return array('preview_id' => $token_, 'preview_url' => pb_home_url('__page-live-edit-preview?preview_id='.$token_));
}

function _pb_page_preview_snapshot($token_){
	if(!is_string($token_) || !preg_match('/^[a-f0-9]{32}$/D', $token_)) return null;
	$previews_ = pb_session_get('_page_preview_snapshots');
	$preview_ = is_array($previews_) && isset($previews_[$token_]) ? $previews_[$token_] : null;
	if(!is_array($preview_) || ($preview_['expires_at'] ?? 0) <= time()
		|| (string)($preview_['user_id'] ?? '') !== (string)pb_current_user_id()) return null;
	return $preview_;
}

function _pb_page_ajax_load_preview(){
	if(!pb_user_has_authority_task(pb_current_user_id(), 'manage_page')){
		pb_ajax_error(__('권한없음'), __('접근권한이 없습니다.'));
	}
	$token_ = _POST('preview_id');
	$preview_ = _pb_page_preview_snapshot($token_);
	$page_id_ = _POST('page_id');
	if(!$preview_ || !is_scalar($page_id_) || (string)($preview_['page_id'] ?? '') !== (string)$page_id_){
		pb_ajax_error(__('미리보기 만료'), __('미리보기 보관 시간이 지났습니다. 편집 화면에서 다시 미리보기를 눌러 주세요.'));
	}
	unset($preview_['user_id']);
	$preview_['preview_url'] = pb_home_url('__page-live-edit-preview?preview_id='.$token_);
	pb_ajax_success($preview_);
}
pb_add_ajax('load-page-preview', '_pb_page_ajax_load_preview');

// 미리보기 임시저장 AJAX
function _pb_page_ajax_preview(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$page_html_ = _POST('page_html');

	if(!is_string($page_html_) || !strlen($page_html_)){
		pb_ajax_error(__("잘못된 요청"), __("미리보기 데이터가 누락되었습니다."));
	}

	pb_ajax_success(_pb_page_store_preview($page_html_, array(
		'page_id' => _POST('page_id'), 'page_title' => _POST('page_title'),
		'page_slug' => _POST('page_slug'), 'page_status' => _POST('page_status'),
	)));
}
pb_add_ajax('preview-page', "_pb_page_ajax_preview");

pb_chunk_text_register(
	'preview-page-chunked',
	function(){
		if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
			pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
		}
	},
	function($merged_text_, $params_){
		return _pb_page_store_preview($merged_text_, $params_);
	}
);

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
