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

	/* devplan002 part008 — 테마 옵션(로고/포인트컬러/푸터/SNS/히어로) 확장분 */
	$sample_theme_logo_text_ = pb_option_value("sample_theme_logo_text");
	$sample_theme_logo_image_ = pb_option_value("sample_theme_logo_image");
	$sample_theme_primary_color_ = pb_option_value("sample_theme_primary_color");
	$sample_theme_footer_copyright_ = pb_option_value("sample_theme_footer_copyright");
	$sample_theme_sns_facebook_ = pb_option_value("sample_theme_sns_facebook");
	$sample_theme_sns_instagram_ = pb_option_value("sample_theme_sns_instagram");
	$sample_theme_sns_youtube_ = pb_option_value("sample_theme_sns_youtube");
	$sample_theme_hero_title_ = pb_option_value("sample_theme_hero_title");
	$sample_theme_hero_subtitle_ = pb_option_value("sample_theme_hero_subtitle");
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

	<!-- devplan002 part008 — 브랜드 & 포인트 컬러 -->
	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">브랜드 &amp; 디자인</h3>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<label>로고 텍스트</label>
				<input type="text" class="form-control" name="sample_theme_logo_text" value="<?=htmlspecialchars($sample_theme_logo_text_)?>" placeholder="PBPress Sample">
				<p class="help-block">로고 이미지 URL이 비어있을 때 헤더에 표시할 텍스트입니다.</p>
			</div>
			<div class="form-group">
				<label>로고 이미지 URL</label>
				<input type="text" class="form-control" name="sample_theme_logo_image" value="<?=htmlspecialchars($sample_theme_logo_image_)?>" placeholder="https://.../logo.png">
				<p class="help-block">값이 있으면 텍스트 로고 대신 이미지로 표시됩니다.</p>
			</div>
			<div class="form-group">
				<label>포인트 컬러</label><br>
				<input type="color" name="sample_theme_primary_color" value="<?=strlen($sample_theme_primary_color_) ? htmlspecialchars($sample_theme_primary_color_) : '#088edb'?>">
				<p class="help-block">프론트 디자인 토큰 <code>--c-primary</code>에 즉시 반영됩니다.</p>
			</div>
		</div>
	</div>

	<!-- devplan002 part008 — 홈 히어로 문구 -->
	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">홈 히어로 문구</h3>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<label>히어로 타이틀</label>
				<input type="text" class="form-control" name="sample_theme_hero_title" value="<?=htmlspecialchars($sample_theme_hero_title_)?>" placeholder="비워두면 기본 문구 사용">
			</div>
			<div class="form-group">
				<label>히어로 서브타이틀</label>
				<input type="text" class="form-control" name="sample_theme_hero_subtitle" value="<?=htmlspecialchars($sample_theme_hero_subtitle_)?>" placeholder="비워두면 기본 문구 사용">
			</div>
		</div>
	</div>

	<!-- devplan002 part008 — 푸터 & SNS -->
	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">푸터 &amp; SNS</h3>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<label>푸터 카피라이트</label>
				<input type="text" class="form-control" name="sample_theme_footer_copyright" value="<?=htmlspecialchars($sample_theme_footer_copyright_)?>" placeholder="&copy; 2026 PBPress Sample Theme. All rights reserved.">
			</div>
			<div class="form-group">
				<label>Facebook URL</label>
				<input type="text" class="form-control" name="sample_theme_sns_facebook" value="<?=htmlspecialchars($sample_theme_sns_facebook_)?>" placeholder="https://facebook.com/...">
			</div>
			<div class="form-group">
				<label>Instagram URL</label>
				<input type="text" class="form-control" name="sample_theme_sns_instagram" value="<?=htmlspecialchars($sample_theme_sns_instagram_)?>" placeholder="https://instagram.com/...">
			</div>
			<div class="form-group">
				<label>YouTube URL</label>
				<input type="text" class="form-control" name="sample_theme_sns_youtube" value="<?=htmlspecialchars($sample_theme_sns_youtube_)?>" placeholder="https://youtube.com/...">
			</div>
		</div>
	</div>



	<?php
}

function _pb_sample_theme_api_hook_update_site_settings($settings_data_){
	pb_option_update('sample_theme_menu_id', $settings_data_['sample_theme_menu_id']);

	/* devplan002 part008 — 테마 옵션(로고/포인트컬러/푸터/SNS/히어로) 확장분 */
	pb_option_update('sample_theme_logo_text', $settings_data_['sample_theme_logo_text']);
	pb_option_update('sample_theme_logo_image', $settings_data_['sample_theme_logo_image']);
	pb_option_update('sample_theme_primary_color', $settings_data_['sample_theme_primary_color']);
	pb_option_update('sample_theme_footer_copyright', $settings_data_['sample_theme_footer_copyright']);
	pb_option_update('sample_theme_sns_facebook', $settings_data_['sample_theme_sns_facebook']);
	pb_option_update('sample_theme_sns_instagram', $settings_data_['sample_theme_sns_instagram']);
	pb_option_update('sample_theme_sns_youtube', $settings_data_['sample_theme_sns_youtube']);
	pb_option_update('sample_theme_hero_title', $settings_data_['sample_theme_hero_title']);
	pb_option_update('sample_theme_hero_subtitle', $settings_data_['sample_theme_hero_subtitle']);
}
pb_hook_add_action('pb-admin-update-site-settings', "_pb_sample_theme_api_hook_update_site_settings");


?>