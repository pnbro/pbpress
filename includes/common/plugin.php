<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_PLUGINS_PATH", PB_DOCUMENT_PATH.'plugins/');
define("PB_PLUGINS_URL", PB_DOCUMENT_URL.'plugins/');

define("PB_OPTION_ACTIVATED_PLUGINS_NAME", "activated_plugins");

function pb_activated_plugins($with_data_ = false){
	$temp_ = pb_clob_option_value(PB_OPTION_ACTIVATED_PLUGINS_NAME, array());

	if(!$with_data_) return $temp_;

	$results_ = array();
	$base_list_ = pb_plugin_list();

	foreach($temp_ as $slug_){
		$results_[$slug_] = $base_list_[$slug_];
	}

	return $results_;
}
function pb_is_plugin_actived($slug_){
	$activated_plugins_ = pb_activated_plugins();
	return in_array($slug_, $activated_plugins_);
}

function pb_active_plugin($slug_){
	if(pb_is_plugin_actived($slug_)) return false;
	pb_hook_do_action('pb_active_plugin', $slug_);

	$activated_plugins_ = pb_activated_plugins();
	$activated_plugins_[] = $slug_;
	pb_clob_option_update(PB_OPTION_ACTIVATED_PLUGINS_NAME, $activated_plugins_);

	global $pb_plugin_list;
	$pb_plugin_list = null;

	pb_hook_do_action('pb_plugin_activated', $slug_);
}

function pb_deactive_plugin($slug_){
	if(!pb_is_plugin_actived($slug_)) return false;
	pb_hook_do_action('pb_deactive_plugin', $slug_);

	$activated_plugins_ = pb_activated_plugins();
	$index_ = array_search($slug_, $activated_plugins_);
	array_splice($activated_plugins_, $index_, 1);
	pb_clob_option_update(PB_OPTION_ACTIVATED_PLUGINS_NAME, $activated_plugins_);

	global $pb_plugin_list;
	$pb_plugin_list = null;

	pb_hook_do_action('pb_plugin_deactivated', $slug_);
}

function pb_plugin_list(){
	global $pb_plugin_list;

	if(isset($pb_plugin_list)) return $pb_plugin_list;

	$pb_plugin_list = array();
	$activated_plugins_ = pb_activated_plugins();

	foreach(glob(PB_PLUGINS_PATH . "**") as $plugin_){
		$plugin_slug_ = basename($plugin_);
		$plugin_info_ = null;

		if(file_exists($plugin_."/plugin-info.json")){
			$plugin_info_ = json_decode(file_get_contents($plugin_."/plugin-info.json"), true);
		}

		if(!isset($plugin_info_)){
			$plugin_info_ = array(
				'name' => $plugin_slug_,
				'desc' => $plugin_slug_,
			);
		}
		$plugin_info_['author'] = isset($plugin_info_['author']) ? $plugin_info_['author'] : null;
		$plugin_info_['version'] = isset($plugin_info_['version']) ? $plugin_info_['version'] : null;
		$plugin_info_['activated'] = in_array($plugin_slug_, $activated_plugins_);

    	$pb_plugin_list[$plugin_slug_] = $plugin_info_;
	}
	return $pb_plugin_list;
}

include(PB_DOCUMENT_PATH . 'includes/common/plugin-builtin.php');

?>