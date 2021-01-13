<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

__iinclude(PB_DOCUMENT_PATH . "includes/page/views/revision-tables.php");

function _pb_admin_ajax_page_revision_load_master(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$revision_id_ = $_REQUEST["key"];
	$revision_data_ = pb_page_revision($revision_id_);
	
	pb_ajax_success(array(
		'results' => $revision_data_
	));
}
pb_add_ajax('pb-admin-page-revision-load', '_pb_admin_ajax_page_revision_load_master');

function _pb_admin_ajax_page_revision_restore(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$revision_id_ = _POST('revision_id', -1);
	$revision_data_ = pb_page_revision($revision_id_);

	if(!isset($revision_data_)){
		pb_ajax_error(__("잘못된 요청"), __("요청정보가 잘못되었습니다."));
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
	
	pb_ajax_success();
}
pb_add_ajax('pb-admin-restore-page-from-revision', '_pb_admin_ajax_page_revision_restore');


function _pb_admin_ajax_page_revision_delete(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$revision_id_ = _POST('revision_id', -1);
	$revision_data_ = pb_page_revision($revision_id_);

	if(!isset($revision_data_)){
		pb_ajax_error(__("잘못된 요청"), __("요청정보가 잘못되었습니다."));
	}

	pb_page_revision_delete($revision_id_);

	pb_ajax_success();
}
pb_add_ajax('pb-admin-delete-page-revision', '_pb_admin_ajax_page_revision_delete');

?>