<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_text extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($content_ = null, $element_data_ = array()){
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$container_type_ = isset($element_data_['container_type']) ? $element_data_['container_type'] : "container";

		?>
		<div class="bt-row <?=$class_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=pb_page_builder_element_parse($content_)?></div>
		<?php
	}

	function render_admin_form($element_data_ = array()){

		$temp_form_id_ = "columns-input-".pb_random_string(5);

		?>

		<?php
	}
}

pb_page_builder_add_element("text", array(
	'name' => "텍스트",
	'desc' => "",
	'icon' => PB_LIBRARY_URL."img/page-builder/text.jpg",
	'element_object' => "PBPageBuilderElement_text",
	'edit_categories' => array("common"),
	'loadable' => false,
	'children' => array("*"),
	'category' => "기본",
));

?>