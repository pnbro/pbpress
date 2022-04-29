<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

__iinclude(PB_DOCUMENT_PATH . "includes/post/views/revision-tables.php");

function _pb_admin_ajax_post_revision_load_master(){
	$revision_id_ = $_REQUEST["key"];
	$revision_data_ = pb_post_revision($revision_id_);

	$post_type_ = $revision_data_['post_type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}
	
	echo json_encode(array(
		"success" => true,
		"results" => $revision_data_,
	));
	pb_end();
}
pb_add_ajax('pb-admin-post-revision-load', '_pb_admin_ajax_post_revision_load_master');

function _pb_admin_ajax_post_revision_restore(){
	$revision_id_ = _POST('revision_id', -1);
	$revision_data_ = pb_post_revision($revision_id_);
	$post_type_ = $revision_data_['post_type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	if(!isset($revision_data_)){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("잘못된 요청"),
			"error_message" => __("요청정보가 잘못되었습니다."),
		));
		pb_end();
	}

	$result_ = pb_post_restore_from_revision($revision_data_['post_id'], $revision_id_);

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
pb_add_ajax('pb-admin-restore-post-from-revision', '_pb_admin_ajax_post_revision_restore');


function _pb_admin_ajax_post_revision_delete(){
	$revision_id_ = _POST('revision_id', -1);
	$revision_data_ = pb_post_revision($revision_id_);
	$post_type_ = $revision_data_['post_type'];

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_{$post_type_}")){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("권한없음"),
			"error_message" => __("접근권한이 없습니다."),
		));
		pb_end();
	}

	if(!isset($revision_data_)){
		echo json_encode(array(
			"success" => false,
			"error_title" => __("잘못된 요청"),
			"error_message" => __("요청정보가 잘못되었습니다."),
		));
		pb_end();
	}

	pb_post_revision_delete($revision_id_);

	echo json_encode(array(
		"success" => true,
	));
	pb_end();
}
pb_add_ajax('pb-admin-delete-post-revision', '_pb_admin_ajax_post_revision_delete');

?>