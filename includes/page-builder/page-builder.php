<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_PAGE_BUILDER_VERSION', "1.1.1");
define('PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MIN', "0.0.1");
define('PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MAX', "1.1.1");

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
	$xml_instance_ = simplexml_load_string($xml_string_);

	$root_node_name_ = $xml_instance_->getName();
	if($root_node_name_ !== "pbpagebuilder"){
		return new PBError(-1, "PBPageBuilder 문서형식이 아닙니다.", "문서형식오류");
	}
	$root_attrs_ = $xml_instance_->attributes();
	$version_ = (string)$root_attrs_["version"];

	$version_check_min_ = version_compare($version_, PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MIN, ">=");
	$version_check_max_ = version_compare($version_, PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MAX, "<=");

	if(!$version_check_min_ || !$version_check_max_){
		return new PBError(-3, "문서 버젼이 현재버젼의  PBPageBuilder와 호환되지 않습니다", "문서호환성오류");	
	}

	if(!isset($xml_instance_->settings) || !isset($xml_instance_->pagecontent)){
		return new PBError(-6, "필수노드가 누락되었습니다.", "문서형식오류");		
	}

	$settings_ = $xml_instance_->settings;
		
	$results_ = array();

	$results_['settings'] = array(
		"style" => (string)$settings_->style,
		"script" => (string)$settings_->script,
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
	call_user_func_array(array($element_class_, "render"), array($element_data_, $element_data_['elementcontent']));
} ?>

<script type="text/javascript"><?=pb_hook_apply_filters('pb_page_builder_global_script',$settings_['script'], $builder_data_)?></script>

	<?php
}

function pb_page_builder($content_ = null, $data_ = array()){
	global $pb_config, $pb_page_builder_admin_initialized;

	$builder_id_ = isset($data_['id']) ? $data_['id'] : "pb-page-builder-".pb_random_string(5);

	if(!$pb_page_builder_admin_initialized){ 
		$element_map_ = pb_page_builder_elements();
	?>
<script type="text/javascript">
window.pb_page_builder_version = "<?=PB_PAGE_BUILDER_VERSION?>";
window.pbpage_builder_element_map = <?=json_encode($element_map_)?>;
</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/page-builder/editor/pb.page-builder.js?version=<?=PB_PAGE_BUILDER_VERSION?>"></script>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">

<?php
		pb_hook_do_action('pb_page_builder_admin_initialize');
		$pb_page_builder_admin_initialized = true;
	}

?>
<div class="pb-page-builder" id="<?=$builder_id_?>">
	
	<div class="page-builder-navbar"><div class="wrap">
		<div class="col-left">
			<img src="<?=PB_LIBRARY_URL?>img/page-builder/icon.png" class="logo-image">
		</div>
		<div class="col-right">
			<a href="" data-element-add-element-btn class="btn btn-default add-element-btn"><i class="icon material-icons">add_circle</i>요소추가</a>
			<a href="" data-element-setting-btn class="icon-link page-settings-btn"><i class="icon material-icons">settings</i></a>
			<a href="" data-fullscreen-btn class="icon-link fullscreen-btn">
				<i class="icon material-icons on">fullscreen</i>
				<i class="icon material-icons off">fullscreen_exit</i>
			</a>

		</div>
	</div></div>

	<div class="element-content-list empty" data-children-frame data-page-element-item="document">
		<a data-add-element-btn="prepend" class="add-element-btn prepend" href=""><i class="material-icons icon">add_box</i> 요소추가</a>
		<a data-add-element-btn="append" class="add-element-btn append" href=""><i class="material-icons icon">add_box</i> 요소추가</a>
	</div>

	<div class="copyrights">© 2019 Paul&Bro Company All Rights Reserved. v<?=PB_PAGE_BUILDER_VERSION?></div>

</div>
<script type="text/xmldata" id="<?=$builder_id_?>-defaults"><?=htmlentities($content_, null, $pb_config->charset)?></script>

<script type="text/javascript">
jQuery(document).ready(function(){
	window._pbpagebuilder_page_settings_modal_module = $("#pb-page-builder-page-settings-modal").pb_page_builder_page_settings_modal();
	window._pbpagebuilder_element_picker_modal_module = $("#pb-page-builder-element-picker-modal").pb_page_builder_element_picker_modal();
	window._pbpagebuilder_element_edit_modal_module = $("#pb-page-builder-element-edit-modal").pb_page_builder_element_edit_modal();
	var page_builder_ = $("#<?=$builder_id_?>").pb_page_builder();

	var default_content_ = $('<textarea />').html($("#<?=$builder_id_?>-defaults").html()).text();
	page_builder_.apply_xml(default_content_);
});
</script>

<?php 
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-element.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-ajax.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-builtin.php');

?>