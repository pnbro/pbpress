<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_fileupload_url($params_ = array()){
	return pb_make_url(pb_home_url("fileupload"), $params_);
}

function _pb_fileupload_add_to_rewrite($results_){
	$results_['fileupload'] = array(
		'page' => PB_DOCUMENT_PATH."includes/common/_fileupload.php",
	);

	return $results_;
};
pb_hook_add_filter('pb_rewrite_list', "_pb_fileupload_add_to_rewrite");

function _pb_fileupload_add_to_header_pbvar($results_){
	$results_['fileupload_url'] = pb_fileupload_url();
	return $results_;
};
pb_hook_add_filter('pb-admin-head-pbvar', "_pb_fileupload_add_to_header_pbvar");
pb_hook_add_filter('pb-head-pbvar', "_pb_fileupload_add_to_header_pbvar");

function _pb_install_rewrite_for_upload_directory(){

	$rewrite_base_ = str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH."uploads/");
	$rewrite_file_ = fopen(PB_DOCUMENT_PATH."uploads/.htaccess", "w");

	if(!isset($rewrite_file_)){
		return new PBError(-1, "에러발생", "Rewrite를 생성할 수 없습니다. 파일권한을 확인하세요.");
	}

	fwrite($rewrite_file_, "RemoveType .phtml .php3 .htm .html .php\n\rRemoveHandler .phtml .php3 .htm .html .php");
	fclose($rewrite_file_);

	return true;
}

?>