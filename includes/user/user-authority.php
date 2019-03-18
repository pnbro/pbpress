<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_user_authority_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
		SELECT   USERS_AUTH.ID ID
	
				,USERS_AUTH.USER_ID USER_ID
				,USERS_AUTH.AUTH_ID AUTH_ID

				,AUTH.AUTH_NAME AUTH_NAME
				,AUTH.SLUG AUTH_SLUG
				
				,USERS_AUTH.REG_DATE REG_DATE
				,DATE_FORMAT(USERS_AUTH.REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS
				,DATE_FORMAT(USERS_AUTH.REG_DATE, '%Y.%m.%d %H:%i') REG_DATE_YMDHI
				,DATE_FORMAT(USERS_AUTH.REG_DATE, '%Y.%m.%d') REG_DATE_YMD

				,USERS_AUTH.MOD_DATE MOD_DATE
				,DATE_FORMAT(USERS_AUTH.MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
				,DATE_FORMAT(USERS_AUTH.MOD_DATE, '%Y.%m.%d %H:%i') MOD_DATE_YMDHI
				,DATE_FORMAT(USERS_AUTH.MOD_DATE, '%Y.%m.%d') MOD_DATE_YMD

				".pb_hook_apply_filters('pb_user_authority_list_select',"",$conditions_)."
	
	FROM USERS_AUTH

	LEFT OUTER JOIN AUTH
	ON   AUTH.ID = USERS_AUTH.AUTH_ID

	LEFT OUTER JOIN USERS
	ON   USERS.ID = USERS_AUTH.USER_ID

	".pb_hook_apply_filters('pb_user_authority_list_join',"",$conditions_)."
	
	WHERE 1 ";

	if(isset($conditions_['ID']) && $conditions_['ID'] === true){
		$query_ .= " AND USERS_AUTH.ID = '".mysql_real_escape_string($conditions_['ID'])."' ";
	}
	if(isset($conditions_['auth_id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['auth_id'], "USERS_AUTH.AUTH_ID")." ";
	}
	if(isset($conditions_['auth_slug'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['auth_slug'], "AUTH.SLUG")." ";
	}
	if(isset($conditions_['auth_task_slug'])){
		$query_ .= " AND USERS_AUTH.AUTH_ID IN (
			SELECT AUTH_TASK.AUTH_ID
			FROM   AUTH_TASK
			WHERE  ".pb_query_in_fields($conditions_['auth_task_slug'], "AUTH_TASK.SLUG")."
		) ";
	}

	if(isset($conditions_['user_id']) && strlen($conditions_['user_id'])){
		$query_ .= " AND USERS_AUTH.USER_ID = '".mysql_real_escape_string($conditions_['user_id'])."' ";
	}
	if(isset($conditions_['user_login']) && strlen($conditions_['user_login'])){
		$query_ .= " AND USERS.USER_LOGIN = '".mysql_real_escape_string($conditions_['user_login'])."' ";
	}
	if(isset($conditions_['user_email']) && strlen($conditions_['user_email'])){
		$query_ .= " AND USERS.USER_EMAIL = '".mysql_real_escape_string($conditions_['user_email'])."' ";
	}

	$query_ .= ' '.pb_hook_apply_filters('pb_user_authority_list_where',"",$conditions_)." ";

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

    if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
        $query_ .= " ".$conditions_['orderby']." ";
    }else{
    	$query_ .= " ORDER BY REG_DATE DESC";
    }

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters('pb_user_authority_list', $pbdb->select($query_));
}

function pb_user_authority($id_){
	$data_ = pb_user_authority_list(array("ID" => $id_));
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

function _pb_user_authority_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_user_authority_parse_fields",array(

		'USER_ID' => '%d',
		'AUTH_ID' => '%s',
		'REG_DATE' => '%s',
		'MOD_DATE' => '%s',
		
	)), $data_);
}

function pb_user_authority_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_authority_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_user_authority_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("USERS_AUTH", $data_, $format_);
	pb_hook_do_action("pb_user_authority_added", $insert_id_);

	return $insert_id_;
}
function pb_user_authority_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_user_authority_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_user_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$pbdb->update("USERS_AUTH", $data_, array("ID" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_user_updated", $id_);
}

function pb_user_authority_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_user_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;
	$pbdb->delete("USERS_AUTH", array("ID" => $id_), array("%d"));
	pb_hook_do_action("pb_user_deleted", $id_);
}

function pb_user_has_authority($user_id_, $authority_){
	$auth_data_ = pb_authority_by_slug($authority_);
	if(!isset($auth_data_)) return false;

	$user_auth_data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_id' => $auth_data_['ID'],
	));

	return count($user_auth_data_) > 0;
}

function pb_user_has_authority_task($user_id_, $authority_task_){
	$user_auth_data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_task_slug' => $authority_task_,
	));

	return (count($user_auth_data_) > 0);
}

function pb_user_grant_authority($user_id_, $authority_){
	$auth_data_ = pb_authority_by_slug($authority_);
	if(!isset($auth_data_)) return null;

	$user_auth_data_ = pb_user_authority_list(array(
		'user_id' => $user_id_,
		'auth_id' => $auth_data_['ID'],
	));

	if(count($user_auth_data_) > 0) return null;

	$inserted_id_ = pb_user_authority_add(array(
		'USER_ID' => $user_id_,
		'AUTH_ID' => $auth_data_['ID'],
		'REG_DATE' => pb_current_time(),
	));
	return $inserted_id_;
}

function pb_user_revoke_authority($user_id_, $authority_){
	$auth_data_ = pb_user_authority_by_slug($user_id_, $authority_);
	if(!isset($auth_data_)) return null;

	pb_user_authority_delete($auth_data_["ID"]);
}

include(PB_DOCUMENT_PATH . 'includes/user/user-authority-builtin.php');

?>