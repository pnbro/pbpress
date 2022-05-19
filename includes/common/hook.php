<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $_pb_hook_action_map, $_pb_hook_filter_map;

$_pb_hook_action_map = array();
$_pb_hook_filter_map = array();

function pb_hook_get_action($action_name_, $key_){
	global $_pb_hook_action_map;

	if(!isset($_pb_hook_action_map[$action_name_])) return null;

	foreach($_pb_hook_action_map[$action_name_] as $index_ => $action_data_){
		if($action_data_['key'] === $converted_key_){
			return $action_data_;
		}
	}
	return null;
}

function pb_hook_set_action($action_name_, $key_, $func_, $priority_ = 10){
	
	if(strpos($action_name_, "|") !== false){
		$action_name_ = explode("|", $action_name_);
		foreach($action_name_ as $a_){
			pb_hook_set_action($a_, $key_, $func_, $priority_);
		}

		return;
	}

	global $_pb_hook_action_map;

	if(!isset($_pb_hook_action_map[$action_name_])) $_pb_hook_action_map[$action_name_] = array();

	foreach($_pb_hook_action_map[$action_name_] as $index_ => $action_data_){
		if($action_data_['key'] === $key_){
			array_splice($_pb_hook_action_map[$action_name_], $index_, 1);
			break;
		}
	}

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
		'key' => $key_,
		'func' => $func_,
		'priority' => $priority_,
	)));
}
function pb_hook_add_action($action_name_, $func_, $priority_ = 10){
	pb_hook_set_action($action_name_, pb_random_string(10, PB_RANDOM_STRING_NUMLOWER), $func_, $priority_);
}

function pb_hook_remove_action($action_name_, $key_){
	global $_pb_hook_action_map;
	if(!isset($_pb_hook_action_map[$action_name_])) return;

	pb_hook_add_action($action_name_, $func_, $priority_, $key_);
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

function pb_hook_get_filter($filter_name_, $key_){
	global $_pb_hook_filter_map;

	if(!isset($_pb_hook_filter_map[$filter_name_])) return null;

	foreach($_pb_hook_filter_map[$filter_name_] as $index_ => $filter_data_){
		if($filter_data_['key'] === $converted_key_){
			return $filter_data_;
		}
	}
	return null;
}

function pb_hook_set_filter($fitler_name_, $key_, $func_, $priority_ = 10){
	if(strpos($fitler_name_, "|") !== false){
		$fitler_name_ = explode("|", $fitler_name_);
		foreach($fitler_name_ as $a_){
			pb_hook_set_filter($a_, $key_, $func_, $priority_);
		}

		return;
	}
	
	global $_pb_hook_filter_map;

	if(!isset($_pb_hook_filter_map[$fitler_name_])) $_pb_hook_filter_map[$fitler_name_] = array();


	foreach($_pb_hook_filter_map[$fitler_name_] as $index_ => $fitler_data_){
		if($fitler_data_['key'] === $key_){
			array_splice($_pb_hook_filter_map[$fitler_name_], $index_, 1);
			break;
		}
	}

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
		'key' => $key_,
		'priority' => $priority_,
	)));
}

function pb_hook_add_filter($fitler_name_, $func_, $priority_ = 10){
	pb_hook_set_filter($fitler_name_, pb_random_string(10, PB_RANDOM_STRING_NUMLOWER), $func_, $priority_);
}
function pb_hook_apply_filters($fitler_name_){
	global $_pb_hook_filter_map;

	$args_ = func_get_args();
	$args_ = array_slice($args_, 1);

	if(count($args_) <= 0) return;

	$filter_list_ = isset($_pb_hook_filter_map[$fitler_name_]) ? $_pb_hook_filter_map[$fitler_name_] : array();

	foreach($filter_list_ as $filter_data_){
		if(is_callable($filter_data_['func'])){
			$args_[0] = call_user_func_array($filter_data_['func'], $args_);	
		}else{
			$args_[0] = $filter_data_['func'];
		}
	}

	return $args_[0];
}

?>