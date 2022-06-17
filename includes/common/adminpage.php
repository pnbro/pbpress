<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}
define('PB_ADMINPAGE_REWRITE_BASE', PB_REWRITE_BASE."admin/");

function _pb_adminpage_list_sort($a_, $b_){
	$a_sort_ = isset($a_['sort']) ? $a_['sort'] : 0;
	$b_sort_ = isset($b_['sort']) ? $b_['sort'] : 0;
	return $a_sort_ < $b_sort_ ? -1 : 1;
}

function pb_adminpage_list(){
	global $pb_adminpage_list;
	if(isset($pb_adminpage_list)) return $pb_adminpage_list;

	$pb_adminpage_list = pb_hook_apply_filters("pb_adminpage_list", array(
		'common' => array(
			'name' => __('사이트관리'),
			'type' => 'directory',
			'sort' => 1,
		),
		'manage-site' => array(
			'name' => __('사이트설정'),
			'type' => 'menu',
			'directory' => 'common',
			'page' => PB_DOCUMENT_PATH."admin/manage-site.php",
			'authority_task' => 'manage_site',
			'subpath' => null,
			'sort' => 1,
		),
	));

	uasort($pb_adminpage_list, "_pb_adminpage_list_sort");
	return $pb_adminpage_list;
}

function pb_adminpage_tree($apply_authority_ = true){
	global $pb_adminpage_tree;
	if(isset($pb_adminpage_tree)) return $pb_adminpage_tree;

	$pb_adminpage_tree = array();

	$adminpage_list_ = pb_adminpage_list();
	$temp_children_ = array();
	$current_adminpage_slug_ = pb_current_adminpage_slug();

	$current_user_id_ = pb_current_user_id();

	//items which is directory or one depth
	foreach($adminpage_list_ as $slug_ => $data_){
		if(isset($data_['type']) && $data_['type'] === "directory"){
			$pb_adminpage_tree[$slug_] = $data_;
			$pb_adminpage_tree[$slug_]['active'] = false;
			$pb_adminpage_tree[$slug_]['children'] = array();
			continue;
		}

		if((!isset($data_['type']) || $data_['type'] === "menu") && !isset($data_['directory'])){

			if($apply_authority_ && isset($data_['authority_task'])){
				if(!pb_user_has_authority_task($current_user_id_, $data_['authority_task'])){
					continue;
				}
			}


			$pb_adminpage_tree[$slug_] = $data_;
			$pb_adminpage_tree[$slug_]["active"] = ($current_adminpage_slug_ === $slug_);

			continue;
		}

		$temp_children_[$slug_] = $data_;
	}

	//children
	foreach($temp_children_ as $slug_ => $data_){
		if($apply_authority_ && isset($data_['authority_task'])){
			if(!pb_user_has_authority_task($current_user_id_, $data_['authority_task'])){
				continue;
			}
		}
		$directory_ = $pb_adminpage_tree[$data_['directory']];
		$active_ = ($current_adminpage_slug_ === $slug_);

		$directory_['children'][$slug_] = $data_;
		$directory_['children'][$slug_]['active'] = $active_;
		$directory_['active'] = ($directory_['active'] || $active_);

		$pb_adminpage_tree[$data_['directory']] = $directory_;
	}


	return $pb_adminpage_tree;
}


function pb_adminpage_rewrite_path(){
	global $pb_adminpage_rewrite_path;
	if(isset($pb_adminpage_rewrite_path)) return pb_hook_apply_filters('pb_adminpage_rewrite_path', $pb_adminpage_rewrite_path);

	if(!isset($_SERVER['REDIRECT_URL'])) return null;
	if(strpos($_SERVER['REQUEST_URI'], PB_ADMINPAGE_REWRITE_BASE) === false) return null;

	$admin_subpath_map_ = preg_replace('/'.preg_quote(PB_ADMINPAGE_REWRITE_BASE,"/").'/', '', strtok($_SERVER['REQUEST_URI'], "?"), 1);
	$admin_subpath_map_ = rtrim($admin_subpath_map_, '/');

	if(strlen($admin_subpath_map_)){
		$admin_subpath_map_ = explode("/", $admin_subpath_map_);	
	}else{
		$admin_subpath_map_ = array();
	}
	

	global $pb_adminpage_rewrite_path;
	$pb_adminpage_rewrite_path = $admin_subpath_map_;
	
	return pb_hook_apply_filters('pb_adminpage_rewrite_path', $pb_adminpage_rewrite_path);
}

function pb_current_adminpage_slug(){
	$write_path_ = pb_adminpage_rewrite_path();	
	if(!isset($write_path_) || count($write_path_) <= 0) return null;

	return $write_path_[0];
}

function pb_current_adminpage(){
	$target_slug_ = pb_current_adminpage_slug();

	if(!strlen($target_slug_)) return null;

	$pb_adminpage_list_ = pb_adminpage_list();
	if(!isset($pb_adminpage_list_[$target_slug_])) return null;
	return $pb_adminpage_list_[$target_slug_];
}
function pb_is_adminpage(){
	return (strpos($_SERVER['REQUEST_URI'], PB_REWRITE_BASE."admin") !== false);
}

function pb_adminpage_rewrite_common_handler($rewrite_path_, $adminpage_data_){
	if(isset($rewrite_path_) && count($rewrite_path_) > 1){
		return new PBError(503, __("잘못된 접근"), __("잘못된 접근입니다."));
	}

	if(!isset($rewrite_path_) || count($rewrite_path_) <= 0){
		return pb_hook_apply_filters("pb-adminpage-dashboard",PB_DOCUMENT_PATH."admin/dashboard.php");
	}

	return $adminpage_data_['page'];
}


function pb_adminpage_draw_error($code_, $message_, $title_ = "ERROR!"){
	$error_map_ = array(
		'error_code' => $code_,
		'error_message' => $message_,
		'error_title' => $title_,
	);

	extract($error_map_);
	include(PB_DOCUMENT_PATH."admin/error.php");
}

?>