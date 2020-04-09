<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_gcode_install(){
	global $_pb_gcode_initial_list;
	$gcode_list_ = pb_hook_apply_filters("pb_intialize_gcode_list", $_pb_gcode_initial_list);

	global $pbdb;

	foreach($gcode_list_ as $code_id_ => $data_){

		$check_exists_gcode_ = pb_gcode($code_id_);
		if(isset($check_exists_gcode_) && !empty($check_exists_gcode_)) continue; //if gcode exists, pass though it

		$gcode_dtl_list_ = $data_['data'];

		$pbdb->insert("gcode", array(
			'code_id' => $code_id_,
			'code_nm' => $data_['name'],
			'use_yn' => ((isset($data_['use_yn']) ? $data_['use_yn'] : true) ? "Y" : "N"),
			'reg_date' => pb_current_time("mysql"),
		));

		$t_sort_char_ = 0;
		$sort_digit_ = ceil((count($gcode_dtl_list_) / 10));
		foreach($gcode_dtl_list_ as $code_did_ => $dtl_data_){

			++$t_sort_char_;
			$sort_char_ = str_pad($t_sort_char_, $sort_digit_, "0", STR_PAD_LEFT);

			$code_dnm_ = null;
			$duse_yn_ = "Y";

			$col1_ = null;
			$col2_ = null;
			$col3_ = null;
			$col4_ = null;

			if(gettype($dtl_data_) === "string"){
				$code_dnm_ = $dtl_data_;
			}else{
				$code_dnm_ = $dtl_data_['name'];
				$col1_ = isset($dtl_data_['col1']) ? $dtl_data_['col1'] : null;
				$col2_ = isset($dtl_data_['col2']) ? $dtl_data_['col2'] : null;
				$col3_ = isset($dtl_data_['col3']) ? $dtl_data_['col3'] : null;
				$col4_ = isset($dtl_data_['col4']) ? $dtl_data_['col4'] : null;
				$sort_char_ = isset($dtl_data_['sort_char']) ? $dtl_data_['sort_char'] : $sort_char_;
				$duse_yn_ = ((isset($dtl_data_['use_yn']) ? $dtl_data_['use_yn'] : true) ? "Y" : "N");
			}

			$pbdb->insert("gcode_dtl", array(
				'code_id' => $code_id_,
				'code_did' => $code_did_,
				'code_dnm' => $code_dnm_,
				'use_yn' => $duse_yn_,
				'col1' => $col1_,
				'col2' => $col2_,
				'col3' => $col3_,
				'col4' => $col4_,
				'sort_char' => $sort_char_,
				'reg_date' => pb_current_time("mysql"),
			));
		}

	}
}
pb_hook_add_action("pb_installed_tables", "pb_gcode_install");

?>