<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('__PB_PAGE_BUILDER_VERSION', "3.1.0");

global $pb_config;

if($pb_config->is_devmode()){
	define('PB_PAGE_BUILDER_VERSION', __PB_PAGE_BUILDER_VERSION."_".time());
}else{
	define('PB_PAGE_BUILDER_VERSION', __PB_PAGE_BUILDER_VERSION);
}

define('PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MIN', "3.0.0");
define('PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MAX', PB_PAGE_BUILDER_VERSION);

function _pb_page_builder_recursive_parse_inner($element_){
	$element_map_ = pb_page_builder_elements();
	global $pb_page_builder_element_classes;

	$tmp_element_attributes_ = $element_->attributes();
	$element_name_ = null;

	foreach($tmp_element_attributes_ as $key_ => $value_){
		if($key_ === "name"){
			$element_name_ = (string)$value_;
		}
	}

	$tmp_element_properties_ = $element_->property;
	$element_properties_ = array();

	foreach($tmp_element_properties_ as $key_ => $value_){
		$element_properties_[(string)$value_->attributes()->name] = (string)$value_;
	}

	if(isset($element_map_[$element_name_]['loadable']) && $element_map_[$element_name_]['loadable']){
		$inner_elements_ = array();

		if(count($element_->elementcontent) > 0){
			foreach($element_->elementcontent->element as $inner_element_){
				$inner_elements_[] = _pb_page_builder_recursive_parse_inner($inner_element_);
			}	
		}

		return array(
			'name' => $element_name_,
			'properties' => $element_properties_,
			'elementcontent' => $inner_elements_,
		);
	}else{
		return array(
			'name' => $element_name_,
			'properties' => $element_properties_,
			'elementcontent' => (string)$element_->elementcontent,
		);
	}
}

function pb_page_builder_parse_xml($xml_string_){
	$xml_instance_ = @simplexml_load_string($xml_string_);

	if(!$xml_instance_){
		return new PBError(-9, __("잘못된 XML 형식입니다."), __("문서형식오류"));		
	}

	$root_node_name_ = $xml_instance_->getName();
	if($root_node_name_ !== "pbpagebuilder"){
		return new PBError(-1, __("PBPageBuilder 문서형식이 아닙니다."), __("문서형식오류"));
	}
	$root_attrs_ = $xml_instance_->attributes();
	$version_ = (string)$root_attrs_["version"];

	$version_check_min_ = version_compare($version_, PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MIN, ">=");
	$version_check_max_ = version_compare($version_, PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MAX, "<=");

	if(!$version_check_min_ || !$version_check_max_){
		return new PBError(-3, __("문서 버젼이 현재버젼의  PBPageBuilder와 호환되지 않습니다"), __("문서호환성오류"));
	}

	if(!isset($xml_instance_->settings) || !isset($xml_instance_->pagecontent)){
		return new PBError(-6, __("필수노드가 누락되었습니다."), __("문서형식오류"));		
	}

	$settings_ = $xml_instance_->settings;
		
	$results_ = array();
	$results_['settings'] = array(
		"style" => (string)(isset($settings_->style) ? $settings_->style : $settings_->pstyle),
		"script" => (string)(isset($settings_->script) ? $settings_->script : $settings_->pscript),
	);

	$page_contents_ = array();

	foreach($xml_instance_->pagecontent->element as $element_){
		$page_contents_[] = _pb_page_builder_recursive_parse_inner($element_);
	}

	$results_['elementcontent'] = $page_contents_;

	return $results_;
}

function pb_page_builder_render($builder_data_){
	$settings_ = $builder_data_['settings'];
	$page_contents_ = $builder_data_['elementcontent'];

	$element_map_ = pb_page_builder_elements();
	global $pb_page_builder_element_classes;

	?>
<style type="text/css"><?=pb_hook_apply_filters('pb_page_builder_global_style',$settings_['style'], $builder_data_)?></style>

<?php foreach($page_contents_ as $element_data_){
	$element_class_ = $pb_page_builder_element_classes[$element_data_['name']];
	call_user_func_array(array($element_class_, "_render"), array($element_data_, $element_data_['elementcontent']));
} ?>

<script type="text/javascript"><?=pb_hook_apply_filters('pb_page_builder_global_script',$settings_['script'], $builder_data_)?></script>

	<?php
}

