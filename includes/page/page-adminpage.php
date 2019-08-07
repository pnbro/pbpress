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
		'name' => '페이지관리',
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

include(PB_DOCUMENT_PATH . "includes/page/views/tables.php");

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
			return new PBError(503, "잘못된 접근", "존재하지 않는 페이지입니다.");
		}

		global $pbpage_meta_map;
		$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

		return PB_DOCUMENT_PATH."includes/page/views/edit.php";
	}

	return new PBError(503, "잘못된 접근", "요청정보가 잘못됬습니다.");
}


function _pb_page_register_authority_task_types($results_){
	$results_['manage_page'] = array(
		'name' => '페이지관리'
	);

	return $results_;
}
pb_hook_add_filter('pb_authority_task_types', "_pb_page_register_authority_task_types");

function _pb_page_installed_tables(){
	$check_ = pb_authority_task_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_page");
	if(isset($check_)) return;

	$auth_data_ = pb_authority_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR);

	pb_authority_task_add(array(
		'auth_id' => $auth_data_['id'],
		'slug' => "manage_page",
		'task_name' => "페이지관리",
		'reg_date' => pb_current_time(),
	));

}
pb_hook_add_action('pb_installed_tables', "_pb_page_installed_tables");

function _pb_page_ajax_edit(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$page_data_ = isset($_POST['page_data']) ? $_POST['page_data'] : null;

	if(!isset($page_data_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "필수 요청값이 누락되었습니다.",
		));
		pb_end();
	}

	$page_id_ = null;
	$page_data_['page_html'] = stripslashes($page_data_['page_html']);

	if(!strlen($page_data_['id'])){
		$page_data_['wrt_id'] = pb_current_user_id();
		$page_id_ = pb_page_write($page_data_);

		if(pb_is_error($page_id_)){
			echo json_encode(array(
				'success' => false,
				'error_title' => $page_id_->error_title(),
				'error_message' => $page_id_->error_message(),
			));
			pb_end();
		}
	}else{
		$page_id_ = $page_data_['id'];
		$result_ = pb_page_edit($page_id_, $page_data_);

		if(pb_is_error($result_)){
			echo json_encode(array(
				'success' => false,
				'error_title' => $result_->error_title(),
				'error_message' => $result_->error_message(),
			));
			pb_end();
		}
	}

	if(isset($page_data_['actived_editor_id'])){
		pb_page_meta_update($page_id_, "actived_editor_id", $page_data_['actived_editor_id']);	
	}

	echo json_encode(array(
		'success' => true,
		'page_id' => $page_id_,
		'redirect_url' => pb_admin_url("manage-page/edit/".$page_id_),
	));
	pb_end();

}
pb_add_ajax('edit-page', "_pb_page_ajax_edit");

function _pb_page_ajax_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$page_id_ = isset($_POST['page_id']) ? $_POST['page_id'] : null;

	if(!strlen($page_id_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "필수 요청값이 누락되었습니다.",
		));
		pb_end();
	}

	$page_data_ = pb_page($page_id_);
	
	if(!isset($page_data_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "페이지정보가 존재하지 않습니다.",
		));
		pb_end();
	}

	pb_page_delete($page_id_);

	echo json_encode(array(
		'success' => true,
		'redirect_url' => pb_admin_url("manage-page"),
	));
	pb_end();

}
pb_add_ajax('delete-page', "_pb_page_ajax_delete");

?>