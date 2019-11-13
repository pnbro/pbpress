<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_gcode_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT code_id code_id,
					code_nm code_nm,
					code_desc code_desc,
					use_yn use_yn,

					col1 col1,
					col2 col2,
					col3 col3,
					col4 col4,

					reg_date reg_date,
					mod_date mod_date,

					DATE_FORMAT(reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis,
					DATE_FORMAT(mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis
	FROM gcode
	WHERE 1=1 ";

	if(isset($conditions_['only_use']) && $conditions_['only_use'] === true){
		$query_ .= " AND gcode.use_yn = 'Y' ";
	}
	if(isset($conditions_['code_id']) && strlen($conditions_['code_id'])){
		$query_ .= " AND gcode.code_id = '".pb_database_escape_string($conditions_['code_id'])."' ";
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND gcode.code_nm LIKE '".pb_database_escape_string($conditions_['keyword'])."%' ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

	$query_ .= " ORDER BY code_id ASC";

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return $pbdb->select($query_);
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

function pb_gcode_dtl_list($conditions_ = array()){
	global $pbdb;

	
	$query_ = "SELECT 
					gcode_dtl.code_id code_id,
					gcode_dtl.code_did code_did,
					gcode_dtl.code_dnm code_dnm,
					gcode_dtl.code_ddesc code_ddesc,

					gcode_dtl.col1 col1,
					gcode_dtl.col2 col2,
					gcode_dtl.col3 col3,
					gcode_dtl.col4 col4,

					gcode.col1 col1_title,
					gcode.col2 col2_title,
					gcode.col3 col3_title,
					gcode.col4 col4_title,

					gcode_dtl.use_yn use_yn,
					gcode_dtl.sort_char sort_char,
					gcode_dtl.reg_date reg_date,
					gcode_dtl.mod_date mod_date,

					DATE_FORMAT(gcode_dtl.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis,
					DATE_FORMAT(gcode_dtl.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis
	FROM gcode_dtl

	LEFT OUTER JOIN gcode
	ON   gcode.code_id = gcode_dtl.code_id


	WHERE 1=1 ";

	if(isset($conditions_['only_use']) && $conditions_['only_use'] === true){
		$query_ .= " AND gcode_dtl.use_yn = 'Y' ";
	}
	if(isset($conditions_['code_id']) && strlen($conditions_['code_id'])){
		$query_ .= " AND   gcode_dtl.code_id = '".pb_database_escape_string($conditions_['code_id'])."' ";
	}
	if(isset($conditions_['code_did']) && strlen($conditions_['code_did'])){
		$query_ .= " AND gcode_dtl.code_did = '".pb_database_escape_string($conditions_['code_did'])."' ";
	}

	if(isset($conditions_['col1']) && strlen($conditions_['col1'])){
		$query_ .= " AND gcode_dtl.col1 = '".pb_database_escape_string($conditions_['col1'])."' ";
	}
	if(isset($conditions_['col2']) && strlen($conditions_['col2'])){
		$query_ .= " AND gcode_dtl.col2 = '".pb_database_escape_string($conditions_['col2'])."' ";
	}
	if(isset($conditions_['col3']) && strlen($conditions_['col3'])){
		$query_ .= " AND gcode_dtl.col3 = '".pb_database_escape_string($conditions_['col3'])."' ";
	}
	if(isset($conditions_['col4']) && strlen($conditions_['col4'])){
		$query_ .= " AND gcode_dtl.col4 = '".pb_database_escape_string($conditions_['col4'])."' ";
	}


	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND gcode_dtl.code_dnm LIKE '".pb_database_escape_string($conditions_['keyword'])."%' ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] == true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

    
	$query_ .= " ORDER BY gcode_dtl.sort_char ASC";

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return $pbdb->select($query_);
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

function pb_gcode_make_options($conditions_, $default_ = null){
	$dtl_list_ = pb_gcode_dtl_list($conditions_);

	ob_start();

	foreach($dtl_list_ as $row_index_ => $row_data_){ ?>
		<option value="<?=$row_data_['code_did']?>" <?=($default_ === $row_data_['code_did'] ? "selected" : "")?>
			data-col1="<?= $row_data_['col1'] ?>" data-col2="<?= $row_data_['col2'] ?>"
			 data-col3="<?= $row_data_['col3'] ?>" data-col4="<?= $row_data_['col4'] ?>"

		 ><?=$row_data_['code_dnm']?></option>
	<?php }

	return ob_get_clean();
}
function pb_query_gcode_dtl_name($code_id_, $column_){
	global $pbdb;
	return "(SELECT gcode_dtl.code_dnm FROM gcode_dtl WHERE gcode_dtl.code_id = '".pb_database_escape_string($code_id_)."' AND gcode_dtl.code_did = {$column_})";
}

include(PB_DOCUMENT_PATH . 'includes/gcode/gcode-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/gcode/gcode-adminpage.php');

?>