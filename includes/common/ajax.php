<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_ajax_add_to_rewrite($results_){
	$results_['ajax'] = array(
		'rewrite_handler' => "_pb_ajax_rewrite_handler",
	);

	return $results_;
};
pb_hook_add_filter('pb_rewrite_list', "_pb_ajax_add_to_rewrite");

function pb_add_ajax($name_, $func_){
	pb_hook_add_action('pb_ajax_'.$name_, $func_);	
}
function pb_ajax_url($action_ = null, $extras_ = null){
	$ajax_base_url_ = pb_home_url("ajax");

	if(strlen($action_)){
		$ajax_base_url_ = pb_append_url($ajax_base_url_, $action_);
	}

	if(isset($extras_)){
		$ajax_base_url_ = pb_make_url($ajax_base_url_, $extras_);
	}

	return $ajax_base_url_;
}
function _pb_ajax_add_to_header_pbvar($results_){
	$results_['ajax_url'] = pb_ajax_url();

	return $results_;
};
pb_hook_add_filter('pb-admin-head-pbvar', "_pb_ajax_add_to_header_pbvar");
pb_hook_add_filter('pb-head-pbvar', "_pb_ajax_add_to_header_pbvar");

function _pb_ajax_rewrite_handler($rewrite_path_, $page_data_){
	if(count($rewrite_path_) > 2) return new PBError(404, __("잘못된 접근"), __("요청값이 잘못되었습니다."));
	if(!isset($rewrite_path_[1]) || !strlen($rewrite_path_[1])){
		return new PBError(404, __("잘못된 접근"), __("요청값이 잘못되었습니다."));
	}
	return PB_DOCUMENT_PATH."includes/common/_ajax.php";
}

?>