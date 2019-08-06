<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_row extends PBPageBuilderElement{

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

		$columns_ = isset($element_data_['columns']) ? $element_data_['columns'] : "12";
		$temp_form_id_ = "columns-input-".pb_random_string(5);

		?>

		<div class="form-group">
			<label>컬럼누나기</label>
			<input type="text" name="columns" class="form-control" value="<?=$columns_?>" id="<?=$temp_form_id_?>">
			<div class="help-block">
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '3:3:3:3')" class="badge">3:3:3:3</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '4:4:4')" class="badge">4:4:4</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '6:6')" class="badge">6:6</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '4:8')" class="badge">4:8</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '12')" class="badge">12</a>
			</div>
		</div>
		<script type="text/javascript">
			function _pb_update_row_column(id_, columns_){
				$(id_).val(columns_);
			}
		</script>

		<?php
	}
}

class PBPageBuilderElement_column extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($content_ = null, $element_data_ = array()){
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$column_width_ = isset($element_data_['column_width']) ? $element_data_['column_width'] : "12";

		?>
		<div class="col-xs-12 col-sm-<?=$column_width_?>  <?=$class_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=pb_page_builder_element_parse($content_)?></div>
		<?php
	}

	function render_admin_form($element_data_ = array()){}
}

pb_page_builder_add_element("row", array(
	'name' => "행",
	'desc' => "기본적인 행 요소",
	'icon' => PB_LIBRARY_URL."img/page-builder/row.jpg",
	'element_object' => "PBPageBuilderElement_row",
	'edit_categories' => array("common"),
	'edit_element_class' => "pb_page_builder_row_element",
	'loadable' => true,
	'children' => array("column"),
	'category' => "기본",
));

pb_page_builder_add_element("column", array(
	'name' => "컬럼",
	'icon' => PB_LIBRARY_URL."img/page-builder/row.jpg",
	'element_object' => "PBPageBuilderElement_column",
	'edit_categories' => array("common"),
	'edit_element_class' => "pb_page_builder_column_element",
	'loadable' => true,
	'children' => array("*", "!container"),
	'parent' => array("row"),
	'category' => "기본",
));

?>