<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


function pb_menu_item_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT 

					 menus_item.id id
					,menus_item.parent_id parent_id
					
					,menus_item.menu_id menu_id
					,menus.slug slug
					
					,menus_item.category category
					,menus_item.title title
					,menus_item.sort_char sort_char
					
					,menus_item.reg_date reg_date
					,DATE_FORMAT(menus_item.reg_date, '%Y.%m.%d') reg_date_ymd
					,DATE_FORMAT(menus_item.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi
					,DATE_FORMAT(menus_item.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
					
					,menus_item.mod_date mod_date
					,DATE_FORMAT(menus_item.mod_date, '%Y.%m.%d') mod_date_ymd
					,DATE_FORMAT(menus_item.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi
					,DATE_FORMAT(menus_item.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis

					 ".pb_hook_apply_filters('pb_menu_item_list_fields', "", $conditions_)." 
	FROM menus_item

	LEFT OUTER JOIN menus
	ON   menus.id = menus_item.menu_id

	".pb_hook_apply_filters('pb_menu_item_list_join', "", $conditions_)." 

	WHERE 1 

	 ".pb_hook_apply_filters('pb_menu_item_list_where', "", $conditions_)."  

	";

	if(isset($conditions_['id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['id'], "menus_item.id")." ";
	}

	if(isset($conditions_['parent_id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['parent_id'], "menus_item.parent_id")." ";
	}
	if(isset($conditions_['root_only']) && $conditions_['root_only'] == true){
		$query_ .= " AND menus_item.parent_id IS NULL ";
	}
		
	if(isset($conditions_['category'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['category'], "menus_item.category")." ";
	}
	if(isset($conditions_['menu_id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['menu_id'], "menus_item.menu_id")." ";
	}
	if(isset($conditions_['menu_slug'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['menu_slug'], "menus.slug")." ";
	}

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND ".pb_query_keyword_search(pb_hook_apply_filters('pb_menu_item_list_keyword', array(
			"menus_item.title",
		)), $conditions_['keyword'])." ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $pbdb->get_var("SELECT COUNT(*) FROM (".$query_.") TEMP");
	}

	if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
		$query_ .= " ".$conditions_['orderby']." ";
	}else{
		$query_ .= " ORDER BY sort_char ASC ";
	}

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters("pb_menu_item_list", $pbdb->select($query_));
}

function pb_menu_item($id_){
	$menu_item_ = pb_menu_item_list(array("id" => $id_));
	if(!isset($menu_item_) || count($menu_item_) <= 0) return null;
	return $menu_item_[0];
}

function _pb_menu_item_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_menu_item_parse_fields",array(

		'parent_id' => '%d',
		'menu_id' => '%d',
		'category' => '%s',
		'title' => '%s',
		'sort_char' => '%d',

		'reg_date' => '%s',
		'mod_date' => '%s',
		
	)), $data_);
}


function pb_menu_item_insert($raw_data_){
	global $pbdb;

	$raw_data_ = _pb_menu_item_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("menus_item", $data_, $format_);
	pb_hook_do_action("pb_menu_item_inserted", $insert_id_);
	return $insert_id_;
}

function pb_menu_item_update($id_, $raw_data_){
	global $pbdb;

	$raw_data_ = _pb_menu_item_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$result_ = $pbdb->update("menus_item", $data_, array("id" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_menu_item_updated", $id_);

	return $result_;
}

function pb_menu_item_delete($id_){
	global $pbdb;

	$result_ = $pbdb->delete("menus_item", array("id" => $id_), array("%d"));
	pb_hook_do_action("pb_menu_item_deleted", $id_);
	return $result_;
}

?>