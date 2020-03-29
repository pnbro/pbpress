<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

include(PB_DOCUMENT_PATH . "includes/page/views/revision-tables.php");

function _pb_admin_ajax_page_revision_load_master(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$revision_id_ = $_REQUEST["key"];
	$revision_data_ = pb_page_revision($revision_id_);
	
	echo json_encode(array(
		"success" => true,
		"results" => $revision_data_,
	));
	pb_end();
}
pb_add_ajax('pb-admin-page-revision-load', '_pb_admin_ajax_page_revision_load_master');

function _pb_admin_ajax_page_revision_restore(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$revision_id_ = _POST('revision_id', -1);
	$revision_data_ = pb_page_revision($revision_id_);

	if(!isset($revision_data_)){
		echo json_encode(array(
			"success" => false,
			"error_title" => "잘못된 요청",
			"error_message" => "요청정보가 잘못되었습니다.",
		));
		pb_end();
	}

	$result_ = pb_page_restore_from_revision($revision_data_['page_id'], $revision_id_);

	if(pb_is_error($result_)){
		echo json_encode(array(
			"success" => false,
			"error_title" => $result_->error_title(),
			"error_message" => $result_->error_message(),
		));
		pb_end();
	}
	
	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax('pb-admin-restore-page-from-revision', '_pb_admin_ajax_page_revision_restore');


function _pb_admin_ajax_page_revision_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$revision_id_ = _POST('revision_id', -1);
	$revision_data_ = pb_page_revision($revision_id_);

	if(!isset($revision_data_)){
		echo json_encode(array(
			"success" => false,
			"error_title" => "잘못된 요청",
			"error_message" => "요청정보가 잘못되었습니다.",
		));
		pb_end();
	}

	pb_page_revision_delete($revision_id_);

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax('pb-admin-delete-page-revision', '_pb_admin_ajax_page_revision_delete');

?>