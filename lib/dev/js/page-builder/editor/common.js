jQuery(function($){

	/**
		PB PAGE BUILDER COMMON ELEMENT - ROW
	*/

	var pb_page_builder_row_element = window.pb_page_builder_row_element = function(target_, page_builder_, key_, defaults_){
		//parent override
		pb_page_builder_element.apply(this, [target_, page_builder_, key_, defaults_]);
		this._target.toggleClass("pb-page-builder-row-element", true);
		this._children_frame_el.empty();

		this._children_frame_el.on("sortstart",function(event_, ui_){
			ui_.placeholder.width(ui_.helper.width());
		});
		this._children_frame_el.on("sortstop",$.proxy(function(event_, ui_){
			var column_items_ = this._children_frame_el.children(".element-content-item");
			var columns_ = [];

			column_items_.each(function(){
				columns_.push($(this).pb_page_builder_element().column_width());
			});
			this._element_data['columns'] = columns_.join(":");
			this._update_preview();
		},this));

		this._children_frame_el.sortable("option", "connectWith", "");
		this._children_frame_el.attr("data-disconnected", "Y");
		this._update_preview();

	}
	pb_page_builder_row_element.prototype = $.extend({}, pb_page_builder_element.prototype);

	pb_page_builder_row_element.prototype.prepend_element = function(key_, defaults_){
		console.error("not supported");
	}
	pb_page_builder_row_element.prototype.append_element = function(key_, defaults_){
		console.error("not supported");
	}
	
	pb_page_builder_row_element.prototype._check_children = function(){
		this._children_frame_el.sortable("refresh");
	};
	
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

		var last_column_item_ = null;

		for(var col_index_=0;col_index_<columns_.length;++col_index_){
			var column_width_ = parseInt(columns_[col_index_]);
				column_width_ = isNaN(column_width_) ? 12 : Math.abs(column_width_);
				column_width_ = column_width_ > 12 ? 12 : column_width_;

			var target_column_el_ = null;

			if(!before_columns_[col_index_]){
				target_column_el_ = $("<div></div>");
				this._children_frame_el.append(target_column_el_);
				target_column_el_.pb_page_builder_column_element(this._page_builder);
			}else{
				target_column_el_ = $(before_columns_[col_index_]);
			}

			target_column_el_.pb_page_builder_element().column_width(column_width_);
			last_column_item_ = target_column_el_;
		}

		for(var col_index_= columns_.length;col_index_<before_columns_.length;++col_index_){
			if(!before_columns_[col_index_]) break;
			var deatch_column_el_ = $(before_columns_[col_index_]);

			var deatch_column_element_ = deatch_column_el_.pb_page_builder_element();
			var last_column_element_ = last_column_item_.pb_page_builder_element();

			deatch_column_element_._children_frame_el.children().appendTo(last_column_element_._children_frame_el);
			deatch_column_el_.remove();

			last_column_element_._check_children();
		}


		this._children_frame_el.sortable("refresh");
	}

	pb_page_builder_row_element.prototype.apply_xml = function(xml_node_){
		var defaults_ = {};

		var property_nodes_ = pb_page_builder._child_node_by_name(xml_node_, "property", true);

		$.each(property_nodes_, function(){
			defaults_[this.getAttribute("name")] = this.textContent;
		});

		this._element_data = defaults_;
		var element_content_node_ = pb_page_builder._child_node_by_name(xml_node_, "elementcontent");
		var element_items_ = pb_page_builder._child_node_by_name(element_content_node_, "element", true);

		this._children_frame_el.children(".element-content-item").remove();

		for(var child_index_ = 0; child_index_<element_items_.length; ++child_index_){
			var child_node_ = element_items_[child_index_];
			var element_id_ = child_node_.getAttribute("name");

			var child_item_ = $("<div></div>");
			this._children_frame_el.append(child_item_);
			var element_class_ = window.pbpage_builder_element_map[element_id_]['edit_element_class'] ? window.pbpage_builder_element_map[element_id_]['edit_element_class'] : "pb_page_builder_element";
			var element_instance_ = child_item_[element_class_].apply(child_item_, [this._page_builder, element_id_]);
			element_instance_.apply_xml(child_node_);
		}

		this._update_preview();
	}

	$.fn.pb_page_builder_row_element = function(page_builder_, key_, defaults_){
		var module_ = this.data("pb-page-builder-element");
		if(module_) return module_;
		return new pb_page_builder_row_element(this, page_builder_, key_, defaults_);
	}

	/**
		PB PAGE BUILDER COMMON ELEMENT - COLUMN
	*/

	var pb_page_builder_column_element = window.pb_page_builder_column_element = function(target_, page_builder_, defaults_){
		//parent override
		pb_page_builder_element.apply(this, [target_, page_builder_, "column", defaults_]);
		this._delete_btn.remove();
		// this._element_name_el.remove();
		this._preview_frame_el.remove();
		this._target.toggleClass("pb-page-builder-column-element", true);
	}
	pb_page_builder_column_element.prototype = $.extend({}, pb_page_builder_element.prototype);
	pb_page_builder_column_element.prototype._update_preview = $.noop;

	pb_page_builder_column_element.prototype.column_width = function(column_width_){
		if(column_width_ !== undefined){
			this._element_data['column_width'] = column_width_;

			this._target.removeClass(function(index_, class_name_) {
				return (class_name_.match(/(^|\s)edit-col-\S+/g) || []).join(' ');
			});
			this._target.addClass("edit-col-"+column_width_);
		}
		return this._element_data['column_width'];
	}

	pb_page_builder_column_element.prototype.apply_xml = function(xml_node_){
		pb_page_builder_element.prototype.apply_xml.apply(this, [xml_node_]);
		var defaults_ = {};

		var property_nodes_ = pb_page_builder._child_node_by_name(xml_node_, "property", true);

		$.each(property_nodes_, function(){
			defaults_[this.getAttribute("name")] = this.textContent;
		});

		this._element_data = defaults_;
		this.column_width((this._element_data['column_width'] ? this._element_data['column_width'] : "12"));
		this._check_children();
	}


	$.fn.pb_page_builder_column_element = function(page_builder_, defaults_){
		var module_ = this.data("pb-page-builder-element");
		if(module_) return module_;
		return new pb_page_builder_column_element(this, page_builder_, defaults_);
	}

});
jQuery(document).ready(function(){
	pb_page_builder.add_element_edit_library('row', {
		preview : function(element_data_, content_){
			var html_ = "<div class='preview-item'>"+(element_data_['columns'] ? element_data_['columns'] : "컬럼 : 12")+"</div>";

			if(element_data_['valign'] === "bottom") html_ += "<div class='preview-item'>수직정렬 : 바닥으로</div>";
			else if(element_data_['valign'] === "middle") html_ += "<div class='preview-item'>수직정렬 : 중앙으로</div>";
			else html_ += "<div class='preview-item'>수직정렬 : 위로</div>";

			return html_;
		}
	});
	pb_page_builder.add_element_edit_library('container', {
		preview : function(element_data_, content_){
			if(element_data_['container_type'] === "box") return "<div class='preview-item'>박스스타일</div>";
			else if(element_data_['container_type'] === "full") return "<div class='preview-item'>꽉채움</div>";
			else return "<div class='preview-item'>박스스타일</div>";
		}
	});
	pb_page_builder.add_element_edit_library('text', {
		preview : function(element_data_, content_){
			var preview_text_ = $("<div>").html(content_).text();
				preview_text_ = preview_text_.substr(0, 100);

			return "<div class='preview-item'>"+preview_text_+"</div>";	
		}
	});
	pb_page_builder.add_element_edit_library('image', {
		preview : function(element_data_, content_){
			return "<div class='preview-item block-item'><img src='"+PBVAR['home_url']+"uploads/"+element_data_['src']+"'></div>";	
		}
	});
});