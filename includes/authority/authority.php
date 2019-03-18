<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_authority_task_types(){
	return pb_hook_apply_filters("pb_authority_task_types", array(
		'access_adminpage' => array(
			'name' => '관리자페이지접근',
		),
		'manage_site' => array(
			'name' => '사이트관리',
		),
	));
}

function pb_authority_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
		SELECT   AUTH.ID ID

				,AUTH.SLUG SLUG
				,AUTH.AUTH_NAME AUTH_NAME
				,AUTH.AUTH_DESC AUTH_DESC
				
				,AUTH.REG_DATE REG_DATE
				,DATE_FORMAT(AUTH.REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS
				,DATE_FORMAT(AUTH.REG_DATE, '%Y.%m.%d %H:%i') REG_DATE_YMDHI
				,DATE_FORMAT(AUTH.REG_DATE, '%Y.%m.%d') REG_DATE_YMD

				,AUTH.MOD_DATE MOD_DATE
				,DATE_FORMAT(AUTH.MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
				,DATE_FORMAT(AUTH.MOD_DATE, '%Y.%m.%d %H:%i') MOD_DATE_YMDHI
				,DATE_FORMAT(AUTH.MOD_DATE, '%Y.%m.%d') MOD_DATE_YMD

				".pb_hook_apply_filters('pb_authority_list_select',"",$conditions_)."

	FROM AUTH

	".pb_hook_apply_filters('pb_authority_list_join',"",$conditions_)."
	
	WHERE 1 ";

	if(isset($conditions_['ID']) && strlen($conditions_['ID'])){
		$query_ .= " AND AUTH.ID = '".mysql_real_escape_string($conditions_['ID'])."' ";
	}
	if(isset($conditions_['slug']) && strlen($conditions_['slug'])){
		$query_ .= " AND AUTH.SLUG = '".mysql_real_escape_string($conditions_['slug'])."' ";
	}
	if(isset($conditions_['auth_name']) && strlen($conditions_['auth_name'])){
		$query_ .= " AND AUTH.AUTH_NAME = '".mysql_real_escape_string($conditions_['auth_name'])."' ";
	}

	$query_ .= ' '.pb_hook_apply_filters('pb_authority_list_where',"",$conditions_)." ";

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

	return pb_hook_apply_filters('pb_authority_list', $pbdb->select($query_));
}

function pb_authority($id_){
	$data_ = pb_authority_list(array("ID" => $id_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}
function pb_authority_by_slug($slug_){
	$data_ = pb_authority_list(array("slug" => $slug_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}

function _pb_authority_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_authority_parse_fields",array(

		'SLUG' => '%s',
		'AUTH_NAME' => '%s',
		'AUTH_DESC' => '%s',
		'REG_DATE' => '%s',
		'MOD_DATE' => '%s',

	)), $data_);
}

function pb_authority_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_authority_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("AUTH", $data_, $format_);
	pb_hook_do_action("pb_authority_added", $insert_id_);

	return $insert_id_;
}

function pb_authority_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_authority_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$pbdb->update("AUTH", $data_, array("ID" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_authority_updated", $id_);
}

function pb_authority_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;
	$pbdb->delete("AUTH", array("ID" => $id_), array("%d"));
	pb_hook_do_action("pb_authority_deleted", $id_);
}

