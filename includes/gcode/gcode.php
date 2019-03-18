<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_gcode_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT CODE_ID CODE_ID,
					CODE_NM CODE_NM,
					CODE_DESC CODE_DESC,
					USE_YN USE_YN,

					COL1 COL1,
					COL2 COL2,
					COL3 COL3,
					COL4 COL4,

					REG_DATE REG_DATE,
					MOD_DATE MOD_DATE,

					DATE_FORMAT(REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS,
					DATE_FORMAT(MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
	FROM GCODE
	WHERE 1=1 ";

	if(isset($conditions_['only_use']) && $conditions_['only_use'] === true){
		$query_ .= " AND GCODE.USE_YN = 'Y' ";
	}
	if(isset($conditions_['code_id']) && strlen($conditions_['code_id'])){
		$query_ .= " AND GCODE.CODE_ID = '".mysql_real_escape_string($conditions_['code_id'])."' ";
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND GCODE.CODE_NM LIKE '".mysql_real_escape_string($conditions_['keyword'])."%' ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

	$query_ .= " ORDER BY CODE_ID ASC";

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
					CODE_ID CODE_ID,
					CODE_DID CODE_DID,
					CODE_DNM CODE_DNM,
					CODE_DDESC CODE_DDESC,

					COL1 COL1,
					COL2 COL2,
					COL3 COL3,
					COL4 COL4,

					USE_YN USE_YN,
					SORT_CHAR SORT_CHAR,
					REG_DATE REG_DATE,
					MOD_DATE MOD_DATE,

					DATE_FORMAT(REG_DATE, '%Y.%m.%d %H:%i:%S') REG_DATE_YMDHIS,
					DATE_FORMAT(MOD_DATE, '%Y.%m.%d %H:%i:%S') MOD_DATE_YMDHIS
	FROM GCODE_DTL
	WHERE 1=1 ";

	if(isset($conditions_['only_use']) && $conditions_['only_use'] === true){
		$query_ .= " AND GCODE_DTL.USE_YN = 'Y' ";
	}
	if(isset($conditions_['code_id']) && strlen($conditions_['code_id'])){
		$query_ .= " AND   CODE_ID = '".mysql_real_escape_string($conditions_['code_id'])."' ";
	}
	if(isset($conditions_['code_did']) && strlen($conditions_['code_did'])){
		$query_ .= " AND GCODE_DTL.CODE_DID = '".mysql_real_escape_string($conditions_['code_did'])."' ";
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND GCODE_DTL.CODE_DNM LIKE '".mysql_real_escape_string($conditions_['keyword'])."%' ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] == true){
        $query_ = " SELECT COUNT(*) CNT FROM (". $query_. ") TMP";
        return $pbdb->get_var($query_);
    }

    
	$query_ .= " ORDER BY sort_char ASC";

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
	return $gcode_dtl_['CODE_DNM'];
}

function pb_gcode_make_options($conditions_, $default_ = null){
	$dtl_list_ = pb_gcode_dtl_list($conditions_);

	ob_start();

	foreach($dtl_list_ as $row_index_ => $row_data_){ ?>
		<option value="<?=$row_data_['CODE_DID']?>" <?=($default_ === $row_data_['CODE_DID'] ? "selected" : "")?>
			data-col1="<?= $row_data_['COL1'] ?>" data-col2="<?= $row_data_['COL2'] ?>"
			 data-col3="<?= $row_data_['COL3'] ?>" data-col4="<?= $row_data_['COL4'] ?>"

		 ><?=$row_data_['CODE_DNM']?></option>
	<?php }

	return ob_get_clean();
}
function pb_query_gcode_dtl_name($code_id_, $column_){
	global $pbdb;
	return "(SELECT GCODE_DTL.CODE_DNM FROM GCODE_DTL WHERE GCODE_DTL.CODE_ID = '".mysql_real_escape_string($code_id_)."' AND GCODE_DTL.CODE_DID = {$column_})";
}

include(PB_DOCUMENT_PATH . 'includes/gcode/gcode-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/gcode/gcode-adminpage.php');

?>