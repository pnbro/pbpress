<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

// if(!pb_is_adminpage()) return;

function _pb_authority_register_adminpage($results_){
	$results_['manage-authority'] = array(
		'name' => '권한관리',
		'type' => 'menu',
		'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."includes/authority/views/list.php",
		'authority_task' => 'manage_authority',
		'subpath' => null,
		'sort' => 7,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_authority_register_adminpage');

include(PB_DOCUMENT_PATH . "includes/authority/views/tables.php");
		
function _pb_ajax_admin_authority_load(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$auth_id_ = $_REQUEST["key"];
	$auth_data_ = pb_authority($auth_id_);
	
	echo json_encode(array(
		"success" => true,
		"results" => $auth_data_,
	));
	pb_end();
}
pb_add_ajax("pb-admin-authority-load", "_pb_ajax_admin_authority_load");

function _pb_ajax_admin_authority_insert(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$target_data_ = $_REQUEST["target_data"];

	global $pbdb;


	$insert_id_ = pb_authority_add(array(
		'auth_name' => $target_data_['auth_name'],
		'slug' => $target_data_['slug'],
		'reg_date' => pb_current_time(),
	));

	
	echo json_encode(array(
		"success" => true,
		'key' => $insert_id_,
	));
	pb_end();
}
pb_add_ajax("pb-admin-authority-insert", "_pb_ajax_admin_authority_insert");

function _pb_ajax_admin_authority_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	
	$auth_id_ = $_REQUEST["key"];
	$target_data_ = $_REQUEST["target_data"];

	global $pbdb;

	pb_authority_update($auth_id_, array(
		'auth_name' => $target_data_['auth_name'],
		'slug' => $target_data_['slug'],
		'MOD_DATE' => pb_current_time(),
	));

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-authority-update", "_pb_ajax_admin_authority_update");

function _pb_ajax_admin_authority_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$key_ = $_REQUEST["key"];

	global $pbdb;

	pb_authority_delete($key_);

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-authority-delete", "_pb_ajax_admin_authority_delete");

function _pb_ajax_admin_authority_task_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$auth_id_ = $_REQUEST["auth_id"];
	$auth_data_ = pb_authority($auth_id_);
	$grant_list_ = isset($_REQUEST["grant_list"]) ? $_REQUEST["grant_list"] : array();
	$revoke_list_ = isset($_REQUEST["revoke_list"]) ? $_REQUEST["revoke_list"] : array();

	global $pbdb;

	foreach($revoke_list_ as $slug_){
		$task_data_ = pb_authority_task_by_slug($auth_data_["slug"], $slug_);
		if(isset($task_data_)){
			pb_authority_task_delete($task_data_['id']);
		}
	}

	$task_types_ = pb_authority_task_types();

	foreach($grant_list_ as $slug_){
		$task_data_ = pb_authority_task_by_slug($auth_data_["slug"], $slug_);
		if(isset($task_data_)) continue;

		pb_authority_task_add(array(
			'auth_id' => $auth_id_,
			'slug' => $slug_,
			'REG_DATE' => pb_current_time(),
		));
	}

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-authority-task-update", "_pb_ajax_admin_authority_task_update");

?>