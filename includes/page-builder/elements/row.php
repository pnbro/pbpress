<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_row extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array()){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$container_type_ = isset($element_data_['container_type']) ? $element_data_['container_type'] : "container";

		?>
		<div class="pb-row <?=$class_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($data_['elementcontent'])?></div>
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){

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

	public function render($data_ = array()){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$column_width_ = isset($element_data_['column_width']) ? $element_data_['column_width'] : "12";
		$column_width_md_ = isset($element_data_['column_width_md']) ? $element_data_['column_width_md'] : $column_width_;
		$column_width_sm_ = isset($element_data_['column_width_sm']) ? $element_data_['column_width_sm'] : $column_width_md_;
		$column_width_xs_ = isset($element_data_['column_width_xs']) ? $element_data_['column_width_xs'] : "12";
		
		?>
		<div class="pb-col-lg-<?=$column_width_?> pb-col-md-<?=$column_width_md_?> pb-col-sm-<?=$column_width_sm_?> pb-col-sm-<?=$column_width_sm_?> <?=$class_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($data_['elementcontent'])?></div>
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$column_width_xs_ = isset($element_data_['column_width_xs']) ? $element_data_['column_width_xs'] : "12";
		$column_width_sm_ = isset($element_data_['column_width_sm']) ? $element_data_['column_width_sm'] : null;
		$column_width_md_ = isset($element_data_['column_width_md']) ? $element_data_['column_width_md'] : null;
		$temp_form_id_ = "columns-input-".pb_random_string(5);

		function _pb_draw_column_options($default_ = null){
			for($index_=0;$index_<12; ++$index_){ ?>
				<option value="<?=$index_+1?>" <?=pb_selected($default_, (string)($index_+1))?>><?=$index_+1?></option>
			<?php }
		}

		?>


		

			<h3>컬럼크기</h3>
			<div class="row">
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label>기본</label>
					<input type="text" readonly value="<?=$element_data_['column_width']?>" class="form-control" name="column_width">
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label>작은PC화면</label>
					<select class="form-control" name="column_width_md">
						<option value="">생략</option>
						<?php _pb_draw_column_options($column_width_md_); ?>
					</select>
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label>태블릿</label>
					<select class="form-control" name="column_width_sm">
						<option value="">생략</option>
						<?php _pb_draw_column_options($column_width_sm_); ?>
					</select>
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<div class="form-margin-xs visible-xs"></div>
					<label>모바일</label>
					<select class="form-control" name="column_width_xs">
						<option value="">생략</option>
						<?php _pb_draw_column_options($column_width_xs_); ?>
					</select>
				</div></div>
			</div>
			
			

		

		<?php
	}
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