<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_text extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array()){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;

		?>
		<div class="text <?=$class_?> <?=$unique_class_name_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($data_['elementcontent'])?></div>
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$temp_form_id_ = "text-input-".pb_random_string(5);

		?>

		<div class="form-group">
			<label>텍스트에디터</label>
			<textarea id="<?=$temp_form_id_?>" name="content"><?=stripslashes($content_)?></textarea>
			<div class="clearfix"></div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				$("#<?=$temp_form_id_?>").init_summernote_for_pb();
			});
		</script>


		<?php
	}
}

pb_page_builder_add_element("text", array(
	'name' => "텍스트",
	'desc' => "",
	'icon' => PB_LIBRARY_URL."img/page-builder/text.jpg",
	'element_object' => "PBPageBuilderElement_text",
	'edit_categories' => array("common", "styles"),
	'loadable' => false,
	'category' => "기본",
));

?>