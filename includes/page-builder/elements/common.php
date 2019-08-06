<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/container.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/row.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/text.php');

function _pb_page_builder_admin_initialize_for_common(){
	
?>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/page-builder/editor/common.js"></script>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/common.css">
<script type="text/javascript">
jQuery(function($){

	/**
		PB PAGE BUILDER COMMON ELEMENT - ROW
	*/

	var pb_page_builder_row_element = window.pb_page_builder_row_element = function(target_, page_builder_, key_, data_, defaults_){
		//parent override
		pb_page_builder_element.apply(this, [target_, page_builder_, key_, data_, defaults_]);
		this._target.toggleClass("pb-page-builder-row-element", true);
		this._children_frame_el.empty();
		this._update_preview();

		this._children_frame_el.on("sortstart",function(event_, ui_){
			ui_.placeholder.width(ui_.helper.width());
		});
		this._children_frame_el.on("sortstop",$.proxy(function(event_, ui_){
			var column_items_ = this._children_frame_el.children(".element-content-item");
			var columns_ = [];

			column_items_.each(function(){
				columns_.push($(this).data("column-width"));
			});
			this._element_data['columns'] = columns_.join(":");
		},this));

		this._children_frame_el.sortable("option", "connectWith", "");

	}
	pb_page_builder_row_element.prototype = $.extend({}, pb_page_builder_element.prototype);

	pb_page_builder_row_element.prototype.prepend_child = function(key_, data_, defaults_){
		console.error("not supported");
	}
	pb_page_builder_row_element.prototype.append_child = function(key_, data_, defaults_){
		console.error("not supported");
	}

	pb_page_builder_row_element.prototype.to_json = function(){

	}
	pb_page_builder_row_element.prototype.apply_json = function(){

	}
	
	pb_page_builder_row_element.prototype._check_children = $.noop;
	
	pb_page_builder_row_element.prototype._update_preview = function(){
		pb_page_builder_element.prototype['_update_preview'].apply(this);

		var columns_ = (this._element_data['columns'] && this._element_data['columns'] !== "") ? this._element_data["columns"] : "12";
			columns_ = columns_.split(":");

		if(columns_.length > 5){
			columns_ = columns_.splice(0, 5);
		}

		var module_ = this;

		var before_column_elements_ = [];
		var before_columns_ = this._children_frame_el.children(".element-content-item");

		for(var col_index_=columns_.length;col_index_<before_columns_.length;++col_index_){
			if(!before_columns_[col_index_]) break;
			var deatch_column_el_ = $(before_columns_[col_index_]);
			deatch_column_el_.children().appendTo(before_columns_[before_columns_.length - 1]);
			deatch_column_el_.remove();
		}

		for(var col_index_=0;col_index_<columns_.length;++col_index_){
			var column_width_ = parseInt(columns_[col_index_]);
				column_width_ = isNaN(column_width_) ? 12 : Math.abs(column_width_);
				column_width_ = column_width_ > 12 ? 12 : column_width_;

			var target_column_el_ = null;

			if(!before_columns_[col_index_]){
				target_column_el_ = $("<div></div>");
				this._children_frame_el.append(target_column_el_);
				target_column_el_.pb_page_builder_column_element(this._page_builder, {});
			}else{
				target_column_el_ = $(before_columns_[col_index_]);
			}

			target_column_el_.removeClass(function(index_, class_name_) {
				return (class_name_.match(/(^|\s)edit-col-\S+/g) || []).join(' ');
			});

			target_column_el_.addClass("edit-col-"+column_width_);
			target_column_el_.data("column-width", column_width_);
		}
	}

	$.fn.pb_page_builder_row_element = function(page_builder_, key_, data_, defaults_){
		var module_ = this.data("pb-page-builder-element");
		if(module_) return module_;
		return new pb_page_builder_row_element(this, page_builder_, key_, data_, defaults_);
	}

	/**
		PB PAGE BUILDER COMMON ELEMENT - COLUMN
	*/

	var pb_page_builder_column_element = window.pb_page_builder_column_element = function(target_, page_builder_, defaults_){
		//parent override
		pb_page_builder_element.apply(this, [target_, page_builder_, "column", {
			name : '컬럼',
			loadable : true,
			parent : ['row'],
			children : ["*", "!container"],
		}, defaults_]);
		this._delete_btn.remove();
		// this._element_name_el.remove();
		this._preview_frame_el.remove();
		this._target.toggleClass("pb-page-builder-column-element", true);
	}
	pb_page_builder_column_element.prototype = $.extend({}, pb_page_builder_element.prototype);
	pb_page_builder_column_element.prototype._update_preview = $.noop;

	$.fn.pb_page_builder_column_element = function(page_builder_, defaults_){
		var module_ = this.data("pb-page-builder-element");
		if(module_) return module_;
		return new pb_page_builder_column_element(this, page_builder_, defaults_);
	}

});
</script>
<?php
}
pb_hook_add_action("pb_page_builder_admin_initialize", "_pb_page_builder_admin_initialize_for_common");

?>