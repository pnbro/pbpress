<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


function _pb_page_builder_hook_register_manage_site_menu_list($results_){
	$results_['page-builder'] = array(
		'name' => __('페이지빌더'),
		'renderer' => '_pb_page_builder_hook_manage_site',
	);
	return $results_;
}
pb_hook_add_filter('pb-admin-manage-site-menu-list', "_pb_page_builder_hook_register_manage_site_menu_list");

function _pb_page_builder_hook_manage_site($menu_data_){
	?>

	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?=__('페이지빌더설정')?></h3>
		</div>
		<div class="panel-body">
			<h3><?=__('반응형기준')?> <small><?=__('정수만 입력하세요 픽셀로 계산됩니다.')?></small></h3>
			<div class="row">
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('모바일')?></label>
					<input type="text"  value="<?=pb_option_value('pb_page_builder_screen_xs', "480")?>" class="form-control" name="pb_page_builder_screen_xs">
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('타블렛')?></label>
					<input type="text"  value="<?=pb_option_value('pb_page_builder_screen_sm', "768")?>" class="form-control" name="pb_page_builder_screen_sm">
				</div></div>

				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('PC(작은화면)')?></label>
					<input type="text"  value="<?=pb_option_value('pb_page_builder_screen_md', "992")?>" class="form-control" name="pb_page_builder_screen_md">
				</div></div>

				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('PC(큰화면)')?></label>
					<input type="text"  value="<?=pb_option_value('pb_page_builder_screen_lg', "1200")?>" class="form-control" name="pb_page_builder_screen_lg">
				</div></div>
			</div>

			<h3><?=__('적재요소관련')?> <small><?=__('정수만 입력하세요 픽셀로 계산됩니다.')?></small></h3>
			<div class="form-group">
				<label><?=__('컨테이너, 컬럼 안쪽여백')?></label>
				<input type="text"  value="<?=pb_option_value('pb_page_builder_default_padding', "20")?>" class="form-control" name="pb_page_builder_default_padding">
			</div>

		</div>
	</div>

	<?php 
}

function _pb_page_builder_hook_update_site_settings($settings_data_){
	pb_option_update("pb_page_builder_screen_xs", $settings_data_['pb_page_builder_screen_xs']);
	pb_option_update("pb_page_builder_screen_sm", $settings_data_['pb_page_builder_screen_sm']);
	pb_option_update("pb_page_builder_screen_md", $settings_data_['pb_page_builder_screen_md']);
	pb_option_update("pb_page_builder_screen_lg", $settings_data_['pb_page_builder_screen_lg']);
	pb_option_update("pb_page_builder_default_padding", $settings_data_['pb_page_builder_default_padding']);
}
pb_hook_add_action('pb-admin-update-site-settings', "_pb_page_builder_hook_update_site_settings");


?>