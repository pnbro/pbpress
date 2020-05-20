<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $options_do;
$options_do = pbdb_data_object("options", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'option_name' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "nn" => true, "index" => true, "comment" => "옵션명"),
	'option_value' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "옵션값"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"옵션");

function pb_option_statement($conditions_ = array()){
	global $options_do;

	$statement_ = $options_do->statement();

	$statement_->add_field(
		"DATE_FORMAT(options.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(options.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(options.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(options.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(options.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(options.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_option_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_option_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_option_list_where', '', $conditions_);

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition(array(
			'options.option_name',
		), $conditions_['keyword']);
	}
	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$statement_->add_compare_condition("options.id", $conditions_['id']);
	}

	if(isset($conditions_['option_name']) && strlen($conditions_['option_name'])){
		$statement_->add_compare_condition("options.option_name", $conditions_['option_name'], "=", PBDB::TYPE_STRING);
	}

	return pb_hook_apply_filters('pb_option_statement', $statement_, $conditions_);
}
function pb_option_list($conditions_ = array()){
	$statement_ = pb_option_statement($conditions_);

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        return $statement_->count();
    }

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
    $limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;
    
	return pb_hook_apply_filters('pb_option_list', $statement_->select($orderby_, $limit_));
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
		$results_ = $row_data_['option_value'];
		break;
	}

	if(!isset($results_)){
		return $default_;
	}

	$_pb_option_map[$option_name_] = unserialize($results_);

	return $_pb_option_map[$option_name_];
}
function pb_option_exists($option_name_){
	$check_ = pb_option_list(array('option_name' => $option_name_));
	return count($check_) > 0;
}

function pb_option_update($option_name_, $option_value_){
	global $pbdb;

	if($option_value_ === null){
		$update_data_ = array(
			'option_value' => null,
			'mod_date' => pb_current_time(),
		);

		$pbdb->update("options", $update_data_, array(
			'option_name' => $option_name_,
		));

	}else{
		$option_value_ = serialize($option_value_);

		if(pb_option_exists($option_name_)){
			$update_data_ = array(
				'option_value' => $option_value_,
				'mod_date' => pb_current_time(),
			);

			$pbdb->update("options", $update_data_, array(
				'option_name' => $option_name_,
			));
		}else{
			$insert_data_ = array(
				'option_name' => $option_name_,
				'option_value' => $option_value_,
				'reg_date' => pb_current_time(),
			);

			$pbdb->insert("options", $insert_data_);
		}	
	}

	global $_pb_option_map;

	if(isset($_pb_option_map) && isset($_pb_option_map[$option_name_])){
		unset($_pb_option_map[$option_name_]);
	}
	pb_hook_do_action('pb_option_updated', $option_name_);
}

include(PB_DOCUMENT_PATH . 'includes/common/option-builtin.php');

?>