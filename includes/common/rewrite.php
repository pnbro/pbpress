<?php 		

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_REWRITE_BASE', str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH));

function pb_install_rewrite(){

	$rewrite_base_ = str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH);
	$rewrite_file_ = fopen(PB_DOCUMENT_PATH.".htaccess", "w");

	if(!isset($rewrite_file_)){
		return new PBError(-1, "에러발생", "Rewrite를 생성할 수 없습니다. 파일권한을 확인하세요.");
	}


	fwrite($rewrite_file_, "RewriteEngine On\n
RewriteBase ".PB_REWRITE_BASE."\n
RewriteRule ^index\.php$ - [L]\n
RewriteCond %{REQUEST_FILENAME} !-f\n
RewriteCond %{REQUEST_FILENAME} !-d\n
RewriteRule . ".PB_REWRITE_BASE."index.php [L]");
	fclose($rewrite_file_);

	return true;
}

function pb_rewrite_list(){
	return pb_hook_apply_filters("pb_rewrite_list", array());
}

function pb_rewrite_path(){
	global $pb_rewrite_path;
	if(isset($pb_rewrite_path)) return $pb_rewrite_path;

	if(strpos($_SERVER['REQUEST_URI'], PB_REWRITE_BASE) === false) return null;

	$subpath_map_ = str_replace(PB_REWRITE_BASE, "", strtok($_SERVER['REQUEST_URI'], "?"));
	$subpath_map_ = rtrim($subpath_map_, '/');
	$subpath_map_ = explode("/", $subpath_map_);

	global $pb_rewrite_path;
	$pb_rewrite_path = $subpath_map_;
	
	return $pb_rewrite_path;
}

function pb_current_slug(){
	$write_path_ = pb_rewrite_path();	
	if(!isset($write_path_) || count($write_path_) <= 0) return null;

	return $write_path_[0];
}

function pb_current_rewrite(){
	$target_slug_ = pb_current_slug();

	if(!strlen($target_slug_)) return null;

	$rewrite_list_ = pb_rewrite_list();
	if(!isset($rewrite_list_[$target_slug_])) return null;
	return $rewrite_list_[$target_slug_];
}


function pb_rewrite_common_handler($rewrite_path_, $page_data_){
	if(!isset($page_data_)){
		return new PBError(503, "잘못된 접근", "잘못된 접근입니다.");
	}

	if(count($rewrite_path_) > 1){
		return new PBError(404, "잘못된 접근", "잘못된 접근입니다.");
	}

	return $page_data_['page'];
}


?>