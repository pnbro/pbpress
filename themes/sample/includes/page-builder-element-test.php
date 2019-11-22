<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_image_slider extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array(), $content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) && strlen($element_data_['id']) ? $element_data_['id'] : "pb_image_slider_".pb_random_string(5);
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;

		global $_is_first_slide;
		$_is_first_slide = true;
		?>
<div class="image-slider-group <?=$class_?> <?=$unique_class_name_?>" id="<?=$id_?>">
	<div class="carousel slide"  data-ride="carousel">

		<div class="carousel-inner" role="listbox">
			<?=$this->render_content($data_['elementcontent'])?>
		</div>
	</div>
</div>
		<?php

		unset($_is_first_slide);
		
	}
	function render_admin_form($element_data_ = array(), $content_ = null){}
}


class PBPageBuilderElement_image_slider_item extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array(), $content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) && strlen($element_data_['id']) ? $element_data_['id'] : "pb_image_slider_".pb_random_string(5);
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;

		$slide_image_ = isset($element_data_['slide_image']) && strlen($element_data_['slide_image']) ? $element_data_['slide_image'] : null;
		$slide_image_ = strlen($slide_image_) ? pb_home_url("uploads/".$slide_image_) : "https://picsum.photos/500?random=".pb_random_string(5);

		$image_name_ = isset($element_data_['image_name']) && strlen($element_data_['image_name']) ? $element_data_['image_name'] : null;
		$title_ = isset($element_data_['title']) && strlen($element_data_['title']) ? $element_data_['title'] : null;
		$bottom_text_ = isset($element_data_['bottom_text']) && strlen($element_data_['bottom_text']) ? $element_data_['bottom_text'] : null;

		global $_is_first_slide;

		?>
<div class="carousel-item <?=$_is_first_slide ? "active" : ""?>">
	<img src="<?=$slide_image_?>" class="d-block w-100">
	<h5><?=$title_?></h5>
	<p><?=$bottom_text_?></p>
</div>

		<?php

		$_is_first_slide = false;
		
	}
	function render_admin_form($element_data_ = array(), $content_ = null){
		$slide_image_ = isset($element_data_['slide_image']) && strlen($element_data_['slide_image']) ? $element_data_['slide_image'] : null;
		$image_name_ = isset($element_data_['image_name']) && strlen($element_data_['image_name']) ? $element_data_['image_name'] : null;

		$title_ = isset($element_data_['title']) && strlen($element_data_['title']) ? $element_data_['title'] : null;
		$bottom_text_ = isset($element_data_['bottom_text']) && strlen($element_data_['bottom_text']) ? $element_data_['bottom_text'] : null;


		?>

		<div class="form-group">
			<label>슬라이드</label>
			<input type="text" name="slide_image" value="<?=$slide_image_?>" class="hidden" id="pb_slide_image_picker" data-upload-path="/">
			<div class="clearfix"></div>
		</div>

		<div class="form-group">
			<label>타이틀</label>
			<input type="text" name="title" value="<?=$title_?>" class="form-control">
			<div class="clearfix"></div>
		</div>
		<div class="form-group">
			<label>하단텍스트</label>
			<input type="text" name="bottom_text" value="<?=$bottom_text_?>" class="form-control">
			<div class="clearfix"></div>
		</div>


	
		<script type="text/javascript">
			jQuery(document).ready(function(){
				$("#pb_slide_image_picker").pb_image_input();
			});
		</script>

		<?php
	}
}

pb_page_builder_add_element("image_slider", array(
	'name' => "이미지 슬라이더",
	'icon' => PB_LIBRARY_URL."img/page-builder/image.jpg",
	'element_object' => "PBPageBuilderElement_image_slider",
	'edit_categories' => array("common"),
	'loadable' => true,
	'children' => array("image_slider_item"),
	'category' => "기본",
));


pb_page_builder_add_element("image_slider_item", array(
	'name' => "슬라이드",
	'icon' => PB_LIBRARY_URL."img/page-builder/image.jpg",
	'element_object' => "PBPageBuilderElement_image_slider_item",
	'edit_categories' => array("common"),
	'parent' => array("image_slider"),
	'category' => "기본",
	'loadable' => false,
	'preview_fields' => array(
		array(
			'name' => 'title',
			'type' => 'text',
		),
		array(
			'name' => 'bottom_text',
			'type' => 'text',
		),
		array(
			'name' => 'slide_image',
			'type' => 'image',
			'display' => 'block',
		),

	),
));
;

?>