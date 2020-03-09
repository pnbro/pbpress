<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $gcode_do;
$gcode_do = pbdb_data_object("gcode", array(
	'code_id'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 20, "pk" => true, "comment" => "코드ID"),
	'code_nm'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "comment" => "코드명"),
	'code_desc'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "코드설명"),
	'use_yn'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 1, "comment" => "사용여부"),

	'col1'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL1"),
	'col2'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL2"),
	'col3'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL3"),
	'col4'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL4"),
	
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"공통코드");

function pb_gcode_statement($conditions_ = array()){
	global $gcode_do;

	$statement_ = $gcode_do->statement();
	$statement_->add_field(
		"DATE_FORMAT(gcode.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(gcode.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis"
	);

	if(isset($conditions_['only_use']) && $conditions_['only_use'] === true){
		$statement_->add_compare_condition('gcode.use_yn', "Y", "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['code_id']) && strlen($conditions_['code_id'])){
		$statement_->add_compare_condition('gcode.code_id', $conditions_['code_id'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition('gcode.code_nm', $conditions_['keyword']);
	}

	return pb_hook_apply_filters('pb_gcode_statement', $statement_);
}
function pb_gcode_list($conditions_ = array()){
	$statement_ = pb_gcode_statement($conditions_);
	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        return $statement_->count();
    }

    $results_ = $statement_->select("code_id ASC", (isset($conditions_['limit']) ? $conditions_['limit'] : null));
    return pb_hook_apply_filters('pb_gcode_list', $results_);
}

function pb_gcode($code_id_){
	$gcode_ = pb_gcode_list(array("code_id" => $code_id_));
	if(!isset($gcode_) || count($gcode_) <= 0) return null;
	return $gcode_[0];
}

function pb_gcode_name($code_id_){
	$gcode_ = pb_gcode($code_id_);
	if(!isset($gcode_)) return $gcode_;
	return $gcode_["CODE_NM"];
}


function pb_gcode_add($raw_data_){
	global $gcode_do;
	$inserted_id_ = $gcode_do->insert($raw_data_);
	pb_hook_do_action("pb_gcode_added", $inserted_id_);
	return $inserted_id_;
}

function pb_gcode_update($id_, $raw_data_){
	global $gcode_do;
	$gcode_do->update($id_,$raw_data_);
	pb_hook_do_action("pb_gcode_updated", $id_);
}

function pb_gcode_delete($id_){
	global $gcode_do;
	pb_hook_do_action("pb_gcode_delete", $id_);
	$gcode_do->delete($id_);
}

global $gcode_dtl_do;

$gcode_dtl_do = pbdb_data_object("gcode_dtl", array(
	'code_id'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 20, "pk" => true, "fk" => array(
		'table' => 'gcode',
		'column' => "code_id",
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), "comment" => "코드ID"),
	'code_did'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 20, "pk" => true, "comment" => "코드상세ID"),
	'code_dnm'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "comment" => "코드상세명"),
	'code_ddesc'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "코드상세설명"),
	'use_yn'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 1, "comment" => "사용여부"),
	'sort_char'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 5, "comment" => "정렬순서"),

	'col1'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL1"),
	'col2'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL2"),
	'col3'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL3"),
	'col4'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "COL4"),

	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"권한별 작업범위");

