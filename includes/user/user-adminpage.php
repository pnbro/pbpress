<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_user_register_adminpage($results_){

	$results_['user'] = array(
		'name' => '사용자관리',
		'type' => 'directory',
		'sort' => 3,
	);
		
	$results_['manage-user'] = array(
		'name' => '사용자관리',
		'type' => 'menu',
		'directory' => 'user',
		'page' => PB_DOCUMENT_PATH."includes/user/views/handler.php",
		'authority_task' => 'manage_user',
		'rewrite_handler' => "_user_adminpage_rewrite_handler",
		'sort' => 7,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_user_register_adminpage');

__iinclude(PB_DOCUMENT_PATH . 'includes/user/views/tables.php');

function _user_adminpage_rewrite_handler($rewrite_path_){
	$subaction_ = isset($rewrite_path_[1]) ? $rewrite_path_[1] : "list";

	switch($subaction_){
		case "list" : 
			return (PB_DOCUMENT_PATH."includes/user/views/list.php");
			break;
		case "edit" : 

			$user_id_ = isset($rewrite_path_[2]) ? $rewrite_path_[2] : null;
			if(!isset($user_id_)){
				return new PBError(503, "잘못된 접근", "잘못된 요청입니다.");
			}

			global $user_data;
			$user_data = pb_user($user_id_);

			if(!isset($user_data)){
				return new PBError(503, "잘못된 접근", "존재하지 않는 사용자입니다.");
			}

			return (PB_DOCUMENT_PATH."includes/user/views/edit.php");

			break;
		case "add" : 

			return (PB_DOCUMENT_PATH."includes/user/views/add.php");
			break;
		default :

			return new PBError(503, "잘못된 접근", "잘못된 요청입니다.");

		break;
	}
}

function _pb_ajax_admin_user_check_login(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_user")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$user_id_ = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : -1;
	$user_login_ = $_REQUEST["user_login"];

	$check_data_ = pb_user_by_user_login($user_login_);

	if(isset($check_data_) && $check_data_['id'] === $user_id_){
		echo json_encode(array("success" => true));
		pb_end();
	}

	if(isset($check_data_)){
		echo 'failed';
		pb_end();	
	}


	echo json_encode(array("success" => true));
	pb_end();
}
pb_add_ajax("pb-admin-manage-user-check-login", "_pb_ajax_admin_user_check_login");
	

function _pb_ajax_admin_user_check_email(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_user")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	
	$user_id_ = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : -1;
	$user_email_ = $_REQUEST["user_email"];

	$check_data_ = pb_user_by_user_email($user_email_);

	if(isset($check_data_) && $check_data_['id'] === $user_id_){
		echo json_encode(array("success" => true));
		pb_end();
	}

	if(isset($check_data_)){
		echo 'failed';
		pb_end();	
	}


	echo json_encode(array("success" => true));
	pb_end();
}
pb_add_ajax("pb-admin-manage-user-check-email", "_pb_ajax_admin_user_check_email");

function _pb_ajax_admin_user_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_user")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$request_data_ = _POST('request_data');

	if(!pb_verify_request_token("pbpress_manage_user", $request_data_['_request_chip'])){
		echo json_encode(array(
			'success' => false,
			'error_title' => '에러발생',
			'error_message' => '요청토큰이 잘못되었습니다.',
		));
		pb_end();	
	}

	$update_data_ = array(
		'user_name' => $request_data_['user_name'],
		'user_email' => $request_data_['user_email'],
	);

	if(isset($request_data_['user_pass']) && strlen($request_data_['user_pass'])){
		$update_data_['user_pass'] = pb_crypt_hash(pb_crypt_decrypt($request_data_['user_pass']));
	}

	if(isset($request_data_['user_status']) && strlen($request_data_['user_status'])){
		$update_data_['status'] = $request_data_['user_status'];
	}

	$request_data_['user_authority'] = isset($request_data_['user_authority']) ? $request_data_['user_authority']: array();

	if(((int)$request_data_['id']) !== 1){ //root admin

		$temp_authority_list_ = pb_authority_list();
		$revoke_list_ = array();
		
		foreach($temp_authority_list_ as $row_data_){
			if(!in_array($row_data_['slug'], $request_data_['user_authority'])){
				$revoke_list_[] = $row_data_['slug'];
			}
		}

		foreach($revoke_list_ as $revoke_slug_){
			pb_user_revoke_authority($request_data_['id'], $revoke_slug_);
		}

		foreach($request_data_['user_authority'] as $grant_slug_){
			pb_user_grant_authority($request_data_['id'], $grant_slug_);
		}
	}


	pb_user_update($request_data_['id'], $update_data_);

	echo json_encode(array(
		"success" => true,
		"redirect_url" => pb_admin_url("manage-user/edit/".$request_data_['id']),
	));
	pb_end();
}
pb_add_ajax("pb-admin-manage-user-do-update", "_pb_ajax_admin_user_update");

function _pb_ajax_admin_user_add(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_user")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$request_data_ = _POST('request_data');

	if(!pb_verify_request_token("pbpress_manage_user", $request_data_['_request_chip'])){
		echo json_encode(array(
			'success' => false,
			'error_title' => '에러발생',
			'error_message' => '요청토큰이 잘못되었습니다.',
		));
		pb_end();	
	}

	$add_data_ = array(
		'user_name' => $request_data_['user_name'],
		'user_email' => $request_data_['user_email'],
		'user_login' => $request_data_['user_login'],
		'user_pass' => pb_crypt_hash(pb_crypt_decrypt($request_data_['user_pass'])),
	);

	if(isset($request_data_['user_status']) && strlen($request_data_['user_status'])){
		$add_data_['status'] = $request_data_['user_status'];
	}

	$insert_id_ = pb_user_add($add_data_);

	$request_data_['user_authority'] = isset($request_data_['user_authority']) ? $request_data_['user_authority']: array();
	
	foreach($request_data_['user_authority'] as $grant_slug_){
		pb_user_grant_authority($insert_id_, $grant_slug_);
	}

	echo json_encode(array(
		"success" => true,
		"redirect_url" => pb_admin_url("manage-user/edit/".$insert_id_),
	));
	pb_end();
}
pb_add_ajax("pb-admin-manage-user-do-add", "_pb_ajax_admin_user_add");

?>