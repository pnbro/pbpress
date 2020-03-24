<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


global $posts_revision_do;
$posts_revision_do = pbdb_data_object("posts_revision", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'post_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'posts',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => true, "comment" => "글ID"),

	'post_html'		 => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "글내용"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
),"글 - 리비젼");


function pb_post_revision_statement($conditions_ = array()){
	global $posts_revision_do, $posts_do;

	$statement_ = $posts_revision_do->statement();

	$statement_->add_field(
		"DATE_FORMAT(posts_revision.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(posts_revision.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(posts_revision.reg_date, '%Y.%m.%d') reg_date_ymd"
	);

	$statement_->add_join_statement('LEFT OUTER JOIN', $posts_do->statement(), 'posts', array(
		array(PBDB_SS::COND_COMPARE, "posts_revision.post_id", "posts.id", "=")
	), array(
		"posts.type post_type"
	));

	$statement_->add_legacy_field_filter('pb_post_revision_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_post_revision_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_post_revision_list_where', '', $conditions_);

	if(isset($conditions_['id'])){
		$statement_->add_in_condition("posts_revision.id", $conditions_['id']);
	}
	if(isset($conditions_['post_id'])){
		$statement_->add_in_condition("posts_revision.post_id", $conditions_['post_id']);
	}
	
	return pb_hook_apply_filters('pb_post_revision_statement', $statement_, $conditions_);
}
function pb_post_revision_list($conditions_ = array()){
	$statement_ = pb_post_revision_statement($conditions_);

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	return pb_hook_apply_filters('pb_post_revision_list', $statement_->select($orderby_, $limit_));
}

function pb_post_revision($id_){
	$data_ = pb_post_revision_list(array("id" => $id_));
	if(count($data_) > 0) return $data_[0];
	return null;
}

function pb_post_revision_insert($raw_data_){
	global $posts_revision_do;
	$insert_id_ = $posts_revision_do->insert($raw_data_);
	pb_hook_do_action("pb_post_revision_inserted", $insert_id_);
	return $insert_id_;
}

function pb_post_revision_update($id_, $raw_data_){
	global $posts_revision_do;

	$result_ = $posts_revision_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_post_revision_updated", $id_);

	return $result_;
}

function pb_post_revision_delete($id_){
	global $posts_revision_do;
	pb_hook_do_action("pb_post_revision_delete", $id_);
	$posts_revision_do->delete($id_);
	pb_hook_do_action("pb_post_revision_deleted", $id_);
}

function pb_post_revision_write_from($post_id_){
	$post_data_ = pb_post($post_id_);
	if(!isset($post_data_)){
		return new PBError(-1, "잘못된 요청", "글정보가 존재하지 않습니다.");
	}

	pb_post_revision_insert(array(
		'post_id' => $post_id_,
		'post_html' => $post_data_['post_html'],
		'reg_date' => pb_current_time(),
	));
}

function pb_post_restore_from_revision($post_id_, $revision_id_){
	$post_data_ = pb_post($post_id_);
	if(!isset($post_data_)){
		return new PBError(-1, "잘못된 요청", "글정보가 존재하지 않습니다.");
	}

	$revision_data_ = pb_post_revision($revision_id_);

	if(!isset($revision_data_)){
		return new PBError(-1, "잘못된 요청", "리비젼정보가 존재하지 않습니다.");
	}	

	pb_post_revision_write_from($post_id_);

	global $posts_do;

	$posts_do->update($post_id_, array("post_html" => $revision_data_['post_html']));
	pb_hook_do_action('pb_post_restored_from_revision', $post_id_, $revision_id_);
}

include(PB_DOCUMENT_PATH . 'includes/post/post-revision-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/post/post-revision-adminpage.php');

?>