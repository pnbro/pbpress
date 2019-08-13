<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_menu_item_meta_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT 

					 menus_item_meta.id id
					,menus_item_meta.menu_item_id menu_item_id

					,menus_item_meta.meta_name meta_name
					,menus_item_meta.meta_value meta_value

					,DATE_FORMAT(menus_item_meta.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
					,DATE_FORMAT(menus_item_meta.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis

					 ".pb_hook_apply_filters('pb_menu_item_meta_list_fields', "", $conditions_)." 
	FROM menus_item_meta


	".pb_hook_apply_filters('pb_menu_item_meta_list_join', "", $conditions_)." 

	WHERE 1 

	 ".pb_hook_apply_filters('pb_menu_item_meta_list_where', "", $conditions_)."  

	";

	if(isset($conditions_['id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['id'], "menus_item_meta.id")." ";
	}
	if(isset($conditions_['menu_item_id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['menu_item_id'], "menus_item_meta.menu_item_id")." ";
	}
	if(isset($conditions_['meta_name'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['meta_name'], "menus_item_meta.meta_name")." ";
	}
	if(isset($conditions_['meta_value'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['meta_value'], "menus_item_meta.meta_value")." ";
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

	return $pbdb->select($query_);
}

function pb_menu_item_meta_data($id_){
	$data_ = pb_menu_item_meta_list(array("id" => $id_));
	if(count($data_) > 0) return $data_[0];
	return null;
}
function pb_menu_item_meta_data_by($menu_item_id_, $meta_name_){
	$data_ = pb_menu_item_meta_list(array("menu_item_id" => $menu_item_id_, "meta_name" => $meta_name_));
	if(count($data_) > 0) return $data_[0];
	return null;
}

function pb_menu_item_meta_map($menu_item_id_, $cache_ = true){
	global $_pb_menu_item_meta_map;

	if($cache_ && isset($_pb_menu_item_meta_map) && isset($_pb_menu_item_meta_map[$menu_item_id_])){
		return $_pb_menu_item_meta_map[$menu_item_id_];
	}

	$temp_ = pb_menu_item_meta_list(array(
		"menu_item_id" => $menu_item_id_,
	));

	$results_ = array();

	foreach($temp_ as $row_data_){
		if(!isset($results_[$row_data_['meta_name']])) $results_[$row_data_['meta_name']] = $row_data_['meta_value'];
		else{
			if(gettype($results_[$row_data_['meta_name']]) !== "array"){
				$results_[$row_data_['meta_name']] = array($results_[$row_data_['meta_name']]);
			}

			$results_[$row_data_['meta_name']][] = $row_data_['meta_value'];
		}
	}

	$_pb_menu_item_meta_map[$menu_item_id_] = $results_;

	return $results_;
}

function pb_menu_item_meta_value($menu_item_id_, $meta_name_, $default_ = null, $cache_ = true){
	$meta_data_ = pb_menu_item_meta_map($menu_item_id_, $cache_);
	if(count($meta_data_) <= 0 || !isset($meta_data_[$meta_name_])) return $default_;
	return $meta_data_[$meta_name_];
}

function pb_menu_item_meta_update($menu_item_id_, $meta_name_, $meta_value_, $unique_ = true){
	global $pbdb;

	$meta_data_ = pb_menu_item_meta_data_by($menu_item_id_, $meta_name_);

	if(!$unique_ || !isset($meta_data_)){
		return $pbdb->insert("menus_item_meta", array(
			'menu_item_id' => $menu_item_id_,
			'meta_name' => $meta_name_,
			'meta_value' => $meta_value_,
			'reg_date' => pb_current_time(),
		));
	}else{
		$pbdb->update("menus_item_meta", array(
			'meta_value' => $meta_value_,
			'mod_date' => pb_current_time(),
		), array("id" => $meta_data_['id']));

		return $meta_data_['id'];
	}
}

?>