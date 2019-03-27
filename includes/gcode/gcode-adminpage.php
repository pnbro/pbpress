<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

// if(!pb_is_adminpage()) return;

function _pb_gcode_register_adminpage($results_){
	$results_['manage-gcode'] = array(
		'name' => '공통코드',
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

include(PB_DOCUMENT_PATH . "includes/gcode/views/tables.php");
		
function _pb_ajax_admin_gcode_load(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$target_data_ = $_REQUEST["target_data"];

	global $pbdb;


	$insert_id_ = $pbdb->insert("gcode", array(
		'code_id' => $target_data_['code_id'],
		'code_nm' => $target_data_['code_nm'],
		'use_yn' => $target_data_['use_yn'],
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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	
	$code_id_ = $_REQUEST["key"];
	$target_data_ = $_REQUEST["target_data"];

	global $pbdb;

	$pbdb->update("gcode", array(
		'code_id' => $target_data_['code_id'],
		'code_nm' => $target_data_['code_nm'],
		'use_yn' => $target_data_['use_yn'],
		'mod_date' => pb_current_time(),
	), array("code_id" => $code_id_));

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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$key_ = $_REQUEST["key"];

	global $pbdb;

	$pbdb->delete("gcode", array("code_id" => $key_));

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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"][0];
	$code_did_ = $_REQUEST["key"][1];

	global $pbdb;

	$code_data_ = $pbdb->get_first_row("
		SELECT Code_id
		,code_did
        ,code_dnm
        ,use_yn
        ,sort_char
		FROM gcode_dtl
		WHERE code_id = '".pb_database_escape_string($code_id_)."'
		AND code_did = '".pb_database_escape_string($code_did_)."'
	");
	
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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$target_data_ = $_REQUEST["target_data"];

	global $pbdb;

	$insert_id_ = $pbdb->insert("gcode_dtl", array(
		'code_id' => $target_data_['code_id'],
		'code_did' => $target_data_['code_did'],
		'code_dnm' => $target_data_['code_dnm'],
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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"][0];
	$code_did_ = $_REQUEST["key"][1];
	$target_data_ = $_REQUEST["target_data"];

	global $pbdb;

	$pbdb->update("gcode_dtl", array(
		'code_did' => $target_data_['code_did'],
		'code_dnm' => $target_data_['code_dnm'],
		'use_yn' => $target_data_['use_yn'],
		'sort_char' => $target_data_['sort_char'],
		'mod_date' => pb_current_time(),
	), array("code_id" => $code_id_,"code_did" => $code_did_));

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
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$code_id_ = $_REQUEST["key"][0];
	$code_did_ = $_REQUEST["key"][1];

	global $pbdb;

	$pbdb->delete("gcode_dtl", array("code_id" => $code_id_,"code_did" => $code_did_));

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax("pb-admin-gcode-dtl-delete", "_pb_ajax_admin_gcode_dtl_delete");

?>