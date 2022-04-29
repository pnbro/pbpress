<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

// if(!pb_is_adminpage()) return;

function _pb_gcode_register_adminpage($results_){
	$results_['manage-gcode'] = array(
		'name' => __('공통코드'),
		'type' => 'menu',
		'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."includes/gcode/views/list.php",
		'authority_task' => 'manage_site',
		'subpath' => null,
		'sort' => 5,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_gcode_register_adminpage');

__iinclude(PB_DOCUMENT_PATH . "includes/gcode/views/tables.php");
		
function _pb_ajax_admin_gcode_load(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"];
	$code_data_ = pb_gcode($code_id_);
	
	echo json_encode(array(
		"success" => true,
		"results" => $code_data_,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-load", "_pb_ajax_admin_gcode_load");

function _pb_ajax_admin_gcode_insert(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$target_data_ = $_REQUEST["target_data"];

	$insert_id_ = pb_gcode_add(array(
		'code_id' => $target_data_['code_id'],
		'code_nm' => $target_data_['code_nm'],
		'use_yn' => $target_data_['use_yn'],
		
		'col1' => isset($target_data_['col1']) && strlen($target_data_['col1']) ? $target_data_['col1'] : null,
		'col2' => isset($target_data_['col2']) && strlen($target_data_['col2']) ? $target_data_['col2'] : null,
		'col3' => isset($target_data_['col3']) && strlen($target_data_['col3']) ? $target_data_['col3'] : null,
		'col4' => isset($target_data_['col4']) && strlen($target_data_['col4']) ? $target_data_['col4'] : null,

		'reg_date' => pb_current_time(),
	));

	echo json_encode(array(
		"success" => true,
		'key' => $insert_id_,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-insert", "_pb_ajax_admin_gcode_insert");

function _pb_ajax_admin_gcode_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	
	$code_id_ = $_REQUEST["key"];
	$target_data_ = $_REQUEST["target_data"];

	pb_gcode_update($code_id_, array(
		'code_id' => $target_data_['code_id'],
		'code_nm' => $target_data_['code_nm'],
		'use_yn' => $target_data_['use_yn'],

		'col1' => isset($target_data_['col1']) && strlen($target_data_['col1']) ? $target_data_['col1'] : null,
		'col2' => isset($target_data_['col2']) && strlen($target_data_['col2']) ? $target_data_['col2'] : null,
		'col3' => isset($target_data_['col3']) && strlen($target_data_['col3']) ? $target_data_['col3'] : null,
		'col4' => isset($target_data_['col4']) && strlen($target_data_['col4']) ? $target_data_['col4'] : null,

		'mod_date' => pb_current_time(),
	));

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-update", "_pb_ajax_admin_gcode_update");

function _pb_ajax_admin_gcode_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$key_ = $_REQUEST["key"];

	pb_gcode_delete($key_);

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-delete", "_pb_ajax_admin_gcode_delete");

function _pb_ajax_admin_gcode_dtl_load(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"][0];
	$code_did_ = $_REQUEST["key"][1];

	$code_data_ = pb_gcode_dtl($code_id_, $code_did_);
	
	echo json_encode(array(
		"success" => true,
		"results" => $code_data_,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-dtl-load", "_pb_ajax_admin_gcode_dtl_load");

function _pb_ajax_admin_gcode_dtl_insert(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$target_data_ = $_REQUEST["target_data"];

	$insert_id_ = pb_gcode_dtl_add(array(
		'code_id' => $target_data_['code_id'],
		'code_did' => $target_data_['code_did'],
		'code_dnm' => $target_data_['code_dnm'],

		'col1' => isset($target_data_['col1']) && strlen($target_data_['col1']) ? $target_data_['col1'] : null,
		'col2' => isset($target_data_['col2']) && strlen($target_data_['col2']) ? $target_data_['col2'] : null,
		'col3' => isset($target_data_['col3']) && strlen($target_data_['col3']) ? $target_data_['col3'] : null,
		'col4' => isset($target_data_['col4']) && strlen($target_data_['col4']) ? $target_data_['col4'] : null,

		'use_yn' => $target_data_['use_yn'],
		'sort_char' => $target_data_['sort_char'],
		'reg_date' => pb_current_time(),
	));

	
	echo json_encode(array(
		"success" => true,
		'key' => $insert_id_,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-dtl-insert", "_pb_ajax_admin_gcode_dtl_insert");

function _pb_ajax_admin_gcode_dtl_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"][0];
	$code_did_ = $_REQUEST["key"][1];
	$target_data_ = $_REQUEST["target_data"];

	pb_gcode_dtl_update($code_id_, $code_did_, array(
		'code_did' => $target_data_['code_did'],
		'code_dnm' => $target_data_['code_dnm'],
		'use_yn' => $target_data_['use_yn'],
		'col1' => isset($target_data_['col1']) && strlen($target_data_['col1']) ? $target_data_['col1'] : null,
		'col2' => isset($target_data_['col2']) && strlen($target_data_['col2']) ? $target_data_['col2'] : null,
		'col3' => isset($target_data_['col3']) && strlen($target_data_['col3']) ? $target_data_['col3'] : null,
		'col4' => isset($target_data_['col4']) && strlen($target_data_['col4']) ? $target_data_['col4'] : null,
		'sort_char' => $target_data_['sort_char'],
		'mod_date' => pb_current_time(),
	));

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-dtl-update", "_pb_ajax_admin_gcode_dtl_update");

function _pb_ajax_admin_gcode_dtl_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"][0];
	$code_did_ = $_REQUEST["key"][1];

	pb_gcode_dtl_delete($code_id_, $code_did_);

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-dtl-delete", "_pb_ajax_admin_gcode_dtl_delete");

?>