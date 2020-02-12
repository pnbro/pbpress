<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_clob_options_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
	SELECT   clob_options.id id
			,clob_options.option_name option_name
			,clob_options.option_value option_value
			
			,clob_options.reg_date reg_date
			,DATE_FORMAT(clob_options.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
			,DATE_FORMAT(clob_options.reg_date, '%Y.%m.%d') reg_date_ymd

			,clob_options.mod_date mod_date
			,DATE_FORMAT(clob_options.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis
			,DATE_FORMAT(clob_options.mod_date, '%Y.%m.%d') mod_date_ymd

			".pb_hook_apply_filters('pb_clob_options_list_fields', "", $conditions_)."
		
	FROM clob_options

	".pb_hook_apply_filters('pb_clob_options_list_join', "", $conditions_)."

	WHERE 1=1 
	
	".pb_hook_apply_filters('pb_clob_options_list_where', "", $conditions_)."

	";

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND ".pb_query_keyword_search(array(
			'clob_options.option_name',
		), $conditions_['keyword'])." ";
	}
	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$query_ .= " AND clob_options.id = '".pb_database_escape_string($conditions_['id'])."' ";
	}

	if(isset($conditions_['option_name']) && strlen($conditions_['option_name'])){
		$query_ .= " AND clob_options.option_name = '".pb_database_escape_string($conditions_['option_name'])."' ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

	$query_ .= " ORDER BY reg_date DESC";

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }
    
	return $pbdb->select($query_);
}

function pb_clob_option_value($option_name_, $default_ = null, $cache_ = true){
	global $_pb_clob_options_map;

	if($cache_ && isset($_pb_clob_options_map) && isset($_pb_clob_options_map[$option_name_])){
		return $_pb_clob_options_map[$option_name_];
	}

	$temp_ = pb_clob_options_list(array(
		"option_name" => $option_name_,
	));

	$results_ = null;

	foreach($temp_ as $row_data_){
		$results_ = $row_data_['option_value'];
		break;
	}

	if(!isset($results_)){
		return $default_;
	}

	$_pb_clob_options_map[$option_name_] = unserialize($results_);

	return $_pb_clob_options_map[$option_name_];
}

//Deprecated
function pb_clob_options_value($option_name_, $default_ = null, $cache_ = true){
	return pb_clob_option_value($option_name_, $default_, $cache_);
}

function pb_clob_option_update($option_name_, $option_value_){
	global $pbdb;

	$before_value_ = pb_clob_option_value($option_name_);

	if($option_value_ === null){
		$update_data_ = array(
			'option_value' => null,
			'mod_date' => pb_current_time(),
		);

		$pbdb->update("clob_options", $update_data_, array(
			'option_name' => $option_name_,
		));

	}else{
		$option_value_ = serialize($option_value_);

		if($before_value_ !== null){
			$update_data_ = array(
				'option_value' => $option_value_,
				'mod_date' => pb_current_time(),
			);

			$pbdb->update("clob_options", $update_data_, array(
				'option_name' => $option_name_,
			));
		}else{
			$insert_data_ = array(
				'option_name' => $option_name_,
				'option_value' => $option_value_,
				'reg_date' => pb_current_time(),
			);

			$pbdb->insert("clob_options", $insert_data_);
		}	
	}


	global $_pb_clob_options_map;

	if(isset($_pb_clob_options_map) && isset($_pb_clob_options_map[$option_name_])){
		unset($_pb_clob_options_map[$option_name_]);
	}
	pb_hook_do_action('pb_clob_option_updated', $option_name_);
}
//Deprecated
function pb_clob_options_update($option_name_, $option_value_){
	return pb_clob_option_update($option_name_, $option_value_);
}

include(PB_DOCUMENT_PATH . 'includes/common/clob-options-builtin.php');

?>