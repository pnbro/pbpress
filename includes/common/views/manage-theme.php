<?php 	
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	// $menu_list_ = pb_hook_apply_filters('pb-admin-manage-site-menu-list', array());

	$site_theme_ = pb_option_value("theme");
	$theme_list_ = pb_theme_list();

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-theme.css">
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-theme.js"></script>
	
<div class="manage-theme-frame"><form id="pb-manage-theme-form" method="POST">

	<h3><?=__('테마설정')?></h3>

	<div class="manage-form-panel panel panel-default manage-theme-panel" id="pb-manage-theme-panel">
		<div class="panel-heading">
			<h3 class="panel-title"><?=__('테마설정')?></h3>
		</div>
		<div class="panel-body">
			<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_manage_theme")?>">
			<input type="hidden" name="theme" value="<?=$site_theme_?>">

			<div class="theme-list" id="pb-manage-site-theme-list">

				<?php foreach($theme_list_ as $theme_key_ => $theme_data_){

					$theme_screen_url_ = null;
					if(file_exists(PB_DOCUMENT_PATH."themes/".$theme_key_."/screenshot.jpg")){
							$theme_screen_url_ = PB_DOCUMENT_URL."themes/".$theme_key_."/screenshot.jpg";
						}else{
							$theme_screen_url_ = PB_LIBRARY_URL."img/theme-placeholder.jpg";
						}
				?>
					<a class="theme-item <?=$theme_key_ === $site_theme_ ? "active" : ""?>" href="javascript:_pb_manage_theme_change_theme('<?=$theme_key_?>');" data-theme-item="<?=$theme_key_?>">
						<i class="active-icon material-icons">check</i>
						<div class="wrap">
							<div class="col-theme-screenshot">
								<div class="theme-screenshot" style="background-image:url('<?=$theme_screen_url_?>')"></div>
							</div>
							<div class="col-theme-info">
								<div class="name"><?=$theme_data_['name']?><?php if(strlen($theme_data_['version'])){ ?>
										<small class="version">v<?=$theme_data_['version']?></small>
									<?php } ?>
								</div>
								<div class="desc"><?=$theme_data_['desc']?></div>
								<div class="author"><?=isset($theme_data_['author']) ? "by ".$theme_data_['author'] : "Unknown"?></div>
							</div>
						</div>
					</a>
				<?php } ?>
			</div>

		</div>
	</div>
	
	<hr>
	<div class="text-center">
		<button type="submit" class="btn btn-primary btn-lg"><?=__('변경사항 저장')?></button>
	</div>
	

</form></div>