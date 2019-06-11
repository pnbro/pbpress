<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_OPTION_THEME_NAME", "theme");

function pb_theme_list(){
	global $pb_theme_list;

	if(isset($pb_theme_list)) return $pb_theme_list;

	$pb_theme_list = array();

	foreach(glob(PB_DOCUMENT_PATH . "themes/**") as $theme_){
		$theme_slug_ = basename($theme_);

		$theme_info_ = null;

		if(file_exists($theme_."/theme-info.json")){
			$theme_info_ = json_decode(file_get_contents($theme_."/theme-info.json"), true);
		}

		if(!isset($theme_info_)){
			$theme_info_ = array(
				'name' => $theme_slug_,
				'desc' => $theme_slug_,
			);
		}

    	$pb_theme_list[$theme_slug_] = $theme_info_;
	}
	return $pb_theme_list;
}

function pb_switch_theme($theme_){
	pb_hook_apply_filters('pb_switch_theme_before', $theme_);
	pb_option_update(PB_OPTION_THEME_NAME, $theme_);

	global $_pb_current_theme;
	$_pb_current_theme = null;

	pb_hook_apply_filters('pb_switch_theme_after', $theme_);
}

function pb_current_theme(){
	global $_pb_current_theme;

	if(strlen($_pb_current_theme)) return $_pb_current_theme;

	$theme_ = pb_option_value(PB_OPTION_THEME_NAME);
	$_pb_current_theme = $theme_;
	return $_pb_current_theme;
}

function pb_current_theme_path(){
	return PB_DOCUMENT_PATH."themes/".pb_current_theme()."/";
}

function pb_current_theme_url(){
	return PB_DOCUMENT_URL."themes/".pb_current_theme()."/";	
}

function pb_theme_header($header_name_ = null){
	if(strlen($header_name_)){
		$header_name_ = "header-".$header_name_;
	}else{
		$header_name_ = "header";
	}

	include(PB_DOCUMENT_PATH."themes/".pb_current_theme()."/{$header_name_}.php");
}

function pb_theme_footer($footer_name_ = null){
	if(strlen($footer_name_)){
		$footer_name_ = "footer-".$footer_name_;
	}else{
		$footer_name_ = "footer";
	}
	include(PB_DOCUMENT_PATH."themes/".pb_current_theme()."/{$footer_name_}.php");	
}

function pb_theme_install_tables(){
	global $pbdb;
	$query_list_ = pb_hook_apply_filters("pb_install_theme_tables", array());

	foreach($query_list_ as $query_){
		$pbdb->query($query_);
	}

	pb_hook_do_action("pb_installed_theme_tables");
	$pbdb->commit();
}
pb_hook_add_action('pb_installed_tables', "pb_theme_install_tables");
pb_hook_add_action('pb_switch_theme_after', "pb_theme_install_tables");



function _pb_theme_hook_register_manage_site_menu_list($results_){
	$results_['theme'] = array(
		'name' => '테마설정',
		'renderer' => '_pb_theme_hook_render_manage_site',
	);
	return $results_;
}
pb_hook_add_filter('pb-admin-manage-site-menu-list', "_pb_theme_hook_register_manage_site_menu_list");

function _pb_theme_hook_render_manage_site($menu_data_){

	$site_theme_ = pb_option_value("theme");
	$theme_list_ = pb_theme_list();

	?>
	
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-site-theme.css">
	<div class="manage-site-form-panel panel panel-default manage-site-theme-panel" id="pb-manage-site-theme-panel">
		<div class="panel-heading">
			<h3 class="panel-title">테마설정</h3>
		</div>
		<div class="panel-body">
			<input type="hidden" name="before_theme" value="<?=$site_theme_?>">
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
					<a class="theme-item <?=$theme_key_ === $site_theme_ ? "active" : ""?>" href="javascript:manage_site_change_theme('<?=$theme_key_?>');" data-theme-item="<?=$theme_key_?>">
						<i class="active-icon material-icons">check</i>
						<div class="wrap">
							<div class="col-theme-screenshot">
								<div class="theme-screenshot" style="background-image:url('<?=$theme_screen_url_?>')"></div>
							</div>
							<div class="col-theme-info">
								<div class="name"><?=$theme_data_['name']?></div>
								<div class="desc"><?=$theme_data_['desc']?></div>
								<div class="author"><?=isset($theme_data_['author']) ? "by ".$theme_data_['author'] : "Unknown"?></div>
							</div>
						</div>
					</a>
				<?php } ?>
			</div>

		</div>
	</div>
	<script type="text/javascript">
		function manage_site_change_theme(theme_){
			$("[data-theme-item]").toggleClass("active", false);
			$("[data-theme-item='"+theme_+"']").toggleClass("active", true);
			// console.log($("[data-theme-item='"+theme_+"']"));

			var theme_panel_ = $("#pb-manage-site-theme-panel");
			var before_theme_input_ = theme_panel_.find("[name='before_theme']");
			var target_theme_input_ = theme_panel_.find("[name='theme']");

			target_theme_input_.val(theme_);
		}
	</script>
	<?php 
}

function _pb_theme_hook_update_site_settings($settings_data_){
	$before_theme_ = pb_current_theme();
	$target_theme_ = $settings_data_['theme'];

	pb_option_update("theme", $target_theme_);

	if($before_theme_ !== $target_theme_){
		pb_switch_theme($target_theme_);	
	}
}
pb_hook_add_action('pb-admin-update-site-settings', "_pb_theme_hook_update_site_settings");

?>