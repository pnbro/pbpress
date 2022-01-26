<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $clob_options_do;
$clob_options_do = pbdb_data_object("clob_options", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'option_name' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "nn" => true, "index" => true, "comment" => "패스워드"),
	'crypted_yn' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 1, "default" => 'N', "index" => true, "comment" => "암호화여부", "check_exists" => true),
	'iv' => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "comment" => "암호화IV", "check_exists" => true),
	'option_value' => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "패스워드"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"옵션");


function pb_clob_option_statement($conditions_ = array()){
	global $clob_options_do;

	$statement_ = $clob_options_do->statement();

	$statement_->add_field(
		"DATE_FORMAT(clob_options.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(clob_options.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(clob_options.reg_date, '%Y.%m.%d') reg_date_ymd",

		"DATE_FORMAT(clob_options.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(clob_options.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(clob_options.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_clob_option_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_clob_option_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_clob_option_list_where', '', $conditions_);

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition(array(
			'clob_options.option_name',
		), $conditions_['keyword']);
	}
	if(isset($conditions_['id']) && strlen($conditions_['id'])){
		$statement_->add_compare_condition("clob_options.id", $conditions_['id']);
	}

	if(isset($conditions_['option_name']) && strlen($conditions_['option_name'])){
		$statement_->add_compare_condition("clob_options.option_name", $conditions_['option_name']);
	}

	return pb_hook_apply_filters('pb_clob_option_statement', $statement_, $conditions_);
}

function pb_clob_option_list($conditions_ = array()){
	$statement_ = pb_clob_option_statement($conditions_);

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        return $statement_->count();
    }

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
    $limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;
    
	return pb_hook_apply_filters('pb_clob_option_list', $statement_->select($orderby_, $limit_));
}
function pb_clob_options_list($conditions_ = array()){
	return pb_clob_option_list($conditions_);
}

function pb_clob_option_value($option_name_, $default_ = null, $cache_ = true){
	global $_pb_clob_options_map;

	if($cache_ && isset($_pb_clob_options_map) && isset($_pb_clob_options_map[$option_name_])){
		return $_pb_clob_options_map[$option_name_];
	}

	$temp_ = pb_clob_options_list(array(
		"option_name" => $option_name_,
	));

	$temp_data_ = @$temp_[0];

	if(!isset($temp_data_)){
		return $default_;	
	}

	$results_ = $temp_data_['option_value'];

	if(!isset($results_)){
		return $default_;
	}

	$_pb_clob_options_map[$option_name_] = unserialize($results_);

	if($temp_data_['crypted_yn'] === "Y"){
		$_pb_clob_options_map[$option_name_] = pb_static_crypt_decrypt($_pb_clob_options_map[$option_name_], $temp_data_['iv']);
	}

	return $_pb_clob_options_map[$option_name_];
}

//Deprecated
function pb_clob_options_value($option_name_, $default_ = null, $cache_ = true){
	return pb_clob_option_value($option_name_, $default_, $cache_);
}

function pb_clob_option_update($option_name_, $option_value_, $crypt_ = false){
	global $pbdb;

	$before_data_ = pb_clob_option_statement(array('option_name' => $option_name_));
	$before_data_ = $before_data_->get_first_row();

	$option_id_ = null;

	if($option_value_ === null){
		$update_data_ = array(
			'option_value' => null,
			'mod_date' => pb_current_time(),
		);

		if(isset($before_data_)){
			$pbdb->delete("clob_options", array(
				'id' => $before_data_['id'],
			));
			pb_hook_do_action('pb_clob_option_deleted', $before_data_);
			$option_id_ = $before_data_['id'];
		}
	}else{

		$iv_ = null;
		if($crypt_){
			$temp_ = pb_static_crypt_encrypt($option_value_);
			$option_value_ = $temp_['data'];
			$iv_ = $temp_['iv'];
		}
		$option_value_ = serialize($option_value_);

		if(isset($before_data_)){
			$update_data_ = array(
				'option_value' => $option_value_,
				'crypted_yn' => $crypt_ ? "Y" : "N",
				'iv' => $iv_,
				'mod_date' => pb_current_time(),
			);

			$pbdb->update("clob_options", $update_data_, array(
				'id' => $before_data_['id'],
			));

			$option_id_ = $before_data_['id'];
		}else{
			$insert_data_ = pb_hook_apply_filters('pb_clob_option_insert', array(
				'option_name' => $option_name_,
				'option_value' => $option_value_,
				'crypted_yn' => $crypt_ ? "Y" : "N",
				'iv' => $iv_,
				'reg_date' => pb_current_time(),
			));

			$option_id_ = $pbdb->insert("clob_options", $insert_data_);
		}	
	}

	global $_pb_clob_option_map;

	if(isset($_pb_clob_option_map) && isset($_pb_clob_option_map[$option_name_])){
		unset($_pb_clob_option_map[$option_name_]);
	}

	if(strlen($option_id_)){
		pb_hook_do_action('pb_clob_option_updated', $option_id_);	
	}

	return $option_id_;
}
pb_clob_option_update("mail_smtp_user_pass", 'Adminqaz1!', true);

//Deprecated
function pb_clob_options_update($option_name_, $option_value_){
	return pb_clob_option_update($option_name_, $option_value_);
}

include(PB_DOCUMENT_PATH . 'includes/common/clob-options-builtin.php');

?>