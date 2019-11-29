<?php 		

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_REWRITE_BASE', str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH));


function pb_exists_rewrite(){
	return file_exists(PB_DOCUMENT_PATH.".htaccess");
}
function pb_install_rewrite(){

	$rewrite_base_ = str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH);
	$rewrite_file_ = fopen(PB_DOCUMENT_PATH.".htaccess", "w");

	if(!isset($rewrite_file_)){
		return new PBError(-1, "에러발생", "Rewrite를 생성할 수 없습니다. 파일권한을 확인하세요.");
	}

	fwrite($rewrite_file_, "RewriteEngine On
\nRewriteCond %{REQUEST_FILENAME} !-f
\nRewriteCond %{REQUEST_FILENAME} !-d
\nRewriteRule ^admin/(.+)$ admin/index.php [L]
\nRewriteCond %{REQUEST_FILENAME} !-f
\nRewriteCond %{REQUEST_FILENAME} !-d
\nRewriteRule ^(.+)$ index.php [L]");
	fclose($rewrite_file_);

	return true;
}

function pb_rewrite_list(){
	global $_pb_rewrite_list;

	if(!isset($_pb_rewrite_list)){
		$_pb_rewrite_list = array();
	}

	return pb_hook_apply_filters("pb_rewrite_list", $_pb_rewrite_list);
}
function pb_rewrite_data($key_){
	global $_pb_rewrite_list;

	if(!isset($_pb_rewrite_list)){
		$_pb_rewrite_list = pb_rewrite_list();
	}
	return isset($_pb_rewrite_list[$key_]) ? $_pb_rewrite_list[$key_] : null;
}
function pb_rewrite_register($key_, $data_){
	$rewrite_list_ = pb_rewrite_list();
	$rewrite_list_[$key_] = $data_;

	global $_pb_rewrite_list;

	$_pb_rewrite_list = $rewrite_list_;
}
function pb_rewrite_unregister($key_){
	$rewrite_list_ = pb_rewrite_list();
	unset($rewrite_list_[$key_]);

	global $_pb_rewrite_list;
	
	$_pb_rewrite_list = $rewrite_list_;
}

function pb_rewrite_path(){
	global $pb_rewrite_path;
	if(isset($pb_rewrite_path)) return $pb_rewrite_path;

	if(!isset($_SERVER['REDIRECT_URL'])) return null;
	if(strpos($_SERVER['REQUEST_URI'], PB_REWRITE_BASE) === false) return null;

	$subpath_string_ = preg_replace('/'.preg_quote(PB_REWRITE_BASE,"/").'/', '', strtok($_SERVER['REQUEST_URI'], "?"), 1);
	$subpath_string_ = ltrim($subpath_string_,"/");
	$subpath_string_ = rtrim($subpath_string_,"/");
	$subpath_string_ = preg_replace('/(\/+)/','/',$subpath_string_);

	$subpath_map_ = strlen($subpath_string_) > 0 ? explode("/", $subpath_string_) : array();

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

function pb_is_current_slug($slug_){
	$target_slug_ = pb_current_slug();
	return ($target_slug_ === $slug_);
}
function pb_is_home(){
	if(strpos($_SERVER['REQUEST_URI'], PB_REWRITE_BASE) === false) return false;
	return !isset($_SERVER['REDIRECT_STATUS']);
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

function pb_rewrite_unique_slug($slug_, $retry_count_ = 0, $extra_data_ = array()){
	$temp_slug_ = $slug_;
	if($retry_count_ > 0){
		$temp_slug_ .= "-".$retry_count_;
	}

	$check_data2_ = pb_rewrite_data($temp_slug_);
	if(!isset($check_data2_)){
		return pb_hook_apply_filters("pb_rewrite_unique_slug", $temp_slug_, $slug_, $retry_count_, $extra_data_);
	}

	return pb_rewrite_unique_slug($slug_, ++$retry_count_, $extra_data_);
}
function _pb_rewrite_unique_slug_for_reserved($result_, $original_slug_, $retry_count_ = 0, $extra_data_){
	global $_pb_rewrite_reserved;

	if(!isset($_pb_rewrite_reserved)){
		$_pb_rewrite_reserved = pb_hook_apply_filters("pb_reserved_slug", array("admin", "includes", "lib", "themes", "uploads"));
	}

	if(!in_array($result_, $_pb_rewrite_reserved)){
		return $result_;
	}

	return pb_rewrite_unique_slug($original_slug_, ++$retry_count_, $extra_data_);
}
pb_hook_add_filter("pb_rewrite_unique_slug", "_pb_rewrite_unique_slug_for_reserved");

function _pb_rewrite_path_normalize(){
	if(!in_array($_SERVER["REQUEST_METHOD"], array("GET"))) return;

	$subpath_string_ = preg_replace('/'.preg_quote(PB_REWRITE_BASE,"/").'/', '', strtok($_SERVER['REQUEST_URI'], "?"), 1);
	$subpath_string_normalized_ = ltrim($subpath_string_, "/");
	$subpath_string_normalized_ = rtrim($subpath_string_normalized_,"/")."/";

	$is_last_slash_ = strrpos($subpath_string_, "/") == strlen($subpath_string_) - 1;
	$subpath_string_ .= ($is_last_slash_ ? "" : "/");
	$query_string_ = @$_SERVER['QUERY_STRING'];

	if($subpath_string_ !== $subpath_string_normalized_){
		$normalized_url_ = pb_home_url($subpath_string_normalized_);
		if(strlen($query_string_)){
			$normalized_url_ .= "?".$query_string_;
		}
		pb_redirect($normalized_url_);
		pb_end();
	}

	$subpath_string_normalized_ = preg_replace('/(\/+)/','/',$subpath_string_normalized_);

	if($subpath_string_ !== $subpath_string_normalized_){
		$normalized_url_ = pb_home_url($subpath_string_normalized_);
		if(strlen($query_string_)){
			$normalized_url_ .= "?".$query_string_;
		}
		
		pb_redirect($normalized_url_);
		pb_end();
	}

}
pb_hook_add_action("pb_init", "_pb_rewrite_path_normalize");
pb_hook_add_action("pb_admin_init", "_pb_rewrite_path_normalize");

?>