function pb_page_builder($content_ = null, $data_ = array()){
	global $pb_config, $pb_page_builder_admin_initialized;

	$builder_id_ = isset($data_['id']) ? $data_['id'] : "pb-page-builder-".pb_random_string(5);
	$elements_ = isset($data_['elements']) ? $data_['elements'] : null;
	$exclude_elements_ = isset($data_['excludes']) ? $data_['excludes'] : array();
	$exclude_elements_ = pb_hook_apply_filters('pb_page_builder_excludes', $exclude_elements_, $builder_id_);

	if(empty($elements_)){
		$elements_ = array();
		$temp_elements_ = pb_page_builder_elements($exclude_elements_);

		foreach($temp_elements_ as $key_ => $element_data_){
			$elements_[] = $key_;
		}
	}

	if(!$pb_page_builder_admin_initialized){ 
		$element_map_ = pb_page_builder_elements();
	?>
<script type="text/javascript">
window.pb_page_builder_version = "<?=PB_PAGE_BUILDER_VERSION?>";
window.pbpage_builder_element_map = <?=json_encode($element_map_)?>;
</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/page-builder/editor/pb.page-builder.js?version=<?=PB_PAGE_BUILDER_VERSION?>"></script>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">
<script type="text/javascript">
jQuery(document).ready(function(){
	window._pbpagebuilder_page_settings_modal_module = $("#pb-page-builder-page-settings-modal").pb_page_builder_page_settings_modal();
	window._pbpagebuilder_element_picker_modal_module = $("#pb-page-builder-element-picker-modal").pb_page_builder_element_picker_modal({
		elements : <?=isset($elements_) ? json_encode($elements_) : "null"?>
	});
	window._pbpagebuilder_element_edit_modal_module = $("#pb-page-builder-element-edit-modal").pb_page_builder_element_edit_modal();
});
</script>


<?php
		pb_hook_do_action('pb_page_builder_admin_initialize', $builder_id_);
		pb_hook_do_action('pb_page_builder_initialize', $builder_id_);
		$pb_page_builder_admin_initialized = true;
	}

?>
<div class="pb-page-builder" id="<?=$builder_id_?>">
	
	<div class="page-builder-navbar"><div class="wrap">
		<div class="col-left">
			<img src="<?=PB_LIBRARY_URL?>img/page-builder/icon.png" class="logo-image">
		</div>
		<div class="col-right">
			<a href="" data-element-add-element-btn class="btn btn-default add-element-btn"><i class="icon material-icons">add_circle</i><?=__('요소추가')?></a>
			<a href="" data-element-setting-btn class="icon-link page-settings-btn"><i class="icon material-icons">settings</i></a>
			<a href="" data-fullscreen-btn class="icon-link fullscreen-btn">
				<i class="icon material-icons on">fullscreen</i>
				<i class="icon material-icons off">fullscreen_exit</i>
			</a>

		</div>
	</div></div>

	<div class="element-content-list empty" data-children-frame data-page-element-item="document">
		<a data-add-element-btn="prepend" class="add-element-btn prepend" href=""><i class="material-icons icon">add_box</i> <?=__('요소추가')?></a>
		<a data-add-element-btn="append" class="add-element-btn append" href=""><i class="material-icons icon">add_box</i> <?=__('요소추가')?></a>
	</div>

	<div class="copyrights"><?=pb_hook_apply_filters('adminpage_footer_copyrights', '© 2019 Paul&Bro Company All Rights Reserved.')?> v<?=PB_PAGE_BUILDER_VERSION?></div>

</div>
<script type="text/xmldata" id="<?=$builder_id_?>-defaults"><?=htmlentities($content_, null, $pb_config->charset)?></script>

<script type="text/javascript">
jQuery(document).ready(function(){
	var page_builder_ = $("#<?=$builder_id_?>").pb_page_builder({
		elements : <?=isset($elements_) ? json_encode($elements_) : "null"?>
	});

	var default_content_ = $('<textarea />').html($("#<?=$builder_id_?>-defaults").html()).text();

	if(default_content_ && default_content_ !== ""){
		page_builder_.apply_xml(default_content_);	
	}
	
});
</script>

<?php 

	pb_hook_do_action('pb_page_builder_admin_initialized', $builder_id_);
	pb_hook_do_action('pb_page_builder_initialized', $builder_id_);
}

define('PB_PAGE_BUILDER_COMPILED_SLUG', '_page_builder_compiled.css');

