<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

include(PB_DOCUMENT_PATH . "includes/common/fileupload.resource.const.php");

global $file_resources_do;
$file_resources_do = pbdb_data_object("file_resources", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	
	'slug'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, 'nn' => true, 'index' => true, "comment" => "슬러그"),
	'file_title'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 200, "nn" => true, "comment" => "파일명"),
	'file_desc'		 => array("type" => PBDB_DO::TYPE_VARCHAR, 'length' => 200, "comment" => "파일설명"),
	'file_type'		 => array("type" => PBDB_DO::TYPE_VARCHAR, 'length' => 20, "comment" => "파일형식"),

	'display_option'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 5, "nn" => true, "index" => true, "comment" => "파일상태"),
	
	'wrt_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'users',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => false, "comment" => "사용자ID"),
		
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
), __("파일리소스"));

function pb_file_resource_statement($conditions_ = array()){
	global $file_resources_do, $users_do;

	$statement_ = $file_resources_do->statement();
	$statement_->add_field(
		PB_FILE_RESOURCE_OPTION::subquery("file_resources.display_option", 'display_option_name'),
		"DATE_FORMAT(file_resources.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(file_resources.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(file_resources.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(file_resources.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$users_join_cond_ = PBDB_SS_conditions();
	$users_join_cond_->add_compare("users.id", "file_resources.wrt_id");

	$statement_->add_join_statement("LEFT OUTER JOIN", $users_do->statement(), "users", $users_join_cond_, array(
		"user_login wrt_login",
		"user_email wrt_email",
		"user_name wrt_name",
	));

	$statement_->add_conditions_from_data($conditions_, array(
		'id' => array(PBDB_SS::COND_IN, 'file_resources.id'),
		'slug' => array(PBDB_SS::COND_IN, 'file_resources.slug'),
		'display_option' => array(PBDB_SS::COND_IN, 'file_resources.display_option'),
		'wrt_id' => array(PBDB_SS::COND_IN, 'file_resources.wrt_id'),
		'keyword' => array(PBDB_SS::COND_LIKE, array(
			'file_resources.file_title',
		)),
	));

	return pb_hook_apply_filters('pb_file_resource_statement', $statement_, $conditions_);
}

function pb_file_resource_list($conditions_ = array()){
	$statement_ = pb_file_resource_statement($conditions_);

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	return pb_hook_apply_filters("pb_file_resource_list", $statement_->select($orderby_, $limit_));
}

function pb_file_resource_data($id_){
	$file_resource_ = pb_file_resource_list(array("id" => $id_));
	if(!isset($file_resource_) || count($file_resource_) <= 0) return null;
	return $file_resource_[0];
}

function pb_file_resource_data_by($slug_){
	$file_resource_ = pb_file_resource_list(array("slug" => $slug_));
	if(!isset($file_resource_) || count($file_resource_) <= 0) return null;
	return $file_resource_[0];
}

function pb_file_resource_add($raw_data_){
	global $file_resources_do;
	$inserted_id_ = $file_resources_do->insert($raw_data_);
	pb_hook_do_action("pb_file_resource_added", $inserted_id_);

	return $inserted_id_;
}
function pb_file_resource_update($id_, $raw_data_){
	global $file_resources_do;
	$file_resources_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_user_updated", $id_);
}

function pb_file_resource_delete($id_){
	global $file_resources_do;
	pb_hook_do_action("pb_user_delete", $id_);
	$before_data_ = pb_file_resource_data($id_);
	$file_resources_do->delete($id_);
	pb_hook_do_action("pb_user_deleted", $before_data_);
}

include(PB_DOCUMENT_PATH . "includes/common/fileupload.resource.modal.php");
__iinclude(PB_DOCUMENT_PATH . "includes/common/fileupload.resource.adminpage.php");

?>