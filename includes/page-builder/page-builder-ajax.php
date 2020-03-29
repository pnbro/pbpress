<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_page_builder_check_children_exp($element_id_, $exp_){
	$result_ = (in_array($element_id_, $exp_) || in_array("*", $exp_)) && !in_array("!".$element_id_, $exp_);
	return $result_;
}

function _pb_page_builder_parse_children_exp($exp_){
	$results_ = array();	
	$temp_page_builder_elements_ = pb_page_builder_elements();

	foreach($temp_page_builder_elements_ as $element_id_ => $element_data_){
		if(_pb_page_builder_check_children_exp($element_id_, $exp_)){
			$results_[$element_id_] = $element_data_;
		}
	}

	return $results_;
}

function _pb_page_builder_ajax_load_elements(){
	$options_ = _POST('options');
	$parent_id_ = isset($options_['parent']) ? $options_['parent'] : null;
	$keyword_ = isset($options_['keyword']) ? $options_['keyword'] : null;
	$elements_ = isset($options_['included_elements']) ? $options_['included_elements'] : null;

	if(gettype($elements_) !== 'array'){
		if(strlen($elements_)) $elements_ = explode(",", $elements_);
		else $elements_ = array();
	}

	$temp_page_builder_elements_ = pb_page_builder_elements();
	$page_builder_elements_ = array();

	$parent_element_data_ = strlen($parent_id_) && isset($temp_page_builder_elements_[$parent_id_]) ? $temp_page_builder_elements_[$parent_id_] : null;

	if(isset($parent_element_data_) && isset($parent_element_data_['children'])){
		$temp_page_builder_elements_ = _pb_page_builder_parse_children_exp($parent_element_data_['children']);
	}

	foreach($temp_page_builder_elements_ as $key_ => $page_builder_element_){
		if(strlen($keyword_)){
			if(strpos($page_builder_element_['name'], $keyword_) === false) continue;
		}
		if(count($elements_) > 0){
			if(in_array($key_, $elements_) === false) continue;
		}

		if(isset($page_builder_element_['parent'])){
			if(!_pb_page_builder_check_children_exp($parent_id_, $page_builder_element_['parent'])) continue;
		}

		$page_builder_elements_[$key_] = $page_builder_element_;
	}

	echo json_encode(array(
		'success' => true,
		'elements' => $page_builder_elements_,
	));

	pb_end();
}
pb_add_ajax('page-builder-load-element', '_pb_page_builder_ajax_load_elements');

function _pb_page_builder_render_edit_form_group($func_){

}

function _pb_page_builder_ajax_load_edit_element_form(){
	$element_id_ = _POST('element_id');
	$defaults_ = _POST('element_data');
	$content_ = _POST('content');
	
	$temp_page_builder_elements_ = pb_page_builder_elements();
	$element_data_ = $temp_page_builder_elements_[$element_id_];
	$element_class_ = pb_page_builder_element_class($element_id_);

	global $pb_page_builder_element_edit_category_funcs;

	$edit_categories_ = pb_page_builder_element_edit_categories();
	$edit_category_functions_ = $pb_page_builder_element_edit_category_funcs;

	$element_edit_categories_ = isset($element_data_['edit_categories']) ? $element_data_['edit_categories'] : array("common");

	ob_start();
?>



<div class="pb-page-builder-element-edit-nav-tabs">

	<ul class="nav nav-tabs" role="tablist">
		<?php for($index_=0;$index_<count($edit_categories_); ++$index_){
			$category_data_ = $edit_categories_[$index_];

			if(!in_array($category_data_['key'], $element_edit_categories_)){
				continue;
			}
		?>
			<li role="presentation" class=""><a href="#manage-site-tab-<?=$category_data_['key']?>" role="tab" data-toggle="tab"><?=$category_data_['title']?></a></li>
		<?php } ?>
		
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<?php for($index_=0;$index_<count($edit_categories_); ++$index_){
			$category_data_ = $edit_categories_[$index_];
			if(!in_array($category_data_['key'], $element_edit_categories_)){
				continue;
			}

			$element_edit_forms_ = $element_class_->edit_forms($category_data_['key']);
		?>
			<div role="tabpanel" class="tab-pane" id="manage-site-tab-<?=$category_data_['key']?>">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><?=$category_data_['title']?></h3>
					</div>
					<div class="panel-body">
					<?php foreach($edit_category_functions_[$category_data_['key']] as $edit_form_){

						if(is_callable($edit_form_['render'])){
							call_user_func_array($edit_form_['render'], array($defaults_, $content_));	
						}else{
							pb_page_builder_element_edit_form_render($edit_form_['render'], $defaults_, $content_);
						}

						
					} ?>

					<?php foreach($element_edit_forms_ as $edit_form_){
						if(is_callable($edit_form_['render'])){
							call_user_func_array($edit_form_['render'], array($defaults_, $content_));	
						}else{
							pb_page_builder_element_edit_form_render($edit_form_['render'], $defaults_, $content_);
						}
					} ?>
					</div>
				</div>
			</div>
		<?php } ?>

		
	</div>
</div>


<?php
	$form_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'form_html' => $form_html_,
	));

	pb_end();
}
pb_add_ajax('page-builder-load-edit-form', '_pb_page_builder_ajax_load_edit_element_form');

function _pb_page_builder_ajax_render_element_custom_preview(){
	$element_id_ = _POST('key');
	$field_name_ = _POST('field_name');
	$element_data_ = _POST('element_data');
	$content_ = _POST('content');

	$temp_page_builder_elements_ = pb_page_builder_elements();

	$target_element_ = $temp_page_builder_elements_[$element_id_];

	$preview_fields_ = $target_element_['preview_fields'];
	$target_preview_data_ = null;

	foreach($preview_fields_ as $preview_field_){
		if($preview_field_['name'] === $field_name_){
			$target_preview_data_ = $preview_field_;
			break;
		}
	}

	ob_start();
	call_user_func_array($target_preview_data_['func'], array($element_data_, $content_));
	$preview_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'preview_html' => $preview_html_,
	));

	pb_end();	
}

pb_add_ajax('page-builder-render-element-custom-preview', '_pb_page_builder_ajax_render_element_custom_preview');


function _pb_page_builder_ajax_render_element_render_preview(){
	$element_id_ = _POST('key');
	$element_data_ = _POST('element_data');
	$content_ = _POST('content');

	$temp_page_builder_elements_ = pb_page_builder_elements();

	$target_element_ = $temp_page_builder_elements_[$element_id_];
	$preview_render_ = $target_element_['preview'];
	
	ob_start();
	call_user_func_array($preview_render_, array($element_data_, $content_));
	$preview_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'preview_html' => $preview_html_,
	));

	pb_end();	
}
pb_add_ajax('page-builder-render-element-render-preview', '_pb_page_builder_ajax_render_element_render_preview');

?>