<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_CLOCK_PATH', dirname(__FILE__)."/");
define('PB_CLOCK_URL', PB_PLUGINS_URL . str_replace(PB_PLUGINS_PATH, "", PB_CLOCK_PATH));



function _pb_clock_hook_register_manage_site_menu_list($results_){
	
	$results_['manage-pb-clock'] = array(
		'name' => '시계설정',
		'renderer' => '_pb_clock_hook_render_manage_site',
	);
	return $results_;
}
pb_hook_add_filter('pb-admin-manage-site-menu-list', "_pb_clock_hook_register_manage_site_menu_list");

function _pb_clock_hook_render_manage_site($menu_data_){
	$menu_list_ = pb_menu_list();
	$pb_clock_format_ = pb_option_value("pb_clock_format", "HH:mm:ss");
	?>

	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">시계설정</h3>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<label>포멧</label>
				<input type="text" name="pb_clock_format" value="<?=$pb_clock_format_?>" placeholder="ex> HH:mm:ss" class="form-control">
			</div>	

		</div>
	</div>

	<?php
}

function _pb_clock_api_hook_update_site_settings($settings_data_){
	pb_option_update('pb_clock_format', $settings_data_['pb_clock_format']);
}
pb_hook_add_action('pb-admin-update-site-settings', "_pb_clock_api_hook_update_site_settings");


function pb_clock_plugin_library(){
	?>
	<link rel="stylesheet" type="text/css" href="<?=PB_CLOCK_URL?>styles.css">
	<script type="text/javascript" src="<?=PB_CLOCK_URL?>functions.js"></script>
	<?php
}

function pb_clock_plugin_add_element(){
	$pb_clock_format_ = pb_option_value("pb_clock_format", "HH:mm:ss");
	?><span class="" id="pb-admin-clock" data-clock-format="<?=$pb_clock_format_?>"></span><?php
}

pb_hook_add_action('pb_admin_head', 'pb_clock_plugin_library');
pb_hook_add_action('pb-admin-header-right-before', 'pb_clock_plugin_add_element');

?>