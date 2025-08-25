<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_image extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
		$this->add_edit_form("common", array(
			array(
				'name' => 'max_width',
				'type' => 'text',
				'label' => __('최대넓이'),
			),
			array(
				'name' => 'alt',
				'type' => 'text',
				'label' => __('설명'),
			),
			array(
				'name' => 'image_align',
				'type' => 'select',
				'label' => __('정렬'),
				'options' => array(
					'center' => __('중앙'),
					'left' => __('좌측'),
					'right' => __('우측'),
				)
			),

		));
	}

	public function render($data_ = array(), $element_content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) && strlen($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;
		$image_data_ = isset($element_data_['src']) ? $element_data_['src'] : null;

		$max_width_ = isset($element_data_['max_width']) && strlen($element_data_['max_width']) ? $element_data_['max_width'] : null;
		$image_align_ = isset($element_data_['image_align']) && strlen($element_data_['image_align']) ? $element_data_['image_align'] : "center";
		$alt_ = isset($element_data_['alt']) && strlen($element_data_['alt']) ? $element_data_['alt'] : null;
		
		?>
		<div class="pb-image-group <?=$class_?> <?=$unique_class_name_?> align-<?=$image_align_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>>
			<img class="pb-image " src="<?=pb_filebase_url(pb_parse_uploaded_file_path($image_data_))?>" style="<?=strlen($max_width_) ? "max-width:".$max_width_ : "" ?>" alt="<?=$alt_?>">
		</div>
		
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$temp_form_id_ = "image-input-".pb_random_string(5);
		$image_data_ = isset($element_data_['src']) ? $element_data_['src'] : null;
		$image_data_ = !is_string($image_data_) ? json_encode($image_data_) : $image_data_;
		?>

		<div class="form-group">
			<label><?=__('이미지선택')?></label>
			<input type="text" name="src" value="<?=htmlentities($image_data_)?>" class="hidden" id="<?=$temp_form_id_?>" data-upload-path="" data-thumbnail-ipnut="#<?=$temp_form_id_?>-thumbnail">
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
			'render' => __('최대넓이 {max_width}'),
		),
		array(
			'name' => 'image_align',
			'type' => 'select',
			'values' => array(
				'center' => __('중앙'),
				'left' => __('좌측'),
				'right' => __('우측'),
			),
			'display' => 'inline',
			'render' => __('{image_align} 정렬'),
		),
		array(
			'name' => 'src',
			'type' => 'image',
			'display' => 'block',
		)
	),
	'category' => __("기본"),
));

?>