<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_image extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array()){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;
		$src_ = isset($element_data_['src']) ? $element_data_['src'] : null;

		$width_ = isset($element_data_['width']) ? $element_data_['width'] : "auto";
		$max_width_ = isset($element_data_['max_width']) ? $element_data_['max_width'] : null;
		$min_width_ = isset($element_data_['max_width']) ? $element_data_['max_width'] : null;

		?>
		<img class="pb-image <?=$class_?> <?=$unique_class_name_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?> src="<?=pb_home_url("uploads/".$src_)?>">
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$temp_form_id_ = "image-input-".pb_random_string(5);
		$src_ = isset($element_data_['src']) ? $element_data_['src'] : null;

		?>

		<div class="form-group">
			<label>이미지선택</label>
			<input type="text" name="src" value="<?=$src_?>" class="hidden" id="<?=$temp_form_id_?>" data-upload-path="/">

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
	'category' => "기본",
));

?>