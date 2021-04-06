<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_authority_register_adminpage($results_){
	$results_['manage-authority'] = array(
		'name' => __('권한관리'),
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

__iinclude(PB_DOCUMENT_PATH . "includes/authority/views/tables.php");
		
function _pb_ajax_admin_authority_load(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$auth_id_ = $_REQUEST["key"];
	$auth_data_ = pb_authority($auth_id_);

	pb_ajax_success(array(
		"results" => $auth_data_,
	));
}
pb_add_ajax("pb-admin-authority-load", "_pb_ajax_admin_authority_load");

function _pb_ajax_admin_authority_insert(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$target_data_ = $_REQUEST["target_data"];
	$target_data_['reg_date'] = pb_current_time();
	
	$insert_id_ = pb_authority_add($target_data_);

	pb_ajax_success(array(
		'key' => $insert_id_,
	));
}
pb_add_ajax("pb-admin-authority-insert", "_pb_ajax_admin_authority_insert");

function _pb_ajax_admin_authority_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	
	$auth_id_ = $_REQUEST["key"];
	$target_data_ = $_REQUEST["target_data"];
	$target_data_['mod_date'] = pb_current_time();

	global $pbdb;

	pb_authority_update($auth_id_, $target_data_);

	pb_ajax_success();
}
pb_add_ajax("pb-admin-authority-update", "_pb_ajax_admin_authority_update");

function _pb_ajax_admin_authority_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$key_ = $_REQUEST["key"];

	global $pbdb;

	pb_authority_delete($key_);

	pb_ajax_success();
}
pb_add_ajax("pb-admin-authority-delete", "_pb_ajax_admin_authority_delete");

function _pb_ajax_admin_authority_task_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_authority")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
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

	pb_ajax_success();
}
pb_add_ajax("pb-admin-authority-task-update", "_pb_ajax_admin_authority_task_update");

?>