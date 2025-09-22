<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_builder_elements($excludes_ = array()){
	global $pb_page_builder_elements;

	if(!isset($pb_page_builder_elements)){
		$pb_page_builder_elements = array();
	}
	$pb_page_builder_elements = pb_hook_apply_filters("pb_page_builder_elements", $pb_page_builder_elements);

	foreach($pb_page_builder_elements as $key_ => $data_){
		if(in_array($key_, $excludes_)){
			unset($pb_page_builder_elements[$key_]);
		}
	}

	return $pb_page_builder_elements;
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
		'priority' => $priority_,
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

	array_splice($pb_page_builder_element_edit_category_funcs[$key_], $insert_index_, 0, array(
		array(
			'render' => $func_,
			'priority' => $priority_,
		)
	));
}

pb_page_builder_element_add_edit_category('common', __('기본설정'), 1);
pb_page_builder_element_add_edit_category('styles', __('스타일'), 2);
pb_page_builder_element_add_edit_category_function('common', 'pb_page_builder_element_edit_category_common');
pb_page_builder_element_add_edit_category_function('styles', 'pb_page_builder_element_edit_category_styles');

function pb_page_builder_element_edit_category_common($element_data_){

	$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
	$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
	?>

	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<label><?=__('ID')?></label>
				<input type="text" name="id" placeholder="<?=__('ID 입력')?>" class="form-control" value="<?=$id_?>">

				<div class="help-block with-errors"></div>
				<div class=""></div>
			</div>

		</div>
		<div class="col-xs-12 col-sm-6">

			<div class="form-group">
				<label><?=__('클래스')?></label>
				<input type="text" name="class" placeholder="<?=__('클래스 입력')?>" class="form-control" value="<?=$class_?>">

				<div class="help-block with-errors"></div>
				<div class=""></div>
			</div>

		</div>
	</div>
			
	<?php
}

