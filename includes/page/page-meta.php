<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_meta_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT 

					pages_meta.id id
					,pages_meta.page_id page_id

					,pages_meta.meta_name meta_name
					,pages_meta.meta_value meta_value

					,DATE_FORMAT(pages_meta.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
					,DATE_FORMAT(pages_meta.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis

					 ".pb_hook_apply_filters('pb_page_meta_list_fields', "", $conditions_)." 
	FROM pages_meta


	".pb_hook_apply_filters('pb_page_meta_list_join', "", $conditions_)." 

	WHERE 1 

	 ".pb_hook_apply_filters('pb_page_meta_list_where', "", $conditions_)."  

	";

	if(isset($conditions_['id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['id'], "pages_meta.id")." ";
	}
	if(isset($conditions_['page_id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['page_id'], "pages_meta.page_id")." ";
	}
	if(isset($conditions_['meta_name'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['meta_name'], "pages_meta.meta_name")." ";
	}
	if(isset($conditions_['meta_value'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['meta_value'], "pages_meta.meta_value")." ";
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

function pb_page_meta_data($id_){
	$data_ = pb_page_meta_list(array("id" => $id_));
	if(count($data_) > 0) return $data_[0];
	return null;
}
function pb_page_meta_data_by($page_id_, $meta_name_){
	$data_ = pb_page_meta_list(array("page_id" => $page_id_, "meta_name" => $meta_name_));
	if(count($data_) > 0) return $data_[0];
	return null;
}

function pb_page_meta_map($page_id_, $cache_ = true){
	global $_pb_page_meta_map;

	if($cache_ && isset($_pb_page_meta_map) && isset($_pb_page_meta_map[$page_id_])){
		return $_pb_page_meta_map[$page_id_];
	}

	$temp_ = pb_page_meta_list(array(
		"page_id" => $page_id_,
		// "meta_name" => $meta_name_,
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

	$_pb_page_meta_map[$page_id_] = $results_;

	return $results_;
}

function pb_page_meta_value($page_id_, $meta_name_, $default_ = null, $cache_ = true){
	$meta_data_ = pb_page_meta_map($page_id_, $cache_);
	if(count($meta_data_) <= 0 || !isset($meta_data_[$meta_name_])) return $default_;
	return $meta_data_[$meta_name_];
}

function pb_page_meta_update($page_id_, $meta_name_, $meta_value_, $unique_ = true){
	global $pbdb;

	$meta_data_ = pb_page_meta_data_by($page_id_, $meta_name_);

	if(!$unique_ || !isset($meta_data_)){
		return $pbdb->insert("pages_meta", array(
			'page_id' => $page_id_,
			'meta_name' => $meta_name_,
			'meta_value' => $meta_value_,
			'reg_date' => pb_current_time(),
		));
	}else{
		$pbdb->update("pages_meta", array(
			'meta_value' => $meta_value_,
			'mod_date' => pb_current_time(),
		), array("id" => $meta_data_['id']));

		return $meta_data_['id'];
	}
}

include(PB_DOCUMENT_PATH . 'includes/page/page-meta-builtin.php');

?>