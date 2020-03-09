<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $users_auth_do;
$users_auth_do = pbdb_data_object("users_auth", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'user_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'users',
		'column' => "id",
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), "comment" => "사용자ID"),
	'auth_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'auth',
		'column' => "id",
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), "comment" => "권한ID"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"사용자");

$users_auth_do->add_legacy_field_filter("pb_user_authority_parse_fields"); // for legacy

function pb_user_authority_list($conditions_ = array()){
	global $auth_do, $users_auth_do;

	$statement_ = $users_auth_do->statement();
	$statement_->add_field(
		"DATE_FORMAT(users_auth.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(users_auth.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(users_auth.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(users_auth.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(users_auth.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(users_auth.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$auth_join_cond_ = pbdb_ss_conditions();
	$auth_join_cond_->add_compare("auth.id", "users_auth.auth_id", "=");
	$statement_->add_join_statement("LEFT OUTER JOIN",$auth_do->statement(), "auth", $auth_join_cond_, array(
		'auth_name',
		'slug auth_slug',
	));

	$statement_->add_legacy_field_filter('pb_user_authority_list_select', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_user_authority_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_user_authority_list_where', '', $conditions_);

	if(isset($conditions_['id']) && $conditions_['id'] === true){
		$statement_->add_compare_condition("users_auth.id", $conditions_['id'], "=", PBDB::TYPE_NUMBER);
	}
	if(isset($conditions_['auth_id'])){
		$statement_->add_compare_condition("users_auth.auth_id", $conditions_['auth_id'], "=", PBDB::TYPE_NUMBER);
	}
	if(isset($conditions_['auth_slug'])){
		$statement_->add_compare_condition("auth.slug", $conditions_['auth_slug'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['auth_task_slug'])){
		$statement_->add_custom_condition("users_auth.auth_id IN (
			SELECT auth_task.auth_id
			FROM   auth_task
			WHERE  ".pb_query_in_fields($conditions_['auth_task_slug'], "auth_task.slug")."
		)");
	}

	if(isset($conditions_['user_id']) && strlen($conditions_['user_id'])){
		$statement_->add_compare_condition("users_auth.user_id", $conditions_['user_id'], "=", PBDB::TYPE_NUMBER);
	}
	if(isset($conditions_['user_login']) && strlen($conditions_['user_login'])){
		$statement_->add_compare_condition("users.user_login", $conditions_['user_login'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['user_email']) && strlen($conditions_['user_email'])){
		$statement_->add_compare_condition("users.user_email", $conditions_['user_email'], "=", PBDB::TYPE_STRING);
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        return $statement_->count();
    }

    $orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
    $limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	return pb_hook_apply_filters('pb_user_authority_list', $statement_->select($orderby_, $limit_));
}

function pb_user_authority($id_){
	$data_ = pb_user_authority_list(array("id" => $id_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}
function pb_user_authority_by_slug($user_id_, $slug_){
	$data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_slug' => $slug_
	));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}


function pb_user_authority_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_authority_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $users_auth_do;
	$inserted_id_ = $users_auth_do->insert($raw_data_);
	pb_hook_do_action("pb_user_authority_added", $inserted_id_);

	return $inserted_id_;
}
function pb_user_authority_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_authority_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $users_auth_do;
	$users_auth_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_user_updated", $id_);
}

function pb_user_authority_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $users_auth_do;
	pb_hook_do_action("pb_user_delete", $id_);
	$users_auth_do->delete($id_);
	pb_hook_do_action("pb_user_deleted", $id_);
}

function pb_user_has_authority($user_id_, $authority_){
	$auth_data_ = pb_authority_by_slug($authority_);
	if(!isset($auth_data_)) return false;

	$user_auth_data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_id' => $auth_data_['id'],
	));

	return pb_hook_apply_filters("pb_user_has_authority", count($user_auth_data_) > 0, $user_id_, $authority_);
}

function pb_user_has_authority_task($user_id_, $authority_task_){
	$user_auth_data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_task_slug' => $authority_task_,
	));

	return pb_hook_apply_filters("pb_user_has_authority_task", count($user_auth_data_) > 0, $user_id_, $authority_task_);
}

function pb_user_grant_authority($user_id_, $authority_){
	$auth_data_ = pb_authority_by_slug($authority_);
	if(!isset($auth_data_)) return null;

	$user_auth_data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_id' => $auth_data_['id'],
	));

	if(count($user_auth_data_) > 0) return null;

	$inserted_id_ = pb_user_authority_add(array(
		'user_id' => $user_id_,
		'auth_id' => $auth_data_['id'],
		'reg_date' => pb_current_time(),
	));
	return $inserted_id_;
}

function pb_user_revoke_authority($user_id_, $authority_){
	$auth_data_ = pb_user_authority_by_slug($user_id_, $authority_);
	if(!isset($auth_data_)) return null;

	pb_user_authority_delete($auth_data_["id"]);
}

include(PB_DOCUMENT_PATH . 'includes/user/user-authority-builtin.php');

?>