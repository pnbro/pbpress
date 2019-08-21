<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_authority_task_types(){
	global $_pb_authority_task_types;
	if(!isset($_pb_authority_task_types)){
		$_pb_authority_task_types = pb_hook_apply_filters("pb_authority_task_types", array(
			'access_adminpage' => array(
				'name' => '관리자페이지접근',
				'selectable' => false,
			),
			'manage_site' => array(
				'name' => '사이트관리',
				'selectable' => false,
			),
		));
	}
	return $_pb_authority_task_types;
}

function pb_authority_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
		SELECT   auth.id id

				,auth.slug slug
				,auth.auth_name auth_name
				,auth.auth_desc auth_desc
				
				,auth.reg_date reg_date
				,DATE_FORMAT(auth.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
				,DATE_FORMAT(auth.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi
				,DATE_FORMAT(auth.reg_date, '%Y.%m.%d') reg_date_ymd

				,auth.mod_date mod_date
				,DATE_FORMAT(auth.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis
				,DATE_FORMAT(auth.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi
				,DATE_FORMAT(auth.mod_date, '%Y.%m.%d') mod_date_ymd

				".pb_hook_apply_filters('pb_authority_list_select',"",$conditions_)."

	FROM auth

	".pb_hook_apply_filters('pb_authority_list_join',"",$conditions_)."
	
	WHERE 1 ";

	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$query_ .= " AND auth.id = '".pb_database_escape_string($conditions_['id'])."' ";
	}
	if(isset($conditions_['slug']) && strlen($conditions_['slug'])){
		$query_ .= " AND auth.slug = '".pb_database_escape_string($conditions_['slug'])."' ";
	}
	if(isset($conditions_['auth_name']) && strlen($conditions_['auth_name'])){
		$query_ .= " AND auth.auth_name = '".pb_database_escape_string($conditions_['auth_name'])."' ";
	}

	$query_ .= ' '.pb_hook_apply_filters('pb_authority_list_where',"",$conditions_)." ";

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

    if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
        $query_ .= " ".$conditions_['orderby']." ";
    }else{
    	$query_ .= " ORDER BY reg_date DESC";
    }

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters('pb_authority_list', $pbdb->select($query_));
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

function _pb_authority_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_authority_parse_fields",array(

		'slug' => '%s',
		'auth_name' => '%s',
		'auth_desc' => '%s',
		'reg_date' => '%s',
		'mod_date' => '%s',

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

	$insert_id_ = $pbdb->insert("auth", $data_, $format_);
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

	$pbdb->update("auth", $data_, array("id" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_authority_updated", $id_);
}

function pb_authority_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_authority_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;
	$pbdb->delete("auth", array("id" => $id_), array("%d"));
	pb_hook_do_action("pb_authority_deleted", $id_);
}

function pb_authority_task_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
		SELECT   auth_task.id id
	
				,auth.slug auth_slug
				,auth.auth_name auth_name

				,auth_task.slug slug

				,auth_task.auth_id auth_id
				
				,auth_task.reg_date reg_date
				,DATE_FORMAT(auth_task.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
				,DATE_FORMAT(auth_task.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi
				,DATE_FORMAT(auth_task.reg_date, '%Y.%m.%d') reg_date_ymd

				,auth_task.mod_date mod_date
				,DATE_FORMAT(auth_task.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis
				,DATE_FORMAT(auth_task.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi
				,DATE_FORMAT(auth_task.mod_date, '%Y.%m.%d') mod_date_ymd

				".pb_hook_apply_filters('pb_authority_task_list_select',"",$conditions_)."

	FROM auth_task

	LEFT OUTER JOIN auth
	ON   auth.id = auth_task.auth_id

	".pb_hook_apply_filters('pb_authority_task_list_join',"",$conditions_)."
	
	WHERE 1 ";

	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$query_ .= " AND auth_task.id = '".pb_database_escape_string($conditions_['id'])."' ";
	}
	if(isset($conditions_['slug']) && strlen($conditions_['slug'])){
		$query_ .= " AND auth_task.slug = '".pb_database_escape_string($conditions_['slug'])."' ";
	}
	if(isset($conditions_['auth_slug']) && strlen($conditions_['auth_slug'])){
		$query_ .= " AND auth.slug = '".pb_database_escape_string($conditions_['auth_slug'])."' ";
	}
	if(isset($conditions_['auth_id']) && strlen($conditions_['auth_id'])){
		$query_ .= " AND auth_task.auth_id = '".pb_database_escape_string($conditions_['auth_id'])."' ";
	}

	$query_ .= ' '.pb_hook_apply_filters('pb_authority_task_list_where',"",$conditions_)." ";

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

    if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
        $query_ .= " ".$conditions_['orderby']." ";
    }else{
    	$query_ .= " ORDER BY reg_date DESC";
    }

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters('pb_authority_task_list', $pbdb->select($query_));
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

function _pb_authority_task_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_authority_task_parse_fields",array(

		'auth_id' => '%d',
		'slug' => '%s',
		'reg_date' => '%s',
		'mod_date' => '%s',

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

	$insert_id_ = $pbdb->insert("auth_task", $data_, $format_);
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

	$pbdb->update("auth_task", $data_, array("id" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_authority_task_updated", $id_);
}

function pb_authority_task_delete($id_){
	$before_check_ = pb_hook_apply_filters("pb_authority_task_before_delete", $id_);

	if(pb_is_error($before_check_)){
		return $before_check_;
	}

	global $pbdb;
	$pbdb->delete("auth_task", array("id" => $id_), array("%d"));
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

include(PB_DOCUMENT_PATH . 'includes/authority/authority-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/authority/authority-adminpage.php');

?>