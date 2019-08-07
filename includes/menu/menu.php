<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_menu_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT 

					 menus.id id
						
					,menus.title title
					,menus.slug slug
					,menus.menu_html menu_html

					,menus.use_yn use_yn
					
					,menus.reg_date reg_date
					,DATE_FORMAT(menus.reg_date, '%Y.%m.%d') reg_date_ymd
					,DATE_FORMAT(menus.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi
					,DATE_FORMAT(menus.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
					
					,menus.mod_date mod_date
					,DATE_FORMAT(menus.mod_date, '%Y.%m.%d') mod_date_ymd
					,DATE_FORMAT(menus.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi
					,DATE_FORMAT(menus.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis

					 ".pb_hook_apply_filters('pb_menu_list_fields', "", $conditions_)." 
	FROM menus

	".pb_hook_apply_filters('pb_menu_list_join', "", $conditions_)." 

	WHERE 1 

	 ".pb_hook_apply_filters('pb_menu_list_where', "", $conditions_)."  

	";

	if(isset($conditions_['id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['id'], "menus.id")." ";
	}
	if(isset($conditions_['slug'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['slug'], "menus.slug")." ";
	}
	if(isset($conditions_['use_yn'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['use_yn'], "menus.use_yn")." ";
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND ".pb_query_keyword_search(pb_hook_apply_filters('pb_menu_list_keyword', array(
			"menus.title",
			"menus.slug",
		)), $conditions_['keyword'])." ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $pbdb->get_var("SELECT COUNT(*) FROM (".$query_.") TEMP");
	}

	if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
		$query_ .= " ".$conditions_['orderby']." ";
	}else{
		$query_ .= " ORDER BY id DESC ";
	}

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters("pb_menu_list", $pbdb->select($query_));
}

function pb_menu($id_){
	$menu_ = pb_menu_list(array("id" => $id_));
	if(!isset($menu_) || count($menu_) <= 0) return null;
	return $menu_[0];
}

function pb_menu_by_slug($slug_){
	$menu_ = pb_menu_list(array("slug" => $slug_));
	if(!isset($menu_) || count($menu_) <= 0) return null;
	return $menu_[0];
}

function _pb_menu_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_menu_parse_fields",array(

		'title' => '%s',
		'slug' => '%s',
		'use_yn' => '%s',

		'reg_date' => '%s',
		'mod_date' => '%s',
		
	)), $data_);
}


function pb_menu_insert($raw_data_){
	global $pbdb;

	$raw_data_ = _pb_menu_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("menus", $data_, $format_);
	pb_hook_do_action("pb_menu_inserted", pb_menu($insert_id_));
	return $insert_id_;
}

function pb_menu_update($id_, $raw_data_){
	global $pbdb;

	$raw_data_ = _pb_menu_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$result_ = $pbdb->update("menus", $data_, array("id" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_menu_updated", pb_menu($id_));

	return $result_;
}

function pb_menu_delete($id_){
	global $pbdb;

	$result_ = $pbdb->delete("menus", array("id" => $id_), array("%d"));
	pb_hook_do_action("pb_menu_deleted", pb_menu($id_));
	return $result_;
}

include(PB_DOCUMENT_PATH . 'includes/menu/menu-dtl.php');
include(PB_DOCUMENT_PATH . 'includes/menu/menu-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/menu/menu-adminpage.php');

?>