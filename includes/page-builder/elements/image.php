<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_image extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array(), $element_content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) && strlen($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;
		$src_ = isset($element_data_['src']) ? $element_data_['src'] : null;

		$max_width_ = isset($element_data_['max_width']) && strlen($element_data_['max_width']) ? $element_data_['max_width'] : null;
		$image_align_ = isset($element_data_['image_align']) && strlen($element_data_['image_align']) ? $element_data_['image_align'] : "center";
		
		?>
		<div class="pb-image-group <?=$class_?> <?=$unique_class_name_?> align-<?=$image_align_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>>
			<img class="pb-image " src="<?=pb_home_url("uploads/".$src_)?>" style="<?=strlen($max_width_) ? "max-width:".$max_width_ : "" ?>">
		</div>
		
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$temp_form_id_ = "image-input-".pb_random_string(5);
		$src_ = isset($element_data_['src']) ? $element_data_['src'] : null;

		$max_width_ = isset($element_data_['max_width']) ? $element_data_['max_width'] : null;
		$image_align_ = isset($element_data_['image_align']) ? $element_data_['image_align'] : "center";

		?>

		<div class="form-group">
			<label>이미지선택</label>
			<input type="text" name="src" value="<?=$src_?>" class="hidden" id="<?=$temp_form_id_?>" data-upload-path="/">
			<div class="clearfix"></div>
		</div>

		<div class="form-group">
			<label>최대넓이</label>
			<input type="text" name="max_width" value="<?=$max_width_?>" class="form-control">
			<div class="clearfix"></div>
		</div>

		<div class="form-group">
			<label>정렬</label>
			<select class="form-control" name="image_align">
				<option <?=pb_selected($image_align_, "center")?> value="center">중앙</option>
				<option <?=pb_selected($image_align_, "left")?> value="left">좌측</option>
				<option <?=pb_selected($image_align_, "right")?> value="right">우측</option>
			</select>
			<div class="clearfix"></div>
		</div>


		<script type="text/javascript">
			jQuery(document).ready(function(){
				$("#<?=$temp_form_id_?>").pb_image_input();
			});
		</script>


		<?php
	}
}

pb_page_builder_add_element("image", array(
	'name' => "이미지",
	'desc' => "",
	'icon' => PB_LIBRARY_URL."img/page-builder/image.jpg",
	'element_object' => "PBPageBuilderElement_image",
	'edit_categories' => array("common", "styles"),
	'loadable' => false,
	'preview_fields' => array(
		array(
			'name' => 'max_width',
			'type' => 'text',
			'render' => '최대넓이 {max_width}',
		),
		array(
			'name' => 'image_align',
			'type' => 'select',
			'values' => array(
				'center' => '중앙',
				'left' => '좌측',
				'right' => '우측',
			),
			'display' => 'inline',
			'render' => '{image_align} 정렬',
		),
		array(
			'name' => 'src',
			'type' => 'image',
			'display' => 'block',
		)
	),
	'category' => "기본",
));

?>