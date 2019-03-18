<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_option_list($conditions_ = array()){
	global $pbdb;

	$query_ = "
	SELECT   OPTIONS.ID ID
			,OPTIONS.OPTION_NAME OPTION_NAME
			,OPTIONS.OPTION_VALUE OPTION_VALUE
			
			,OPTIONS.REG_DATE REG_DATE
			,DATE_FORMAT(OPTIONS.REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS
			,DATE_FORMAT(OPTIONS.REG_DATE, '%Y.%m.%d') REG_DATE_YMD

			,OPTIONS.MOD_DATE MOD_DATE
			,DATE_FORMAT(OPTIONS.MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
			,DATE_FORMAT(OPTIONS.MOD_DATE, '%Y.%m.%d') MOD_DATE_YMD

			".pb_hook_apply_filters('pb_option_list_fields', "", $conditions_)."
		
	FROM OPTIONS

	".pb_hook_apply_filters('pb_option_list_join', "", $conditions_)."

	WHERE 1=1 
	
	".pb_hook_apply_filters('pb_option_list_where', "", $conditions_)."

	";

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND ".pb_query_keyword_search(array(
			'OPTIONS.OPTION_NAME',
		), $conditions_['keyword'])." ";
	}
	if(isset($conditions_['ID']) && strlen($conditions_['ID'])){
		$query_ .= " AND OPTIONS.ID = '".mysql_real_escape_string($conditions_['ID'])."' ";
	}

	if(isset($conditions_['option_name']) && strlen($conditions_['option_name'])){
		$query_ .= " AND OPTIONS.OPTION_NAME = '".mysql_real_escape_string($conditions_['option_name'])."' ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

	$query_ .= " ORDER BY REG_DATE DESC";

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }
    
	return $pbdb->select($query_);
}

function pb_option_value($option_name_, $default_ = null, $cache_ = true){
	global $_pb_option_map;

	if($cache_ && isset($_pb_option_map) && isset($_pb_option_map[$option_name_])){
		return $_pb_option_map[$option_name_];
	}

	$temp_ = pb_option_list(array(
		"option_name" => $option_name_,
	));

	$results_ = null;

	foreach($temp_ as $row_data_){
		$results_ = $row_data_['OPTION_VALUE'];
		break;
	}

	if(!isset($results_)){
		return null;
	}

	$_pb_option_map[$option_name_] = unserialize($results_);

	return $_pb_option_map[$option_name_];
}

function pb_option_update($option_name_, $option_value_){
	global $pbdb;

	if($option_value_ === null) return;

	$before_value_ = pb_option_value($option_name_);
	$option_value_ = serialize($option_value_);

	if(strlen($before_value_)){
		$update_data_ = array(
			'OPTION_VALUE' => $option_value_,
			'MOD_DATE' => pb_current_time(),
		);

		$pbdb->update("OPTIONS", $update_data_, array(
			'OPTION_NAME' => $option_name_,
		));
	}else{
		$insert_data_ = array(
			'OPTION_NAME' => $option_name_,
			'OPTION_VALUE' => $option_value_,
			'REG_DATE' => pb_current_time(),
		);

		$pbdb->insert("OPTIONS", $insert_data_);
	}

	global $_pb_option_map;

	if(isset($_pb_option_map) && isset($_pb_option_map[$option_name_])){
		unset($_pb_option_map[$option_name_]);
	}
	pb_hook_do_action('pb_option_updated', $option_name_);
}

include(PB_DOCUMENT_PATH . 'includes/common/option-builtin.php');

?>