function pb_authority_task_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
		SELECT   AUTH_TASK.ID ID
	
				,AUTH.SLUG AUTH_SLUG
				,AUTH.AUTH_NAME AUTH_NAME

				,AUTH_TASK.SLUG SLUG
				,AUTH_TASK.TASK_NAME TASK_NAME

				,AUTH_TASK.AUTH_ID AUTH_ID
				
				,AUTH_TASK.REG_DATE REG_DATE
				,DATE_FORMAT(AUTH_TASK.REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS
				,DATE_FORMAT(AUTH_TASK.REG_DATE, '%Y.%m.%d %H:%i') REG_DATE_YMDHI
				,DATE_FORMAT(AUTH_TASK.REG_DATE, '%Y.%m.%d') REG_DATE_YMD

				,AUTH_TASK.MOD_DATE MOD_DATE
				,DATE_FORMAT(AUTH_TASK.MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
				,DATE_FORMAT(AUTH_TASK.MOD_DATE, '%Y.%m.%d %H:%i') MOD_DATE_YMDHI
				,DATE_FORMAT(AUTH_TASK.MOD_DATE, '%Y.%m.%d') MOD_DATE_YMD

				".pb_hook_apply_filters('pb_authority_task_list_select',"",$conditions_)."

	FROM AUTH_TASK

	LEFT OUTER JOIN AUTH
	ON   AUTH.ID = AUTH_TASK.AUTH_ID

	".pb_hook_apply_filters('pb_authority_task_list_join',"",$conditions_)."
	
	WHERE 1 ";

	if(isset($conditions_['ID']) && strlen($conditions_['ID'])){
		$query_ .= " AND AUTH_TASK.ID = '".mysql_real_escape_string($conditions_['ID'])."' ";
	}
	if(isset($conditions_['slug']) && strlen($conditions_['slug'])){
		$query_ .= " AND AUTH_TASK.SLUG = '".mysql_real_escape_string($conditions_['slug'])."' ";
	}
	if(isset($conditions_['auth_slug']) && strlen($conditions_['auth_slug'])){
		$query_ .= " AND AUTH.SLUG = '".mysql_real_escape_string($conditions_['auth_slug'])."' ";
	}
	if(isset($conditions_['auth_id']) && strlen($conditions_['auth_id'])){
		$query_ .= " AND AUTH_TASK.AUTH_ID = '".mysql_real_escape_string($conditions_['auth_id'])."' ";
	}

	$query_ .= ' '.pb_hook_apply_filters('pb_authority_task_list_where',"",$conditions_)." ";

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

	return pb_hook_apply_filters('pb_authority_task_list', $pbdb->select($query_));
}


function pb_authority_task($id_){
	$data_ = pb_authority_task_list(array("ID" => $id_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}
function pb_authority_task_by_slug($auth_slug_, $task_slug_){
	$data_ = pb_authority_task_list(array("auth_slug" => $auth_slug_, "slug" => $task_slug_));
	if(!isset($data_) || count($data_) <= 0) return null;
	return $data_[0];
}

function _pb_authority_task_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_authority_task_parse_fields",array(

		'AUTH_ID' => '%d',
		'SLUG' => '%s',
		'TASK_NAME' => '%s',
		'REG_DATE' => '%s',
		'MOD_DATE' => '%s',

	)), $data_);
}

function pb_authority_task_add($raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_add", $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_authority_task_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("AUTH_TASK", $data_, $format_);
	pb_hook_do_action("pb_authority_task_added", $insert_id_);

	return $insert_id_;
}

function pb_authority_task_update($id_, $raw_data_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_update", $id_, $raw_data_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;

	$raw_data_ = _pb_authority_task_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$pbdb->update("AUTH_TASK", $data_, array("ID" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_authority_task_updated", $id_);
}

function pb_authority_task_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;
	$pbdb->delete("AUTH_TASK", array("ID" => $id_), array("%d"));
	pb_hook_do_action("pb_authority_task_deleted", $id_);
}


function pb_authority_map($auth_id_){
	$temp_task_list_ = pb_authority_task_list(array("auth_id" => $auth_id_));
	$task_list_ = array();
	foreach($temp_task_list_ as $task_data_){
		$task_list_[$task_data_['SLUG']] = array(
			'name' => $task_data_['TASK_NAME'],
		);
	}
	return $task_list_;
}

include(PB_DOCUMENT_PATH . 'includes/authority/authority-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/authority/authority-adminpage.php');

?>