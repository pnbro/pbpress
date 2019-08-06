<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $_pb_hook_action_map, $_pb_hook_filter_map;

$_pb_hook_action_map = array();
$_pb_hook_filter_map = array();

function pb_hook_add_action($action_name_, $func_, $priority_ = 10){
	global $_pb_hook_action_map;

	if(!isset($_pb_hook_action_map[$action_name_])) $_pb_hook_action_map[$action_name_] = array();

	$map_count_ = count($_pb_hook_action_map[$action_name_]);
	$insert_index_ = $map_count_;
	for($row_index_=0;$row_index_<$map_count_;++$row_index_){
		$target_item_ = $_pb_hook_action_map[$action_name_][$row_index_];

		if($target_item_['priority'] > $priority_){
			$insert_index_ = $row_index_;
			break;
		}
	}

	array_splice($_pb_hook_action_map[$action_name_], $insert_index_, 0, array(array(
		'func' => $func_,
		'priority' => $priority_,
	)));
}
function pb_hook_do_action($action_name_){
	global $_pb_hook_action_map;

	$args_ = func_get_args();
	$args_ = array_slice($args_, 1);

	$action_list_ = isset($_pb_hook_action_map[$action_name_]) ? $_pb_hook_action_map[$action_name_] : array();

	foreach($action_list_ as $action_data_){
		call_user_func_array($action_data_['func'], $args_);
	}
}

function pb_hook_add_filter($fitler_name_, $func_, $priority_ = 10){
	global $_pb_hook_filter_map;

	if(!isset($_pb_hook_filter_map[$fitler_name_])) $_pb_hook_filter_map[$fitler_name_] = array();

	$map_count_ = count($_pb_hook_filter_map[$fitler_name_]);
	$insert_index_ = $map_count_;
	for($row_index_=0;$row_index_<$map_count_;++$row_index_){
		$target_item_ = $_pb_hook_filter_map[$fitler_name_][$row_index_];

		if($target_item_['priority'] > $priority_){
			$insert_index_ = $row_index_;
			break;
		}
	}

	array_splice($_pb_hook_filter_map[$fitler_name_], $insert_index_, 0, array(array(
		'func' => $func_,
		'priority' => $priority_,
	)));
}
function pb_hook_apply_filters($fitler_name_){
	global $_pb_hook_filter_map;

	$args_ = func_get_args();
	$args_ = array_slice($args_, 1);

	if(count($args_) <= 0) return;

	$filter_list_ = isset($_pb_hook_filter_map[$fitler_name_]) ? $_pb_hook_filter_map[$fitler_name_] : array();

	foreach($filter_list_ as $filter_data_){
		$args_[0] = call_user_func_array($filter_data_['func'], $args_);
	}

	return $args_[0];
}
	
?>