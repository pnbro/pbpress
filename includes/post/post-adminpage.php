<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_post_register_adminpage($results_){

	$post_types_ = pb_post_types();

	foreach($post_types_ as $key_ => $type_data_){
		$results_["manage-{$key_}"] = array(
			'name' => "{$type_data_['name']} 관리",
			'type' => 'menu',
			'post_type' => $key_,
			'rewrite_handler' => "_pb_post_rewrite_handler_for_adminpage",
			'authority_task' => "manage_{$key_}",
			'subpath' => null,
			'sort' => 7,
		);
	}

		
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_post_register_adminpage');

include(PB_DOCUMENT_PATH . "includes/post/views/tables.php");

function _pb_post_rewrite_handler_for_adminpage($rewrite_path_){

	$adminpage_data_ = pb_current_adminpage();
	$post_type_ = $adminpage_data_['post_type'];

	global $pbpost_type, $pbpost_type_data;

	$post_types_ = pb_post_types();

	$pbpost_type = $post_type_;
	$pbpost_type_data = isset($post_types_[$post_type_]) ? $post_types_[$post_type_] : null;

	if(!isset($pbpost_type_data)){
		return new PBError(503, "잘못된 접근", "존재하지 않는 글형식입니다.");
	}
	
	if(count($rewrite_path_) < 2){
		return PB_DOCUMENT_PATH."includes/post/views/list.php";
	}

	$sub_action_ = $rewrite_path_[1];

	if($sub_action_ === "add"){
		return PB_DOCUMENT_PATH."includes/post/views/edit.php";		
	}

	global $pbpost;

	if($sub_action_ === "edit"){
		$post_id_ = isset($rewrite_path_[2]) ? $rewrite_path_[2] : -1;
		$pbpost = pb_post($post_id_);

		if(!isset($pbpost) || $pbpost['type'] !== $pbpost_type){
			return new PBError(503, "잘못된 접근", "존재하지 않는 글입니다.");
		}

		global $pbpost_meta_map;
		$pbpost_meta_map = pb_post_meta_map($pbpost['id']);

		return PB_DOCUMENT_PATH."includes/post/views/edit.php";
	}

	$other_path_ = pb_hook_apply_filters('pb_adminpage_manage_post_{$pbpost_type}_rewrite_handler', null, $sub_action_, $rewrite_path_);

	if(pb_is_error($other_path_)){
		return $other_path_;
	}

	if(strlen($other_path_)){
		return $other_path_;
	}

	$other_path_ = pb_hook_apply_filters('pb_adminpage_manage_post_rewrite_handler', null, $sub_action_, $rewrite_path_);

	if(pb_is_error($other_path_)){
		return $other_path_;
	}

	if(strlen($other_path_)){
		return $other_path_;
	}

	return new PBError(503, "잘못된 접근", "요청정보가 잘못됬습니다.");
}


function _pb_post_register_authority_task_types($results_){
	$post_types_ = pb_post_types();

	foreach($post_types_ as $key_ => $type_data_){
		$results_["manage_{$key_}"] = array(
			'name' => "{$type_data_['name']} 관리"
		);
	}

	return $results_;
}
pb_hook_add_filter('pb_authority_task_types', "_pb_post_register_authority_task_types");

function _pb_post_installed_tables(){
	$post_types_ = pb_post_types();

	foreach($post_types_ as $key_ => $type_data_){
		$check_ = pb_authority_task_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_{$key_}");
		if(isset($check_)) continue;

		$auth_data_ = pb_authority_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR);

		pb_authority_task_add(array(
			'auth_id' => $auth_data_['id'],
			'slug' => "manage_{$key_}",
			'reg_date' => pb_current_time(),
		));
	}	
}
pb_hook_add_action('pb_installed_tables', "_pb_post_installed_tables");

function _pb_post_ajax_edit(){
	$post_data_ = isset($_POST['post_data']) ? $_POST['post_data'] : null;

	if(!isset($post_data_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "필수 요청값이 누락되었습니다.",
		));
		pb_end();
	}

	$post_type_ = $post_data_['type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	
	$post_id_ = null;
	$post_data_['post_html'] = stripslashes($post_data_['post_html']);

	if(!strlen($post_data_['id'])){
		$post_data_['wrt_id'] = pb_current_user_id();
		$post_id_ = pb_post_write($post_data_);

		if(pb_is_error($post_id_)){
			echo json_encode(array(
				'success' => false,
				'error_title' => $post_id_->error_title(),
				'error_message' => $post_id_->error_message(),
			));
			pb_end();
		}
	}else{
		$post_id_ = $post_data_['id'];
		$result_ = pb_post_edit($post_id_, $post_data_);

		if(pb_is_error($result_)){
			echo json_encode(array(
				'success' => false,
				'error_title' => $result_->error_title(),
				'error_message' => $result_->error_message(),
			));
			pb_end();
		}
	}

	if(isset($post_data_['actived_editor_id'])){
		pb_post_meta_update($post_id_, "actived_editor_id", $post_data_['actived_editor_id']);	
	}

	echo json_encode(array(
		'success' => true,
		'post_id' => $post_id_,
		'redirect_url' => pb_admin_url("manage-{$post_type_}/edit/".$post_id_),
	));
	pb_end();

}
pb_add_ajax('edit-post', "_pb_post_ajax_edit");

function _pb_post_ajax_delete(){
	$post_id_ = isset($_POST['post_id']) ? $_POST['post_id'] : null;

	if(!strlen($post_id_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "필수 요청값이 누락되었습니다.",
		));
		pb_end();
	}

	$post_data_ = pb_post($post_id_);
	
	if(!isset($post_data_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "글정보가 존재하지 않습니다.",
		));
		pb_end();
	}

	$post_type_ = $post_data_['type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	pb_post_delete($post_id_);

	echo json_encode(array(
		'success' => true,
		'redirect_url' => pb_admin_url("manage-{$post_type_}"),
	));
	pb_end();

}
pb_add_ajax('delete-post', "_pb_post_ajax_delete");

function _pb_post_ajax_update_slug(){
	$post_id_ = isset($_POST['post_id']) ? $_POST['post_id'] : null;
	$slug_ = isset($_POST['slug']) ? $_POST['slug'] : null;

	if(!strlen($post_id_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "필수 요청값이 누락되었습니다.",
		));
		pb_end();
	}

	$post_data_ = pb_post($post_id_);
	$post_type_ = $post_data_['type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	pb_post_edit($post_id_, array("slug" => $slug_));
	$post_data_ = pb_post($post_id_);

	echo json_encode(array(
		'success' => true,
		'slug' => $post_data_['slug'],
	));
	pb_end();

}
pb_add_ajax('update-post-slug', "_pb_post_ajax_update_slug");

function _pb_post_ajax_update_status(){
	$post_id_ = isset($_POST['post_id']) ? $_POST['post_id'] : null;
	$status_ = isset($_POST['status']) ? $_POST['status'] : null;
	$post_data_ = pb_post($post_id_);
	$post_type_ = $post_data_['type'];

	if(!strlen($post_id_) || !isset($post_data_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => "잘못된 요청",
			'error_message' => "필수 요청값이 누락되었습니다.",
		));
		pb_end();
	}

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	pb_post_update($post_id_, array(
		'status' => $status_,
		'mod_date' => pb_current_time(),
	));

	echo json_encode(array(
		'success' => true,
	));
	pb_end();

}
pb_add_ajax('change-post-status', "_pb_post_ajax_update_status");

?>