function pb_gcode_dtl_statement($conditions_ = array()){
	global $gcode_do, $gcode_dtl_do;

	$statement_ = $gcode_dtl_do->statement();
	$statement_->add_field(
		"DATE_FORMAT(gcode_dtl.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(gcode_dtl.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis"
	);

	$gcode_join_cond_ = pbdb_ss_conditions();
	$gcode_join_cond_->add_compare("gcode.code_id", "gcode_dtl.code_id", "=");
	$statement_->add_join_statement("LEFT OUTER JOIN", $gcode_do->statement(), "gcode_dtl", $gcode_join_cond_, array(
		"col1 col1_title",
		"col2 col2_title",
		"col3 col3_title",
		"col4 col4_title",
	));


	if(isset($conditions_['only_use']) && $conditions_['only_use'] === true){
		$statement_->add_compare_condition('gcode_dtl.use_yn', "Y", "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['code_id']) && strlen($conditions_['code_id'])){
		$statement_->add_compare_condition('gcode_dtl.code_id', $conditions_['code_id'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['code_did']) && strlen($conditions_['code_did'])){
		$statement_->add_compare_condition('gcode_dtl.code_did', $conditions_['code_did'], "=", PBDB::TYPE_STRING);
	}

	if(isset($conditions_['col1']) && strlen($conditions_['col1'])){
		$statement_->add_compare_condition('gcode_dtl.col1', $conditions_['col1'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['col2']) && strlen($conditions_['col2'])){
		$statement_->add_compare_condition('gcode_dtl.col2', $conditions_['col2'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['col3']) && strlen($conditions_['col3'])){
		$statement_->add_compare_condition('gcode_dtl.col3', $conditions_['col3'], "=", PBDB::TYPE_STRING);
	}
	if(isset($conditions_['col4']) && strlen($conditions_['col4'])){
		$statement_->add_compare_condition('gcode_dtl.col4', $conditions_['col4'], "=", PBDB::TYPE_STRING);
	}

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition('gcode_dtl.code_dnm', $conditions_['keyword']);
	}

	return pb_hook_apply_filters('pb_gcode_dtl_statement', $statement_);
}
function pb_gcode_dtl_list($conditions_ = array()){
	$statement_ = pb_gcode_dtl_statement($conditions_);

	if(isset($conditions_['justcount']) && $conditions_['justcount'] == true){
        return $statement_->count();
    }

	$results_ = $statement_->select("gcode_dtl.sort_char ASC", (isset($conditions_['limit']) ? $conditions_['limit'] : null));
	return pb_hook_apply_filters('pb_gcode_dtl_list', $results_);
}

function pb_gcode_dtl($code_id_, $code_did_){
	$gcode_ = pb_gcode_dtl_list(array(
		"code_id" => $code_id_,
		"code_did" => $code_did_,
	));
	if(!isset($gcode_) || count($gcode_) <= 0) return null;	
	return $gcode_[0];
}

function pb_gcode_dtl_name($code_id_, $code_did_){
	$gcode_dtl_ = pb_gcode_dtl($code_id_,$code_did_);
	if(!isset($gcode_dtl_)) return null;
	return $gcode_dtl_['code_dnm'];
}

function pb_gcode_dtl_add($raw_data_){
	global $gcode_dtl_do;
	$inserted_id_ = $gcode_dtl_do->insert($raw_data_);
	pb_hook_do_action("pb_gcode_added", $inserted_id_);
	return $inserted_id_;
}

function pb_gcode_dtl_update($code_id_, $code_did_, $raw_data_){
	global $gcode_dtl_do;
	$gcode_dtl_do->update($code_id_, $code_did_, $raw_data_);
	pb_hook_do_action("pb_gcode_updated", $code_id_, $code_did_);
}

function pb_gcode_dtl_delete($code_id_, $code_did_){
	global $gcode_dtl_do;
	pb_hook_do_action("pb_gcode_delete", $code_id_, $code_did_);
	$gcode_dtl_do->delete($code_id_, $code_did_);
}

function pb_gcode_make_options($conditions_, $default_ = null, $echo_ = true){
	$dtl_list_ = pb_gcode_dtl_list($conditions_);

	$default_ = isset($default_) ? $default_ : array();
	if(gettype($default_) === "string") $default_ = array($default_);

	ob_start();

	foreach($dtl_list_ as $row_index_ => $row_data_){ ?>
		<option value="<?=$row_data_['code_did']?>" <?=(in_array($row_data_['code_did'], $default_) ? "selected" : "")?>
			data-col1="<?= $row_data_['col1'] ?>" data-col2="<?= $row_data_['col2'] ?>"
			 data-col3="<?= $row_data_['col3'] ?>" data-col4="<?= $row_data_['col4'] ?>"

		 ><?=$row_data_['code_dnm']?></option>
	<?php }

	$result_ = ob_get_clean();

	if($echo_) echo $result_;
	return $echo_;
}
function pb_query_gcode_dtl_name($code_id_, $column_){
	global $pbdb;
	return "(SELECT gcode_dtl.code_dnm FROM gcode_dtl WHERE gcode_dtl.code_id = '".pb_database_escape_string($code_id_)."' AND gcode_dtl.code_did = {$column_})";
}

include(PB_DOCUMENT_PATH . 'includes/gcode/gcode-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/gcode/gcode-adminpage.php');

?>