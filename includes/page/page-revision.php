<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


global $pages_revision_do;
$pages_revision_do = pbdb_data_object("pages_revision", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'page_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'pages',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => true, "comment" => "페이지ID"),

	'page_html'		 => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "페이지내용"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
),"페이지 - 리비젼");


function pb_page_revision_statement($conditions_ = array()){
	global $pages_revision_do;

	$statement_ = $pages_revision_do->statement();

	$statement_->add_field(
		"DATE_FORMAT(pages_revision.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(pages_revision.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(pages_revision.reg_date, '%Y.%m.%d') reg_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_page_revision_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_page_revision_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_page_revision_list_where', '', $conditions_);

	if(isset($conditions_['id'])){
		$statement_->add_in_condition("pages_revision.id", $conditions_['id']);
	}
	if(isset($conditions_['page_id'])){
		$statement_->add_in_condition("pages_revision.page_id", $conditions_['page_id']);
	}
	
	return pb_hook_apply_filters('pb_page_revision_statement', $statement_, $conditions_);
}
function pb_page_revision_list($conditions_ = array()){
	$statement_ = pb_page_revision_statement($conditions_);

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	return pb_hook_apply_filters('pb_page_revision_list', $statement_->select($orderby_, $limit_));
}

function pb_page_revision($id_){
	$data_ = pb_page_revision_list(array("id" => $id_));
	if(count($data_) > 0) return $data_[0];
	return null;
}

function pb_page_revision_insert($raw_data_){
	global $pages_revision_do;
	$insert_id_ = $pages_revision_do->insert($raw_data_);
	pb_hook_do_action("pb_page_revision_inserted", $insert_id_);
	return $insert_id_;
}

function pb_page_revision_update($id_, $raw_data_){
	global $pages_revision_do;

	$result_ = $pages_revision_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_page_revision_updated", $id_);

	return $result_;
}

function pb_page_revision_delete($id_){
	global $pages_revision_do;
	pb_hook_do_action("pb_page_revision_delete", $id_);
	$pages_revision_do->delete($id_);
	pb_hook_do_action("pb_page_revision_deleted", $id_);
}

function pb_page_revision_write_from($page_id_){
	$page_data_ = pb_page($page_id_);
	if(!isset($page_data_)){
		return new PBError(-1, "잘못된 요청", "페이지정보가 존재하지 않습니다.");
	}

	pb_page_revision_insert(array(
		'page_id' => $page_id_,
		'page_html' => $page_data_['page_html'],
		'reg_date' => pb_current_time(),
	));
}

function pb_page_restore_from_revision($page_id_, $revision_id_){
	$page_data_ = pb_page($page_id_);
	if(!isset($page_data_)){
		return new PBError(-1, "잘못된 요청", "페이지정보가 존재하지 않습니다.");
	}

	$revision_data_ = pb_page_revision($revision_id_);

	if(!isset($revision_data_)){
		return new PBError(-1, "잘못된 요청", "리비젼정보가 존재하지 않습니다.");
	}	

	pb_page_revision_write_from($page_id_);

	global $pages_do;

	$pages_do->update($page_id_, array("page_html" => $revision_data_['page_html']));
	pb_hook_do_action('pb_page_restored_from_revision', $page_id_, $revision_id_);
}

include(PB_DOCUMENT_PATH . 'includes/page/page-revision-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-revision-adminpage.php');

?>