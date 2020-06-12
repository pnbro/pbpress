<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_authority_task_types(){
	global $_pb_authority_task_types;
	if(!isset($_pb_authority_task_types)){
		$_pb_authority_task_types = array(
			'access_adminpage' => array(
				'name' => '관리자페이지접근',
				'selectable' => false,
			),
			'manage_site' => array(
				'name' => '사이트관리',
				'selectable' => false,
			),
		);
	}
	return pb_hook_apply_filters("pb_authority_task_types", $_pb_authority_task_types);
}
function pb_authority_task_add_type($task_type_, $data_){
	$pb_authority_task_types_ = pb_authority_task_types();
	$pb_authority_task_types_[$task_type_] = $data_;
	global $_pb_authority_task_types;
	$_pb_authority_task_types = $pb_authority_task_types_;
}

global $auth_do;
$auth_do = pbdb_data_object("auth", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'slug'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "index"=> true,"comment" => "슬러그"),
	'auth_name'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "comment" => "권한명"),
	'auth_desc'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "권한설명"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"권한");

$auth_do->add_legacy_field_filter("pb_authority_parse_fields", array()); // for legacy

function pb_authority_statement($conditions_ = array()){
	global $auth_do;

	$statement_ = $auth_do->statement();
	$statement_->add_field(
		"DATE_FORMAT(auth.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(auth.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(auth.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(auth.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(auth.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(auth.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$statement_->add_compare_condition("auth.id", $conditions_['id'], "=", PBDB::TYPE_NUMBER);
	}
	if(isset($conditions_['slug']) && strlen($conditions_['slug'])){
		$statement_->add_compare_condition("auth.slug", $conditions_['slug'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['auth_name']) && strlen($conditions_['auth_name'])){
		$statement_->add_compare_condition("auth.auth_name", $conditions_['auth_name'], "=", PBDB::TYPE_STRING);
	}

    $statement_->add_legacy_field_filter('pb_authority_list_select', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_authority_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_authority_list_where', '', $conditions_);

	return pb_hook_apply_filters('pb_authority_statement', $statement_, $conditions_);
}

function pb_authority_list($conditions_ = array()){
	$statement_ = pb_authority_statement($conditions_);
	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
    }

	$results_ = $statement_->select($orderby_, $limit_);
	return pb_hook_apply_filters('pb_authority_list', $results_);
}

function pb_authority($id_){
	$data_ = pb_authority_list(array("id" => $id_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}
function pb_authority_by_slug($slug_){
	$data_ = pb_authority_list(array("slug" => $slug_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}

function pb_authority_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $auth_do;
	$insert_id_ = $auth_do->insert($raw_data_);
	pb_hook_do_action("pb_authority_added", $insert_id_);
	return $insert_id_;
}

function pb_authority_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $auth_do;
	$auth_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_authority_updated", $id_);
}

function pb_authority_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $auth_do;
	pb_hook_do_action("pb_authority_delete", $id_);
	$auth_do->delete($id_);
	pb_hook_do_action("pb_authority_deleted", $id_);
}


global $auth_task_do;

$auth_task_do = pbdb_data_object("auth_task", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'auth_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'auth',
		'column' => "id",
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), "nn" => true, "comment" => "권한ID"),

	'slug'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "index"=> true,"comment" => "슬러그"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"권한별 작업범위");

$auth_task_do->add_legacy_field_filter("pb_authority_task_parse_fields", array()); // for legacy

function pb_authority_task_list($conditions_ = array()){
	global $auth_do, $auth_task_do;

	$statement_ = $auth_task_do->statement();

	$statement_->add_field(
		"DATE_FORMAT(auth_task.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(auth_task.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(auth_task.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(auth_task.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(auth_task.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(auth_task.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_join_statement("LEFT OUTER JOIN",$auth_do->statement(), "auth", array(
		array(PBDB_SS::COND_COMPARE, "auth.id", "auth_task.auth_id", "=")
	), array(
		'slug auth_slug',
		'auth_name',
	));

	$statement_->add_legacy_field_filter('pb_authority_task_list_select', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_authority_task_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_authority_task_list_where', '', $conditions_);

	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$statement_->add_compare_condition("auth_task.id", $conditions_['id'], "=", PBDB::TYPE_NUMBER);
	}
	if(isset($conditions_['slug']) && strlen($conditions_['slug'])){
		$statement_->add_compare_condition("auth_task.slug", $conditions_['slug'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['auth_slug']) && strlen($conditions_['auth_slug'])){
		$statement_->add_compare_condition("auth.slug", $conditions_['auth_slug'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['auth_id']) && strlen($conditions_['auth_id'])){
		$statement_->add_compare_condition("auth_task.auth_id", $conditions_['auth_id'], "=", PBDB::TYPE_NUMBER);
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	$results_ = $statement_->select($orderby_, $limit_);

	return pb_hook_apply_filters('pb_authority_task_list', $results_);
}


function pb_authority_task($id_){
	$data_ = pb_authority_task_list(array("id" => $id_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}
function pb_authority_task_by_slug($auth_slug_, $task_slug_){
	$data_ = pb_authority_task_list(array("auth_slug" => $auth_slug_, "slug" => $task_slug_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}

function pb_authority_task_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $auth_task_do;
	$insert_id_ = $auth_task_do->insert($raw_data_);
	pb_hook_do_action("pb_authority_task_added", $insert_id_);

	return $insert_id_;
}

function pb_authority_task_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $auth_task_do;
	$result_ = $auth_task_do->update($id_, $raw_data_);
	global $pbdb;
	pb_hook_do_action("pb_authority_task_updated", $id_);
}

function pb_authority_task_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $auth_task_do;
	$auth_task_do->delete($id_);
	pb_hook_do_action("pb_authority_task_deleted", $id_);
}


function pb_authority_map($auth_id_){
	$authority_task_types_ = pb_authority_task_types();
	$temp_task_list_ = pb_authority_task_list(array("auth_id" => $auth_id_));
	$task_list_ = array();
	foreach($temp_task_list_ as $task_data_){
		$task_list_[$task_data_['slug']] = array(
			'name' => isset($authority_task_types_[$task_data_['slug']]) ? $authority_task_types_[$task_data_['slug']]['name'] : null,
		);
	}
	return $task_list_;
}

global $_pb_authority_initial_list;
$_pb_authority_initial_list = array();
function pb_authority_initial_register($authority_slug_, $task_){
	global $_pb_authority_initial_list;
	if(!isset($_pb_authority_initial_list[$authority_slug_])){
		$_pb_authority_initial_list[$authority_slug_] = array();
	}

	$_pb_authority_initial_list[$authority_slug_][] = $task_;
}

include(PB_DOCUMENT_PATH . 'includes/authority/authority-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/authority/authority-adminpage.php');

?>