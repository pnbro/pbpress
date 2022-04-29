<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_row extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array(), $element_content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;
		$valign_ = isset($element_data_['valign']) ? $element_data_['valign'] : "top";

		?>
		<div class="pb-row valign-<?=$valign_?> <?=$class_?> <?=$unique_class_name_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($element_content_)?></div>
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$temp_form_id_ = "columns-input-".pb_random_string(5);

		$columns_ = isset($element_data_['columns']) ? $element_data_['columns'] : "12";
		$valign_ = isset($element_data_['valign']) ? $element_data_['valign'] : "top";
		
		?>

		<div class="form-group">
			<label><?=__('컬럼나누기')?></label>
			<input type="text" name="columns" class="form-control" value="<?=$columns_?>" id="<?=$temp_form_id_?>">
			<div class="help-block">
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '3:3:3:3')" class="badge">3:3:3:3</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '4:4:4')" class="badge">4:4:4</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '6:6')" class="badge">6:6</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '4:8')" class="badge">4:8</a>
				<a href="javascript:_pb_update_row_column('#<?=$temp_form_id_?>', '12')" class="badge">12</a>
			</div>
		</div>
		<div class="form-group">
			<label><?=__('수직정렬')?></label>
			<select class="form-control" name="valign">
				<option value="top" <?=pb_selected($valign_, "top")?>><?=__('위로')?></option>
				<option value="middle" <?=pb_selected($valign_, "middle")?>><?=__('중간으로')?></option>
				<option value="bottom" <?=pb_selected($valign_, "bottom")?>><?=__('아래로')?></option>
			</select>
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

	public function render($data_ = array(), $element_content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;
		$column_width_ = isset($element_data_['column_width']) && strlen($element_data_['column_width']) ? $element_data_['column_width'] : "12";
		$column_width_md_ = isset($element_data_['column_width_md']) && strlen($element_data_['column_width_md']) ? $element_data_['column_width_md'] : $column_width_;
		$column_width_sm_ = isset($element_data_['column_width_sm']) && strlen($element_data_['column_width_sm']) ? $element_data_['column_width_sm'] : $column_width_md_;
		$column_width_xs_ = isset($element_data_['column_width_xs']) && strlen($element_data_['column_width_xs']) ? $element_data_['column_width_xs'] : "12";
		
		?><div class="pb-col-lg-<?=$column_width_?> <?=$unique_class_name_?> pb-col-md-<?=$column_width_md_?> pb-col-sm-<?=$column_width_sm_?> pb-col-sm-<?=$column_width_sm_?> pb-col-xs-<?=$column_width_xs_?> <?=$class_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($element_content_)?></div><?php
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


		

			<h3><?=__('컬럼크기')?></h3>
			<div class="row">
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('기본')?></label>
					<input type="text" readonly value="<?=$element_data_['column_width']?>" class="form-control" name="column_width">
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('작은PC화면')?></label>
					<select class="form-control" name="column_width_md">
						<option value=""><?=__('생략')?></option>
						<?php _pb_draw_column_options($column_width_md_); ?>
					</select>
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<label><?=__('태블릿')?></label>
					<select class="form-control" name="column_width_sm">
						<option value=""><?=__('생략')?></option>
						<?php _pb_draw_column_options($column_width_sm_); ?>
					</select>
				</div></div>
				<div class="col-xs-6 col-sm-3"><div class="form-group">
					<div class="form-margin-xs visible-xs"></div>
					<label><?=__('모바일')?></label>
					<select class="form-control" name="column_width_xs">
						<option value=""><?=__('생략')?></option>
						<?php _pb_draw_column_options($column_width_xs_); ?>
					</select>
				</div></div>
			</div>
			
			

		

		<?php
	}
}

pb_page_builder_add_element("row", array(
	'name' => __("행"),
	'desc' => __("기본적인 행 요소"),
	'icon' => PB_LIBRARY_URL."img/page-builder/row.jpg",
	'element_object' => "PBPageBuilderElement_row",
	'edit_categories' => array("common", "styles"),
	'edit_element_class' => "pb_page_builder_row_element",
	'loadable' => true,
	'parent' => array("*", "!row"),
	'children' => array("column"),
	'preview_fields' => array(
		array(
			'name' => 'columns',
			'type' => 'text',
			'display' => 'inline',
		),
		array(
			'name' => 'valign',
			'type' => 'select',
			'values' => array(
				'top' => __('수직정렬 : 위쪽'),
				'middle' => __('수직정렬 : 중앙'),
				'bottom' => __('수직정렬 : 아래쪽'),
			),
			'display' => 'inline',
		)
	),
	'category' => __("기본"),
));

pb_page_builder_add_element("column", array(
	'name' => __("컬럼"),
	'icon' => PB_LIBRARY_URL."img/page-builder/row.jpg",
	'element_object' => "PBPageBuilderElement_column",
	'edit_categories' => array("common", "styles"),
	'edit_element_class' => "pb_page_builder_column_element",
	'loadable' => true,
	'children' => array("*", "!container"),
	'parent' => array("row"),
	'only_in_parent' => true,
	'category' => __("기본"),
));

?>