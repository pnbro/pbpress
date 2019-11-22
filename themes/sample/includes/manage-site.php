<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_sample_theme_hook_register_manage_site_menu_list($results_){
	
	$results_['샘플테마설정'] = array(
		'name' => '샘플테마설정',
		'renderer' => '_pb_sample_theme_hook_render_manage_site',
	);
	return $results_;
}
pb_hook_add_filter('pb-admin-manage-site-menu-list', "_pb_sample_theme_hook_register_manage_site_menu_list");

function _pb_sample_theme_hook_render_manage_site($menu_data_){
	$menu_list_ = pb_menu_list();
	$sample_theme_menu_id_ = pb_option_value("sample_theme_menu_id");
	?>

	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">기본설정</h3>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<label>메인메뉴</label>
				<select class="form-control" name="sample_theme_menu_id">
					<option value="">-메뉴선택-</option>
					<?php foreach($menu_list_ as $menu_data_){ ?>
						<option value="<?=$menu_data_['id']?>" <?=pb_selected($sample_theme_menu_id_, $menu_data_['id'])?> ><?=$menu_data_['title']?></option>
					<?php } ?>
				</select>
			</div>	

		</div>
	</div>

			

	<?php
}

function _pb_sample_theme_api_hook_update_site_settings($settings_data_){
	pb_option_update('sample_theme_menu_id', $settings_data_['sample_theme_menu_id']);
}
pb_hook_add_action('pb-admin-update-site-settings', "_pb_sample_theme_api_hook_update_site_settings");


?>