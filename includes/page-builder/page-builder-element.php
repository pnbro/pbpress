<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_builder_elements(){
	global $pb_page_builder_elements;

	if(!isset($pb_page_builder_elements)){
		$pb_page_builder_elements = array();
	}
	return pb_hook_apply_filters("pb_page_builder_elements", $pb_page_builder_elements);
}
function pb_page_builder_element_class($key_){
	global $pb_page_builder_element_classes;
	if(isset($pb_page_builder_element_classes[$key_])) return $pb_page_builder_element_classes[$key_];
	return null;
}
function pb_page_builder_add_element($key_, $data_){
	global $pb_page_builder_elements, $pb_page_builder_element_classes;

	if(!isset($pb_page_builder_elements)){
		$pb_page_builder_elements = array();
		$pb_page_builder_element_classes = array();
	}

	$pb_page_builder_elements[$key_] = $data_;
	$pb_page_builder_element_classes[$key_] = new $data_['element_object']($key_);
}

function pb_page_builder_element_parse($xml_string_){

}

global $pb_page_builder_element_edit_categories, $pb_page_builder_element_edit_category_funcs;
$pb_page_builder_element_edit_categories = array();
$pb_page_builder_element_edit_category_funcs = array();

function pb_page_builder_element_edit_categories(){
	global $pb_page_builder_element_edit_categories;
	return pb_hook_apply_filters('pb_page_builder_element_edit_categories', $pb_page_builder_element_edit_categories);
}
function pb_page_builder_element_add_edit_category($key_, $title_, $priority_ = 10){
	global $pb_page_builder_element_edit_categories, $pb_page_builder_element_edit_category_funcs;

	if(!isset($pb_page_builder_element_edit_category_funcs[$key_])){
		$pb_page_builder_element_edit_category_funcs[$key_] = array();
	}

	$map_count_ = count($pb_page_builder_element_edit_categories);
	$insert_index_ = $map_count_;
	for($row_index_=0;$row_index_<$map_count_;++$row_index_){
		$target_item_ = $pb_page_builder_element_edit_categories[$row_index_];

		if($target_item_['priority'] > $priority_){
			$insert_index_ = $row_index_;
			break;
		}
	}

	array_splice($pb_page_builder_element_edit_categories, $insert_index_, 0, array(array(
		'key' => $key_,
		'title' => $title_,
	)));
}
function pb_page_builder_element_add_edit_category_function($key_, $func_, $priority_ = 10){
	global $pb_page_builder_element_edit_category_funcs;	
	if(!isset($pb_page_builder_element_edit_category_funcs[$key_])) return;

	$map_count_ = count($pb_page_builder_element_edit_category_funcs[$key_]);
	$insert_index_ = $map_count_;
	for($row_index_=0;$row_index_<$map_count_;++$row_index_){
		$target_item_ = $pb_page_builder_element_edit_category_funcs[$key_][$row_index_];

		if($target_item_['priority'] > $priority_){
			$insert_index_ = $row_index_;
			break;
		}
	}

	array_splice($pb_page_builder_element_edit_category_funcs[$key_], $insert_index_, 0, array($func_));
}

pb_page_builder_element_add_edit_category('common', '기본설정', 1);
pb_page_builder_element_add_edit_category_function('common', 'pb_page_builder_element_edit_category_common');

function pb_page_builder_element_edit_category_common($element_data_){

	$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
	$classes_ = isset($element_data_['classes']) ? $element_data_['classes'] : null;
	?>
	<div class="form-group">
		<label>ID</label>
		<input type="text" name="id" placeholder="ID 입력" class="form-control" value="<?=$id_?>">

		<div class="help-block with-errors"></div>
		<div class=""></div>
	</div>

	<div class="form-group">
		<label>클래스</label>
		<input type="text" name="classes" placeholder="클래스 입력" class="form-control" value="<?=$classes_?>">

		<div class="help-block with-errors"></div>
		<div class=""></div>
	</div>

	<?php
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/class.PBPageBuilderElement.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/common.php');

?>