function pb_page_builder_element_edit_category_styles($element_data_){

	$margin_top_ = isset($element_data_['margin_top']) ? $element_data_['margin_top'] : null;
	$margin_bottom_ = isset($element_data_['margin_bottom']) ? $element_data_['margin_bottom'] : null;
	$margin_left_ = isset($element_data_['margin_left']) ? $element_data_['margin_left'] : null;
	$margin_right_ = isset($element_data_['margin_right']) ? $element_data_['margin_right'] : null;

	$padding_top_ = isset($element_data_['padding_top']) ? $element_data_['padding_top'] : null;
	$padding_bottom_ = isset($element_data_['padding_bottom']) ? $element_data_['padding_bottom'] : null;
	$padding_left_ = isset($element_data_['padding_left']) ? $element_data_['padding_left'] : null;
	$padding_right_ = isset($element_data_['padding_right']) ? $element_data_['padding_right'] : null;

	$background_color_ = isset($element_data_['background_color']) ? $element_data_['background_color'] : null;
	$background_image_ = isset($element_data_['background_image']) ? $element_data_['background_image'] : null;
	$background_image_ = pb_encode_json_uploaded_file($background_image_);
	$background_size_ = isset($element_data_['background_size']) ? $element_data_['background_size'] : null;
	$background_position_ = isset($element_data_['background_position']) ? $element_data_['background_position'] : null;

	$bacground_color_picker_id_ = "pb-background-color-picker-".pb_random_string(5);
	$bacground_image_picker_id_ = "pb-background-image-picker-".pb_random_string(5);
	$unique_class_name_ = isset($element_data_['unique_class_name']) && strlen($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : "pb-element-class-".pb_random_string(10, PB_RANDOM_STRING_NUMLOWER);

	$classes_ = isset($element_data_['classes']) ? $element_data_['classes'] : null;
	?>
	<input type="hidden" name="unique_class_name" value="<?=$unique_class_name_?>">
	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<div class="form-group">
				<label><?=__('여백설정')?></label>
				<div class="pb-page-builder-element-margin-editor">
						
					<div class="square-edit-area margin">
						<input type="text" name="margin_top" class="input top" placeholder="-" value="<?=$margin_top_?>">
						<input type="text" name="margin_right" class="input right" placeholder="-" value="<?=$margin_right_?>">
						<input type="text" name="margin_bottom" class="input bottom" placeholder="-" value="<?=$margin_bottom_?>">
						<input type="text" name="margin_left" class="input left" placeholder="-" value="<?=$margin_left_?>">
						<label class="subject"><?=__('바깥쪽여백')?></label>
					</div>
					<div class="square-edit-area padding">
						<input type="text" name="padding_top" class="input top" placeholder="-" value="<?=$padding_top_ ?>">
						<input type="text" name="padding_right" class="input right" placeholder="-" value="<?=$padding_right_ ?>">
						<input type="text" name="padding_bottom" class="input bottom" placeholder="-" value="<?=$padding_bottom_ ?>">
						<input type="text" name="padding_left" class="input left" placeholder="-" value="<?=$padding_left_ ?>">
						<label class="subject"><?=__('안쪽여백')?></label>					
					</div>

				</div>

				<div class="help-block with-errors"></div>
				<div class=""></div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6">

			<div class="row">
				<div class="col-xs-12 col-sm-12">
					<div class="form-group">
						<label><?=__('배경색')?></label>
						<div class="input-group colorpicker-component" id="<?=$bacground_color_picker_id_?>">
							<input type="text" name="background_color" placeholder="<?=__('배경색 선택')?>" class="form-control" value="<?=$background_color_?>">
							<span class="input-group-addon"><i></i></span>
						</div>
						
						<script type="text/javascript">
							jQuery(document).ready(function(){
								$("#<?=$bacground_color_picker_id_?>").colorpicker();
							});
						</script>

						<div class="help-block with-errors"></div>
						<div class=""></div>
					</div>

				</div>
				<div class="col-xs-12 col-sm-12">
					<div class="form-group">
						<label><?=__('배경이미지')?></label>
						<input type="hidden" name="background_image" value="<?= isset($background_image_) ? (htmlentities(json_encode($background_image_))) : null ?>" data-upload-path="/" id="<?=$bacground_image_picker_id_?>" data-limit="1">

						<script type="text/javascript">
							jQuery(document).ready(function(){
								$("#<?=$bacground_image_picker_id_?>").pb_image_input();
							});
						</script>

						<div class="help-block with-errors"></div>
						<div class=""></div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12 col-sm-12">
					<div class="form-group">
						<label><?=__('배경이미지 크기')?></label>
						<input type="text" name="background_size" placeholder="크기 입력" class="form-control" value="<?=$background_size_?>">

						<div class="help-block with-errors"></div>
						<div class=""></div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12">
					<div class="form-group">
						<label><?=__('배경이미지 위치')?></label>
						<input type="text" name="background_position" placeholder="<?=__('위치 입력')?>" class="form-control" value="<?=$background_position_?>">

						<div class="help-block with-errors"></div>
						<div class=""></div>
					</div>
				</div>
			</div>
					
					
		</div>
	</div>

	<?php
}


function pb_page_builder_element_make_styles($element_data_ = array()){
	$class_name_ = isset($element_data_['unique_class_name']) && strlen($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;
	if(!strlen($class_name_)) return null;

	$data_ = array();

	$data_['margin-top'] = isset($element_data_['margin_top']) ? $element_data_['margin_top'] : null;
	$data_['margin-bottom'] = isset($element_data_['margin_bottom']) ? $element_data_['margin_bottom'] : null;
	$data_['margin-left'] = isset($element_data_['margin_left']) ? $element_data_['margin_left'] : null;
	$data_['margin-right'] = isset($element_data_['margin_right']) ? $element_data_['margin_right'] : null;

	$data_['padding-top'] = isset($element_data_['padding_top']) ? $element_data_['padding_top'] : null;
	$data_['padding-bottom'] = isset($element_data_['padding_bottom']) ? $element_data_['padding_bottom'] : null;
	$data_['padding-left'] = isset($element_data_['padding_left']) ? $element_data_['padding_left'] : null;
	$data_['padding-right'] = isset($element_data_['padding_right']) ? $element_data_['padding_right'] : null;
	
	$data_['background-color'] = isset($element_data_['background_color']) ? $element_data_['background_color'] : null;

	$data_['background-image'] = isset($element_data_['background_image']) && strlen($element_data_['background_image']) ? "url('".pb_filebase_url(pb_parse_uploaded_file_path($element_data_['background_image']))."')" : null;
	$data_['background-size'] = isset($element_data_['background_size']) ? $element_data_['background_size'] : null;
	$data_['background-position'] = isset($element_data_['background_position']) ? $element_data_['background_position'] : null;

	$data_ = pb_hook_apply_filters('pb_page_builder_element_make_styles', $data_, $element_data_);

	ob_start();

	?> .<?=$class_name_?>{
<?php foreach($data_ as $key_ => $value_){
	if(!$value_ || $value_ === "") continue;
	?>	<?=$key_?>:<?=$value_?>;
<?php } ?>
} <?php

	return ob_get_clean();
}

function _pb_page_builder_element_recv_render_style($element_data_){
	$element_map_ = pb_page_builder_elements();
	$style_sheet_ = pb_page_builder_element_make_styles($element_data_['properties']);

	if(isset($element_map_[$element_data_['name']]['loadable']) && $element_map_[$element_data_['name']]['loadable']){
		$page_contents_ = $element_data_['elementcontent'];
		foreach($page_contents_ as $child_data_){
			$style_sheet_ .= _pb_page_builder_element_recv_render_style($child_data_);
		}
	}

	return $style_sheet_;
}
function _pb_page_builder_element_render_styles($results_, $builder_data_){
	$page_contents_ = $builder_data_['elementcontent'];
	foreach($page_contents_ as $element_data_){
		$results_ .= _pb_page_builder_element_recv_render_style($element_data_);
	}
	return $results_;
}
pb_hook_add_filter('pb_page_builder_global_style', '_pb_page_builder_element_render_styles');


function pb_page_builder_element_edit_form_types(){
	global $_pb_page_builder_element_edit_form_types;
	if(isset($_pb_page_builder_element_edit_form_types)) return $_pb_page_builder_element_edit_form_types;
	$_pb_page_builder_element_edit_form_types = pb_hook_apply_filters('pb_page_builder_element_edit_form_types', array(
		'text' => '_pb_page_builder_element_edit_form_type_common_render',
		'textarea' => '_pb_page_builder_element_edit_form_type_common_render',
		'image' => '_pb_page_builder_element_edit_form_type_common_render',
		'select' => '_pb_page_builder_element_edit_form_type_common_render',
		'checkbox' => '_pb_page_builder_element_edit_form_type_common_render',
		'radio' => '_pb_page_builder_element_edit_form_type_common_render',
		'editor' => '_pb_page_builder_element_edit_form_type_common_render',
		'html' => '_pb_page_builder_element_edit_form_type_common_render',
	));

	return $_pb_page_builder_element_edit_form_types;
}

function pb_page_builder_element_edit_form_render($edit_list_, $element_data_, $content_ = null){
	$edit_form_types_ = pb_page_builder_element_edit_form_types();

	foreach($edit_list_ as $edit_data_){
		$type_ = isset($edit_data_['type']) ? $edit_data_['type'] : 'text';
		$edit_renderer_ = isset($edit_form_types_[$type_]) ? $edit_form_types_[$type_] : null;

		if(is_callable($edit_renderer_)){
			call_user_func_array($edit_renderer_, array($edit_data_, $element_data_, $content_));
		}
	}
}

function _pb_page_builder_element_edit_form_type_common_render($edit_data_, $element_data_, $content_ = null){

	$name_ = isset($edit_data_['name']) ? $edit_data_['name'] : null;
	$label_ = isset($edit_data_['label']) ? $edit_data_['label'] : null;
	$placeholder_ = isset($edit_data_['placeholder']) ? $edit_data_['placeholder'] : null;
	$help_ = isset($edit_data_['help']) ? $edit_data_['help'] : null;
	$type_ = isset($edit_data_['type']) ? $edit_data_['type'] : null;
	$default_ = isset($edit_data_['default']) ? $edit_data_['default'] : null;

	$input_value_ = null;

	if($name_ === 'content'){
		$input_value_ = $content_;
	}else{
		$input_value_ = isset($element_data_[$name_]) ? $element_data_[$name_] : $default_;
	}

	$validators_ = isset($edit_data_['validators']) ? $edit_data_['validators'] : array();
	$validator_attr_ = "";

	foreach($validators_ as $validator_){
		$type_ = $validator_['type'];
		$value_ = isset($validator_['value'])? $validator_['value'] : null;
		$error_message_ = isset($validator_['error']) ? $validator_['error'] : null;

		$attr_str_ = ' data-'.$type_.'="'.$value_.'" ';

		if(strlen($error_message_)){
			$attr_str_ .= ' data-'.$type_.'-error="'.$error_message_.'" ';
		}

		$validator_attr_ .= $attr_str_;
	}

	?>

	<div class="form-group">

	<?php 

	if(strlen($label_)){ ?>

		<label><?=$label_?></label>

	<?php }

	switch($type_){
		
		case 'textarea' : ?>
			<textarea name="<?=$name_?>" <?=$validator_attr_?> placeholder="<?=$placeholder_?>" class="form-control"><?=stripslashes($input_value_)?></textarea>
		<?php break;
		case 'image' :

			$image_input_id_ = 'pb-page-builder-image-input-'.pb_random_string(5);
			$image_limit_ = isset($edit_data_['limit']) ? $edit_data_['limit'] : 1;

		?>
			<input type="hidden" name="<?=$name_?>" data-upload-path="/" id='<?=$image_input_id_?>' value="<?=htmlentities($input_value_)?>"  data-limit="<?=$image_limit_?>">
			<script type="text/javascript">jQuery("#<?=$image_input_id_?>").pb_image_input();</script>
			
		<?php break;
		case 'file' :

			$file_input_id_ = 'pb-page-builder-file-input-'.pb_random_string(5);
			$file_limit_ = isset($edit_data_['limit']) ? $edit_data_['limit'] : 1;

		?>
			<input type="hidden" name="<?=$name_?>" data-upload-path="/" id='<?=$image_input_id_?>' value="<?=htmlentities($input_value_)?>" data-limit="<?=$file_limit_?>">
			<script type="text/javascript">jQuery("#<?=$file_input_id_?>").pb_file_input();</script>
			
		<?php break;

		case 'select' : 
			$options_ = $edit_data_['options'];
		?>

			<select class="form-control" name="<?=$name_?>" <?=$validator_attr_?> placeholder="<?=$placeholder_?>" >
				<?php foreach($options_ as $option_value_ => $option_name_){ ?>
					<option value="<?=$option_value_?>" <?=pb_selected($option_value_, $input_value_)?>><?=$option_name_ ?></option>
				<?php } ?>
			</select>
			
		<?php break;
		case 'checkbox' :
		case 'radio' : 

			$options_ = $edit_data_['options'];
			$input_value_ = gettype($input_value_) !== "array" ? explode(",", $input_value_) : array();
		?>

		<div>
			<?php foreach($options_ as $option_value_ => $option_name_){ ?>
			<label class="<?=$type_?>-inline"><input type="<?=$type_?>" name="<?=$name_?>" value="<?=$option_value_?>" <?=$validator_attr_?> <?=in_array($option_value_, $input_value_) !== false ? "checked" : ""?>><?=$option_name_?></label>
			<?php } ?>
		</div>
			
		<?php break;
		case 'editor' :
			$editor_id_ = 'pb-page-builder-editor-'.pb_random_string(5);
		?>
	
		<input type="text" name="<?=$name_?>" <?=$validator_attr_?> class="hidden" id="<?=$editor_id_?>-input" value="<?=htmlentities($input_value_)?>">
		<?php pb_wysiwyg_editor('_'.$name_, stripslashes($input_value_), array(
			'id' => $editor_id_,
		)); ?>
		<script type="text/javascript">
		$("#<?=$editor_id_?>").pb_wysiwyg_editor().options({
			'sync' : function(content_){
				$("#<?=$editor_id_?>-input").val(content_);
			}
		});
		</script>
			
		<?php break;
		case 'html' :
			$editor_id_ = 'pb-page-builder-html-editor-'.pb_random_string(5);
		?>
		
		<textarea id="<?=$editor_id_?>" name="<?=$name_?>" <?=$validator_attr_?> placeholder="<?=$placeholder_?>"><?=stripslashes($input_value_)?></textarea>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			var target_textarea_ = $("#<?=$editor_id_?>");
			var target_module_ = CodeMirror.fromTextArea(target_textarea_[0], {
				lineNumbers: true,
				selectionPointer: true,
				styleActiveLine: true,
				matchBrackets: true, 
				autoCloseBrackets : true,
				continueComments : true,
				selectionPointer: true,
				mode: "text/html",
				extraKeys: {"Ctrl-Space": "autocomplete"},
			});

			target_module_.on("change", $.proxy(function(instance_){
				this.val(instance_.getValue());
			}, target_textarea_));

			setTimeout($.proxy(function() {
				this.refresh();
			}, target_module_),100);
		});
		</script>
			
		<?php break;
		case 'text' :
		default : ?>
			<input type="text" name="<?=$name_?>" value="<?=htmlentities($input_value_)?>" <?=$validator_attr_?> placeholder="<?=$placeholder_?>" class="form-control">
		<?php break;
	}

	?>

		<div class="help-block with-errors"></div>
		<?php if(strlen($help_)){ ?>
			<div class="help-block"><?=$help_?></div>
		<?php } ?>
		<div class="clearfix"></div>
	</div>

	<?php 
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/class.page-builder-element.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/common.php');

?>