global $_pb_page_builder_css_map;
$_pb_page_builder_css_map = array();

function pb_page_builder_css_add($file_path_){
	global $_pb_page_builder_css_map;
	$_pb_page_builder_css_map[] = array(
		'path' => $file_path_,
	);
}
function pb_page_builder_css_remove($file_path_){
	global $_pb_page_builder_css_map;

	$finded_index_ = array_search($file_path_, $_pb_page_builder_css_map);
	if($finded_index_ === FALSE) return;

	array_splice($_pb_page_builder_css_map, $finded_index_, 1);
}

function _pb_page_builder_compile_css_map(){
	$screen_xs_min_ = pb_option_value('pb_page_builder_screen_xs', 480);
	$screen_sm_min_ = pb_option_value('pb_page_builder_screen_sm', 768);
	$screen_md_min_ = pb_option_value('pb_page_builder_screen_md', 992);
	$screen_lg_min_ = pb_option_value('pb_page_builder_screen_lg', 1220);

	$screen_xs_max_ = $screen_sm_min_ - 1;
	$screen_sm_max_ = $screen_md_min_ - 1;
	$screen_md_max_ = $screen_lg_min_ - 1;

	$default_padding_ = pb_option_value('pb_page_builder_default_padding', 20);

	$style_data_ = array(
		'screen_xs_min' => $screen_xs_min_.'px',
		'screen_xs_max' => $screen_xs_max_.'px',
		'screen_sm_min' => $screen_sm_min_.'px',
		'screen_sm_max' => $screen_sm_max_.'px',
		'screen_md_min' => $screen_md_min_.'px',
		'screen_md_max' => $screen_md_max_.'px',
		'screen_lg_min' => $screen_lg_min_.'px',
		'default_padding' => $default_padding_.'px',
		'default_padding_double' => ($default_padding_ * 2).'px',
		'default_padding_half' => ($default_padding_ / 2).'px',
	);

	$style_data_ = pb_hook_apply_filters('pb_page_builder_compile_css_style_data', $style_data_);

	global $_pb_page_builder_css_map;
	if(!isset($_pb_page_builder_css_map)) $_pb_page_builder_css_map = array();

	$style_variables_string_ = "";

	foreach($style_data_ as $skey_ => $sval_){
		$style_variables_string_ .= "--pbuilder-{$skey_}: ".$sval_.";".PHP_EOL;
	}

	$style_variables_string_ = ":root{".PHP_EOL.$style_variables_string_."}".PHP_EOL;

	$css_string_ = $style_variables_string_;

	foreach($_pb_page_builder_css_map as $css_map_data_){
		$css_string_ .= @file_get_contents($css_map_data_['path']) . PHP_EOL;
	}

	$css_string_ = pb_hook_apply_filters('pb_page_builder_compile_css_string', $css_string_);

	foreach($style_data_ as $key_ => $value_){
		$css_string_ = str_replace("@@".$key_."@@", $value_, $css_string_);	
		$css_string_ = str_replace(urlencode("@@".$key_."@@"), urlencode($value_), $css_string_);	
	}

	preg_match_all('(\@\@\{[0-9\@a-zA-Z\_\-\+\ ]{1,}\}\@\@)', $css_string_, $regex_matches_);

	if(count($regex_matches_) > 0){
		foreach($regex_matches_[0] as $string_){
			$exp_ = ltrim($string_, "@@{");
			$exp_ = rtrim($exp_, "}@@");
		}
	}
		
	return $css_string_;
}

pb_rewrite_register(PB_PAGE_BUILDER_COMPILED_SLUG, array(
	"rewrite_handler" => "_pb_page_builder_rewrite_handler_for_compiled_css",
));
function _pb_page_builder_rewrite_handler_for_compiled_css(){
	$css_string_ = _pb_page_builder_compile_css_map();
	header("Content-type: text/css", true);
	echo $css_string_;
	pb_end();
}

function pb_page_builder_compiled_url(){
	return pb_home_url(PB_PAGE_BUILDER_COMPILED_SLUG);
}


pb_hook_add_action("pb_head", "_pb_page_builder_head_hook_for_css_map");
function _pb_page_builder_head_hook_for_css_map(){
	?>
	<link rel="stylesheet" type="text/css" href="<?=pb_page_builder_compiled_url()?>">
	<?php
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-element.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-ajax.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-builtin.php');
__iinclude(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-adminpage.php');

?>