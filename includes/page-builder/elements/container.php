<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_container extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array()){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$container_type_ = isset($element_data_['container_type']) ? $element_data_['container_type'] : "box";

		?>
		<div class="pb-container <?=$container_type_?> <?=$class_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($data_['elementcontent'])?></div>
		<?php
		
	}
	function render_admin_form($element_data_ = array(), $content_ = null){

		$container_type_ = isset($element_data_['container_type']) ? $element_data_['container_type'] : "container";

		?>

		<div class="form-group">
			<label>컨테이너 스타일</label>
			<select class="form-control" name="container_type">
				<option value="box" <?=pb_selected($container_type_, "box")?> >박스스타일</option>
				<option value="full" <?=pb_selected($container_type_, "full")?> >꽉채움</option>
			</select>
		</div>

		<?php
	}
}

pb_page_builder_add_element("container", array(
	'name' => "컨테이너",
	'desc' => "기본적인 요소 적재공간",
	'icon' => PB_LIBRARY_URL."img/page-builder/container.jpg",
	'element_object' => "PBPageBuilderElement_container",
	'edit_categories' => array("common"),
	'loadable' => true,
	'children' => array("*", "!container"),
	'parent' => array("*", "!column"),
	'category' => "기본",
));

?>