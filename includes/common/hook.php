<?

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $_pb_hook_action_map, $_pb_hook_filter_map;

$_pb_hook_action_map = array();
$_pb_hook_filter_map = array();

function __pb_hook_sort_func($a_, $b_){
	return ($a_['priority'] < $b_['priority'] ? -1 : 1);
}

function _pb_hook_sort_map(&$map_, $key_name_){
	if(!isset($map_[$key_name_])) return;

	$data_list_ = &$map_[$key_name_];
	usort($data_list_, "__pb_hook_sort_func");
}

function pb_hook_add_action($action_name_, $func_, $priority_ = 10){
	global $_pb_hook_action_map;

	if(!isset($_pb_hook_action_map[$action_name_])) $_pb_hook_action_map[$action_name_] = array();

	$_pb_hook_action_map[$action_name_][] = array(
		'func' => $func_,
		'priority' => $func_,
	);

	_pb_hook_sort_map($_pb_hook_action_map, $action_name_);
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

	$_pb_hook_filter_map[$fitler_name_][] = array(
		'func' => $func_,
		'priority' => $priority_,
	);

	_pb_hook_sort_map($_pb_hook_filter_map, $fitler_name_);
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