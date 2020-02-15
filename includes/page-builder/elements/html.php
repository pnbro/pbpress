<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_html extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array(
			
			array(
				'name' => 'content',
				'type' => 'html',
				'label' => 'HTML에디터',
			),

		));
	}

	public function render($data_ = array(), $element_content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;

		?>
		<div class="html <?=$class_?> <?=$unique_class_name_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($element_content_)?></div>
		<?php
	}
}

pb_page_builder_add_element("html", array(
	'name' => "HTML",
	'desc' => "",
	'icon' => PB_LIBRARY_URL."img/page-builder/html.jpg",
	'element_object' => "PBPageBuilderElement_html",
	'edit_categories' => array("common", "styles"),
	'loadable' => false,
	'preview_fields' => array(
		array(
			'name' => 'content',
			'type' => 'html',
			'display' => 'block',
		),
	),
	'category' => "기본",
));

?>