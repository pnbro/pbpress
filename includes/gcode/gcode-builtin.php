<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_gcode_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `GCODE` (
		`CODE_ID` varchar(20) NOT NULL COMMENT '코드ID',
		`CODE_NM` varchar(50) DEFAULT NULL COMMENT '코드명',
		`CODE_DESC` varchar(100) DEFAULT NULL COMMENT '코드설명',
		`USE_YN` varchar(1) DEFAULT NULL COMMENT '사용여부',

		`COL1` VARCHAR(100) DEFAULT NULL COMMENT 'COL1',
		`COL2` VARCHAR(100) DEFAULT NULL COMMENT 'COL2',
		`COL3` VARCHAR(100) DEFAULT NULL COMMENT 'COL3',
		`COL4` VARCHAR(100) DEFAULT NULL COMMENT 'COL4',

		`REG_DATE` datetime DEFAULT null COMMENT '등록일자',
		`MOD_DATE` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`CODE_ID`)
	) ENGINE=InnoDB COMMENT='공통코드';";

	$args_[] = "CREATE TABLE IF NOT EXISTS `GCODE_DTL` (
		`CODE_ID` varchar(20) NOT NULL COMMENT '코드ID',
		`CODE_DID` varchar(10) NOT NULL COMMENT '코드상세ID',
		`CODE_DNM` varchar(50) DEFAULT NULL COMMENT '코드상세명',
		`CODE_DDESC` varchar(100) DEFAULT NULL COMMENT '코드상세설명',

		`COL1` VARCHAR(100) DEFAULT NULL COMMENT 'COL1',
		`COL2` VARCHAR(100) DEFAULT NULL COMMENT 'COL2',
		`COL3` VARCHAR(100) DEFAULT NULL COMMENT 'COL3',
		`COL4` VARCHAR(100) DEFAULT NULL COMMENT 'COL4',

		`USE_YN` varchar(1) DEFAULT NULL COMMENT '사용여부',
		`SORT_CHAR` varchar(5) DEFAULT NULL COMMENT '정렬구분자',
		`REG_DATE` datetime DEFAULT NULL COMMENT '등록일자',
		`MOD_DATE` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`CODE_ID`,`CODE_DID`),
		CONSTRAINT `GCODE_DTL_FK1` FOREIGN KEY (`CODE_ID`) REFERENCES `GCODE` (`CODE_ID`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='공통코드 - 상세';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "pb_gcode_install_tables");

function pb_gcode_install(){
	$gcode_list_ = pb_hook_apply_filters("pb_intialize_gcode_list", array());

	global $pbdb;

	foreach($gcode_list_ as $code_id_ => $data_){

		$check_exists_gcode_ = pb_gcode($code_id_);
		if(isset($check_exists_gcode_) && !empty($check_exists_gcode_)) continue; //if gcode exists, pass though it

		$gcode_dtl_list_ = $data_['data'];

		$pbdb->insert("GCODE", array(
			'CODE_ID' => $code_id_,
			'CODE_NM' => $data_['name'],
			'USE_YN' => ((isset($data_['use_yn']) ? $data_['use_yn'] : true) ? "Y" : "N"),
			'REG_DATE' => pb_current_time("mysql"),
		));

		$sort_char_ = 0;
		foreach($gcode_dtl_list_ as $code_did_ => $dtl_data_){

			++$sort_char_;

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

				$duse_yn_ = ((isset($dtl_data_['use_yn']) ? $dtl_data_['use_yn'] : true) ? "Y" : "N");
			}

			$pbdb->insert("GCODE_DTL", array(
				'CODE_ID' => $code_id_,
				'CODE_DID' => $code_did_,
				'CODE_DNM' => $code_dnm_,
				'USE_YN' => $duse_yn_,
				'COL1' => $col1_,
				'COL2' => $col2_,
				'COL3' => $col3_,
				'COL4' => $col4_,
				'SORT_CHAR' => $sort_char_,
				'REG_DATE' => pb_current_time("mysql"),
			));
		}

	}
}
pb_hook_add_action("pb_installed_tables", "pb_gcode_install");

?>