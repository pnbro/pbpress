<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_PAGE_BUILDER_VERSION', "0.1.1");
define('PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MIN', "0.0.1");
define('PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MAX', "0.1.1");

function _pb_page_builder_recursive_parse_inner($element_){
	$element_map_ = pb_page_builder_elements();
	global $pb_page_builder_element_classes;

	$tmp_element_attributes_ = $element_->attributes();
	$element_name_ = null;

	foreach($tmp_element_attributes_ as $key_ => $value_){
		if($key_ === "name"){
			$element_name_ = (string)$value_;
		}
	}

	$tmp_element_properties_ = $element_->property;
	$element_properties_ = array();

	foreach($tmp_element_properties_ as $key_ => $value_){
		$element_properties_[(string)$value_->attributes()->name] = (string)$value_;
	}

	if(isset($element_map_[$element_name_]['loadable']) && $element_map_[$element_name_]['loadable']){
		$inner_elements_ = array();

		if(count($element_->elementcontent) > 0){
			foreach($element_->elementcontent->element as $inner_element_){
				$inner_elements_[] = _pb_page_builder_recursive_parse_inner($inner_element_);
			}	
		}

		return array(
			'name' => $element_name_,
			'properties' => $element_properties_,
			'elementcontent' => $inner_elements_,
		);
	}else{
		return array(
			'name' => $element_name_,
			'properties' => $element_properties_,
			'elementcontent' => (string)$element_->elementcontent,
		);
	}
}

function pb_page_builder_parse_xml($xml_string_){
	$xml_instance_ = simplexml_load_string($xml_string_);

	$root_node_name_ = $xml_instance_->getName();
	if($root_node_name_ !== "pbpagebuilder"){
		return new PBError(-1, "PBPageBuilder 문서형식이 아닙니다.", "문서형식오류");
	}
	$root_attrs_ = $xml_instance_->attributes();
	$version_ = (string)$root_attrs_["version"];

	$version_check_min_ = version_compare($version_, PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MIN, ">=");
	$version_check_max_ = version_compare($version_, PB_PAGE_BUILDER_VERSION_COMPATIBILITY_MAX, "<=");

	if(!$version_check_min_ || !$version_check_max_){
		return new PBError(-3, "문서 버젼이 현재버젼의  PBPageBuilder와 호환되지 않습니다", "문서호환성오류");	
	}

	if(!isset($xml_instance_->settings) || !isset($xml_instance_->pagecontent)){
		return new PBError(-6, "필수노드가 누락되었습니다.", "문서형식오류");		
	}

	$settings_ = $xml_instance_->settings;
		
	$results_ = array();

	$results_['settings'] = array(
		"style" => (string)$settings_->style,
		"script" => (string)$settings_->script,
	);

	$page_contents_ = array();

	foreach($xml_instance_->pagecontent->element as $element_){
		$page_contents_[] = _pb_page_builder_recursive_parse_inner($element_);
	}

	$results_['elementcontent'] = $page_contents_;

	return $results_;
}

function pb_page_builder_render($builder_data_){
	$settings_ = $builder_data_['settings'];
	$page_contents_ = $builder_data_['elementcontent'];

	$element_map_ = pb_page_builder_elements();
	global $pb_page_builder_element_classes;

	?>
<style type="text/css"><?=$settings_['style']?></style>

<?php foreach($page_contents_ as $element_data_){
	$element_class_ = $pb_page_builder_element_classes[$element_data_['name']];
	call_user_func_array(array($element_class_, "render"), array($element_data_));
} ?>

<script type="text/javascript"><?=$settings_['script']?></script>

	<?php
}

function pb_page_builder($content_ = null, $data_ = array()){
	global $pb_page_builder_admin_initialized;

	$builder_id_ = isset($data_['id']) ? $data_['id'] : "pb-page-builder-".pb_random_string(5);

	if(!$pb_page_builder_admin_initialized){ 

		$element_map_ = pb_page_builder_elements();
	?>
<script type="text/javascript">
jQuery(function($){

	/***
		PB PAGE BUILDER - PAGE SETTINGS MODAL
	**/

	window.pb_page_builder_version = "<?=PB_PAGE_BUILDER_VERSION?>";
	window.pbpage_builder_element_map = <?=json_encode($element_map_)?>;

	var pb_page_builder_page_settings_modal = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({

		}, options_);

		this._style_sheet_editor = this._target.find("[data-style-sheet-editor]");
		this._javascript_editor = this._target.find("[data-javascript-editor]");
		
		this._style_sheet_editor_module = CodeMirror.fromTextArea(this._style_sheet_editor[0], {
			lineNumbers: true,
			styleActiveLine: true,
			matchBrackets: true, 
			autoCloseBrackets : true,
			continueComments : true,
			mode: "css"
		});
		this._javascript_editor_module = CodeMirror.fromTextArea(this._javascript_editor[0], {
			lineNumbers: true,
			styleActiveLine: true,
			matchBrackets: true, 
			autoCloseBrackets : true,
			continueComments : true,
			tern: true,
			mode: "javascript"
		});
		
		this._callback = $.noop;

		this._target.on("shown.bs.modal", $.proxy(function(){
			this._style_sheet_editor_module.refresh();
			this._javascript_editor_module.refresh();
			
		}, this));

		this._modal_form = this._target.find("#pb-page-builder-page-settings-modal-form");
		this._modal_form.submit_handler($.proxy(function(){
			this._callback.apply(this, [this.to_json()]);
			this._target.modal("hide");
		}, this));
		
		this._target.data("pb-page-builder-page-settings-modal", this);
	}

	pb_page_builder_page_settings_modal.prototype.target = function(){
		return this._target;
	};

	pb_page_builder_page_settings_modal.prototype.open = function(default_, callback_){
		default_ = $.extend({
			'style' : "",
			'script' : "",
		}, default_);
		this._style_sheet_editor_module.setValue(default_['style'] ? default_['style'] : "");
		this._javascript_editor_module.setValue(default_['script'] ? default_['script'] : "");

		this._callback = (callback_ ? callback_ : $.noop);
		this._target.modal({
			show : true,
			backdrop : "static",
			keyboard : false,
		});
	};

	pb_page_builder_page_settings_modal.prototype.to_json = function(){
		var style_ = this._style_sheet_editor_module.getValue();
		var script_ = this._javascript_editor_module.getValue();

		return {
			style : style_,
			script : script_,
		}
	};

	$.fn.pb_page_builder_page_settings_modal = function(options_){
		var module_ = this.data("pb-page-builder-page-settings-modal");
		if(module_) return module_;
		return new pb_page_builder_page_settings_modal(this, options_);
	}

	/***
		PB PAGE BUILDER - ELEMENT
	**/

	var pb_page_builder_element = window.pb_page_builder_element = function(target_, page_builder_, key_, defaults_){
		this._target = target_;
		this._key = key_;
		this._target.data("pb-page-builder-element", this);
		this._page_builder = page_builder_;
		this._element_data = $.extend({}, defaults_);
		this._content = null;

		var is_loadable_ = window.pbpage_builder_element_map[key_]['loadable'] ? window.pbpage_builder_element_map[key_]['loadable'] : false;

		this._target.toggleClass("content" , !is_loadable_);
		this._target.toggleClass("loadable" , is_loadable_);

		var element_html_ = '<div class="wrap"> \
			<div class="col-action"> \
				<div class="element-info-frame"> \
					<div class="element-name" data-element-name>'+window.pbpage_builder_element_map[key_]['name']+'</div> \
				</div> \
				<div class="action-frame"> \
					<a href="" data-handle-btn class="action-btn handle-btn"> \
						<i class="icon material-icons">drag_handle</i> \
					</a> \
					<a href="" data-edit-btn class="action-btn edit-btn"> \
						<i class="icon material-icons">edit</i> \
					</a> \
					<a href="" data-delete-btn class="action-btn delete-btn"> \
						<i class="icon material-icons">close</i> \
					</a> \
				</div> \
			</div> \
			<div class="col-content"> \
				<div class="preview-frame" data-preview-frame></div> \ ';
			
			if(is_loadable_){
				element_html_ += '<div class="children-frame" data-children-frame> \
						<a data-add-element-btn="prepend" class="add-element-btn prepend" href=""><i class="material-icons icon">add_box</i> 요소추가</a> \
						<a data-add-element-btn="append" class="add-element-btn append" href=""><i class="material-icons icon">add_box</i> 요소추가</a> \
				</div>';
			}
			element_html_ += '</div> \
		</div>';

		this._target.append(element_html_);
		this._target.toggleClass("element-content-item", true);

		this._element_name_el = this._target.find("[data-element-name]");
		this._preview_frame_el = this._target.find("[data-preview-frame]");
		this._children_frame_el = this._target.find("> .wrap > .col-content > [data-children-frame]");

		this._handle_btn = this._target.find("[data-handle-btn]");
		this._edit_btn = this._target.find("[data-edit-btn]");
		this._delete_btn = this._target.find("[data-delete-btn]");

		this._handle_btn.click(function(){
			return false;
		});

		this._target.on("mouseenter", $.proxy(function(){

			if(this._children_frame_el.find(".element-content-item.hover").length > 0) return;
			this._target.toggleClass("hover", true);
			var closest_ = this._target.parent().closest(".element-content-item");
			if(closest_.length <= 0) return;
			closest_.pb_page_builder_element()._toggle_hover(false, true);


		}, this));

		this._target.on("mouseleave", $.proxy(function(){
			this._target.toggleClass("hover", false);
			var closest_ = this._target.parent().closest(".element-content-item");
			if(closest_.length <= 0) return;
			closest_.pb_page_builder_element()._toggle_hover(true);
		}, this));

		this._add_element_btns = this._target.find("[data-add-element-btn]");
		this._add_element_btns.click($.proxy(function(event_){
			var target_btn_ = $(event_.currentTarget);
			var add_method_ = target_btn_.attr("data-add-element-btn") === "append" ? this.append_element : this.prepend_element;

			window._pbpagebuilder_element_picker_modal_module.pick({
				parent : this._key
			},$.proxy(function(element_id_, element_data_){
				this['add_method'].apply(this['module'], [element_id_]);
			}, {
				module : this,
				add_method : add_method_,
			}));

			return false;

		}, this));

		this._edit_btn.click($.proxy(function(){
			this.edit();
			return false;
		},this));

		this._delete_btn.click($.proxy(function(){
			PB.confirm({
				title : "삭제확인",
				content : "해당 요소를 삭제하시겠습니까?",
				button1 : "삭제하기",
			}, $.proxy(function(c_){
				if(!c_) return;
				this.delete();
			}, this));
			return false;
		},this));

		this._children_frame_el.sortable({
			items : "> .element-content-item",
			helper: "clone",
			placeholder: "element-content-item-placeholder",
			handle : "[data-handle-btn]",
			connectWith : "[data-children-frame]:not([data-disconnected])",
			stop : $.proxy(function(){
				this._check_children();
			},this),
			start : function(event_, ui_){
				ui_.placeholder.toggleClass("hidden", false);
			},
			over : $.proxy(function(event_, ui_){
				var element_item_ = ui_.item.pb_page_builder_element();
				var parent_key_ = this._key;

				var parent_exp_ = window.pbpage_builder_element_map[element_item_._key]['parent'] ? window.pbpage_builder_element_map[element_item_._key]['parent'] : ["*"];
				var can_over_ = (parent_exp_.indexOf(parent_key_) >= 0 || parent_exp_.indexOf("*") >=0) && (parent_exp_.indexOf("!"+parent_key_) < 0);

				var children_key_ = element_item_._key;
				var children_exp_ = window.pbpage_builder_element_map[this._key]['children'] ? window.pbpage_builder_element_map[this._key]['children'] : ["*"];

				can_over_ = can_over_ && (children_exp_.indexOf(children_key_) >= 0 || children_exp_.indexOf("*") >= 0) && (children_exp_.indexOf("!"+children_key_) < 0);

				ui_.placeholder.toggleClass("hidden", !can_over_);
			}, this),
			receive : $.proxy(function(event_, ui_){
				var element_item_ = ui_.item.pb_page_builder_element();
				var parent_key_ = this._key;

				var parent_exp_ = window.pbpage_builder_element_map[element_item_._key]['parent'] ? window.pbpage_builder_element_map[element_item_._key]['parent'] : ["*"];
				var can_over_ = (parent_exp_.indexOf(parent_key_) >= 0 || parent_exp_.indexOf("*") >=0) && (parent_exp_.indexOf("!"+parent_key_) < 0);

				var children_key_ = element_item_._key;
				var children_exp_ = window.pbpage_builder_element_map[this._key]['children'] ? window.pbpage_builder_element_map[this._key]['children'] : ["*"];

				can_over_ = can_over_ && (children_exp_.indexOf(children_key_) >= 0 || children_exp_.indexOf("*") >=0) && (children_exp_.indexOf("!"+children_key_) < 0);

				if(!can_over_) $(ui_.sender).sortable('cancel');
				this._check_children();
			}, this)
		});

		this._check_children();
		this._update_preview();
	}

	pb_page_builder_element.prototype.prepend_element = function(key_, defaults_){
		var child_item_ = $("<div></div>");
		this._children_frame_el.prepend(child_item_);
		var element_class_ = window.pbpage_builder_element_map[key_]['edit_element_class'] ? window.pbpage_builder_element_map[key_]['edit_element_class'] : "pb_page_builder_element";
		var element_instance_ = child_item_[element_class_].apply(child_item_, [this._page_builder, key_, defaults_]);
		element_instance_.edit();
		this._check_children();
		return child_item_;
	}
	pb_page_builder_element.prototype.append_element = function(key_, defaults_){
		var child_item_ = $("<div></div>");
		this._children_frame_el.append(child_item_);
		var element_class_ = window.pbpage_builder_element_map[key_]['edit_element_class'] ? window.pbpage_builder_element_map[key_]['edit_element_class'] : "pb_page_builder_element";
		var element_instance_ = child_item_[element_class_].apply(child_item_, [this._page_builder, key_, defaults_]);
		element_instance_.edit();
		this._check_children();
		return child_item_;
	}
	pb_page_builder_element.prototype.content = function(content_){
		if(content_ !== undefined){
			this._content = content_;
		}
		return this._content;
	}

	pb_page_builder_element.prototype.to_xml = function(document_){
		var is_loadable_ = window.pbpage_builder_element_map[this._key]['loadable'] ? window.pbpage_builder_element_map[this._key]['loadable'] : false;


		var element_node_ = document_.createElement("element");
			element_node_.setAttribute("name", this._key);

		$.each(this._element_data, function(key_, value_){
			var property_node_ = document_.createElement("property");

			property_node_.setAttribute("name", key_);
			property_node_.appendChild(document.createTextNode(value_));
			element_node_.appendChild(property_node_);
		});

		var element_content_node_ = document_.createElement("elementcontent");

		this._children_frame_el.children(".element-content-item").each(function(){
			var child_node_ = $(this).pb_page_builder_element().to_xml(document_);
			element_content_node_.appendChild(child_node_);
		});

		element_node_.appendChild(element_content_node_);

		if(!is_loadable_){
			var content_ = this.content();
			element_content_node_.appendChild(document.createTextNode((content_ ? content_ : "")));
		}

		return element_node_;
	}
	pb_page_builder_element.prototype.apply_xml = function(xml_node_){
		var defaults_ = {};

		var property_nodes_ = pb_page_builder._child_node_by_name(xml_node_, "property", true);

		$.each(property_nodes_, function(){
			defaults_[this.getAttribute("name")] = this.textContent;
		});

		this._element_data = defaults_;
		var element_content_node_ = pb_page_builder._child_node_by_name(xml_node_, "elementcontent");

		if(window.pbpage_builder_element_map[this._key]['loadable']){

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

			this._check_children();
		}else{
			this.content(element_content_node_.textContent);
		}

		this._update_preview();
	}

	pb_page_builder_element.prototype.edit = function(){
		window._pbpagebuilder_element_edit_modal_module.edit(this._key, this._element_data, this.content(), $.proxy(function(results_, content_){
			this._element_data = results_;
			this._content = content_;
			this._update_preview();
		}, this));
	}
	pb_page_builder_element.prototype.delete = function(){
		var cloest_ = this._target.parent().closest(".element-content-item");
		this._target.remove();
		if(cloest_.length > 0) cloest_.pb_page_builder_element()._check_children();
		this._page_builder._check_children();
	}

	pb_page_builder_element.prototype._check_children = function(){
		var empty_toggled_ = this._children_frame_el.children(".element-content-item").length == 0;
		this._children_frame_el.toggleClass("empty", empty_toggled_);
		this._children_frame_el.sortable("refresh");
	}
	pb_page_builder_element.prototype._toggle_hover = function(toggled_, recv_){
		this._target.toggleClass("hover", toggled_);

		if(recv_){
			var closest_ = this._target.parent().closest(".element-content-item");
			if(closest_.length > 0){
				closest_.pb_page_builder_element()._toggle_hover(toggled_, recv_);	
			}
		}
	}
	pb_page_builder_element.prototype._update_preview = function(){
		var element_id_ = this._element_data['id'];
		
		var preview_html_ = "";

		if(element_id_ && element_id_ !== ""){
			preview_html_ += "<div class='preview-item'>#"+element_id_+"</div>";
		}

		if(pb_page_builder_element_edit_library[this._key]){
			preview_html_ += pb_page_builder_element_edit_library[this._key]['preview'].apply(this, [this._element_data, this.content()]);
		}
		this._preview_frame_el.html(preview_html_);
	}

	$.fn.pb_page_builder_element = function(page_builder_, key_, defaults_){
		var module_ = this.data("pb-page-builder-element");
		if(module_) return module_;
		return new pb_page_builder_element(this, page_builder_, key_, defaults_);
	}

	/***
		PB PAGE BUILDER - ELEMENT PICKER MODAL
	**/
	var pb_page_builder_element_picker_modal = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({

		}, options_);

		this._element_list_el = this._target.find("[data-element-list]");
		this._element_search_form = this._target.find("[data-element-search-form]");
		this._callback = $.noop;

		this._last_element_list = [];
		this._target.data("pb-page-builder-element-picker-modal", this);

		this._element_search_form.submit_handler($.proxy(function(){
			this.load();
		}, this));
	}

	pb_page_builder_element_picker_modal.prototype.target = function(){
		return this._target;
	};
	pb_page_builder_element_picker_modal.prototype.toggle_loading = function(toggle_){
		this._target.toggleClass("loading", toggle_);
	};

	pb_page_builder_element_picker_modal.prototype.load = function(){
		this.toggle_loading(true);

		PB.post("page-builder-load-element", {
			options : this._element_search_form.serialize_object(),
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "데이타 불러오는 중, 에러가 발생했습니다.",
				});
				return;
			}

			this._last_element_list = response_json_.elements;
			this._element_list_el.empty();

			var module_ = this;
			$.each(this._last_element_list, function(key_, element_data_){
				var element_item_html_ =
					'<div class="element-item" data-element-id="'+key_+'"><a href="" class="wrap">' +
						'<div class="col-icon"><div class="icon-wrap">';


				if(element_data_['icon']){
					element_item_html_ += '<img src="'+element_data_['icon']+'" class="icon">';
				}

				element_item_html_ += '</div></div>' +
						'<div class="col-name">' +
							'<div class="name">'+element_data_['name']+'</div>' +
							'<div class="desc">'+(element_data_['desc'] ? element_data_['desc'] : "")+'</div>' +
						'</div>' +
					'</a></div>';

				var element_item_ = $(element_item_html_);
				module_._element_list_el.append(element_item_);
				element_item_.data("element-data", element_data_);

				element_item_.click($.proxy(function(event_){
					var t_target_el_ = $(event_.currentTarget);
					this._callback.apply(this, [t_target_el_.attr("data-element-id"), t_target_el_.data("element-data")]);
					this._target.modal("hide");
					return false;
				}, module_));
			});

			if(this._last_element_list.length <= 0){
				var cond_data_ = this._element_search_form.serialize_object();

				if(cond_data_['keyword'] && cond_data_['keyword'] !== ""){
					this._element_list_el.append("<div class='empty-text'>검색된 요소가 없습니다.</div>");	
				}else{
					this._element_list_el.append("<div class='empty-text'>추가할 수 있는 요소가 없습니다.</div>");
				}
				
			}

			this.toggle_loading(false);

		},this));
	};

	pb_page_builder_element_picker_modal.prototype.pick = function(options_, callback_){
		options_ = $.extend({
			parent : null,
			keyword : null,
		},options_);
		this._element_search_form.find("[name='parent']").val(options_['parent']);
		this._element_search_form.find("[name='keyword']").val(options_['keyword']);

		this.load();
		this._callback = callback_ || $.noop;
		this._target.modal({
			show : true,
			backdrop : "static",
			keyboard : false,
		});
	};

	$.fn.pb_page_builder_element_picker_modal = function(options_){
		var module_ = this.data("pb-page-builder-element-picker-modal");
		if(module_) return module_;
		return new pb_page_builder_element_picker_modal(this, options_);
	}

	/***
		PB PAGE BUILDER - ELEMENT EDIT MODAL
	**/
	var pb_page_builder_element_edit_modal = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({

		}, options_);

		this._modal_body_el = this._target.find(".modal-body");
		this._modal_footer_el = this._target.find(".modal-footer");
		this._callback = $.noop;

		this._target.data("pb-page-builder-element-edit-modal", this);

		this._edit_form = this._target.find("#pb-page-builder-element-edit-modal-form");
		this._edit_form.submit_handler($.proxy(function(){
			var edit_data_ = this._edit_form.serialize_object();
			var content_ = edit_data_['content'] ? edit_data_['content'] : null;

			delete edit_data_['content'];

			this._callback.apply(this, [edit_data_, content_]);
			this._target.modal("hide");
		}, this));
	}

	pb_page_builder_element_edit_modal.prototype.target = function(){
		return this._target;
	};
	
	pb_page_builder_element_edit_modal.prototype.edit = function(element_id_, defaults_, content_, callback_){
		this._callback = callback_ || $.noop;
		this._modal_body_el.html('<div class="loading-frame"> \
				<div class="pb-loading-indicator loading-indicator"></div> \
			</div>');
		this._modal_footer_el.toggle(false);

		PB.post("page-builder-load-edit-form", {
			element_id : element_id_,
			element_data : defaults_,
			content : content_
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "수정폼을 불러오는 중, 에러가 발생했습니다.",
				});
				return;
			}
			this._modal_body_el.html(response_json_.form_html);
			this._modal_footer_el.toggle(true);
			this._modal_body_el.find(".pb-page-builder-element-edit-nav-tabs > .nav-tabs a:first").tab('show');
		},this));

		this._target.modal({
			show : true,
			backdrop : "static",
			keyboard : false,
		});
	};

	$.fn.pb_page_builder_element_edit_modal = function(options_){
		var module_ = this.data("pb-page-builder-element-edit-modal");
		if(module_) return module_;
		return new pb_page_builder_element_edit_modal(this, options_);
	}

	/***
		PB PAGE BUILDER
	**/
	window.pb_page_builder = window.pb_page_builder = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			
		}, options_);

		this._add_element_btn = this._target.find("[data-element-add-element-btn]");
		this._style = "";
		this._script = "";

		this._add_element_btn.click($.proxy(function(){
			this.pick_element({
				parent : "document"
			});
			return false;
		}, this));

		this._page_settings_btn = this._target.find("[data-element-setting-btn]");
		this._page_settings_btn.click($.proxy(function(){
			this.open_page_settings();
			return false;
		}, this));

		this._fullscreen_btn = $("[data-fullscreen-btn]");
		this._fullscreen_btn.click($.proxy(function(){
			this._target.toggleClass("fullscreen");
			return false;
		}, this));

		this._page_element_content_list_el = this._target.children("[data-children-frame]");
		this._target.data("pb-page-builder", this);


		this._target.find("[data-add-element-btn]").click($.proxy(function(event_){
			this.pick_element({}, $(event_.currentTarget).attr("data-add-element-btn"))
			return false;
		}, this));

		this._page_element_content_list_el.sortable({
			items : "> .element-content-item",
			placeholder: "element-content-item-placeholder",
			helper: "clone",
			handle : "[data-handle-btn]",
			connectWith : "[data-children-frame]:not([data-disconnected])",
			stop : $.proxy(function(){
				this._check_children();
			},this),
			start : $.proxy(function(event_, ui_){
				ui_.placeholder.toggleClass("hidden", false);
			}, this),
			over : $.proxy(function(event_, ui_){
				var element_item_ = ui_.item.pb_page_builder_element();

				var parent_key_ = "document";
				var parent_exp_ = window.pbpage_builder_element_map[element_item_._key]['parent'] ? window.pbpage_builder_element_map[element_item_._key]['parent'] : ["*"];
				var can_over_ = (parent_exp_.indexOf(parent_key_) >= 0 || parent_exp_.indexOf("*") >=0) && (parent_exp_.indexOf("!"+parent_key_) < 0);

				var children_key_ = element_item_._key;
				var children_exp_ = ["*"];

				can_over_ = can_over_ && (children_exp_.indexOf(children_key_) >= 0 || children_exp_.indexOf("*") >= 0) && (children_exp_.indexOf("!"+children_key_) < 0);

				ui_.placeholder.toggleClass("hidden", !can_over_);

			}, this),
			receive : $.proxy(function(event_, ui_){
				var element_item_ = ui_.item.pb_page_builder_element();
				
				var parent_key_ = "document";
				var parent_exp_ = window.pbpage_builder_element_map[element_item_._key]['parent'] ? window.pbpage_builder_element_map[element_item_._key]['parent'] : ["*"];
				var can_over_ = (parent_exp_.indexOf(parent_key_) >= 0 || parent_exp_.indexOf("*") >=0) && (parent_exp_.indexOf("!"+parent_key_) < 0);

				var children_key_ = element_item_._key;
				var children_exp_ = ["*"];

				can_over_ = can_over_ && (children_exp_.indexOf(children_key_) >= 0 || children_exp_.indexOf("*") >=0) && (children_exp_.indexOf("!"+children_key_) < 0);

				if(!can_over_) $(ui_.sender).sortable('cancel');
				this._check_children();
			}, this)
		});

		this._check_children();
	};

	pb_page_builder.prototype.target = function(){
		return this._target;
	}

	pb_page_builder.prototype.open_page_settings = function(){
		window._pbpagebuilder_page_settings_modal_module.open({
			style : this._style,
			script : this._script,
		}, $.proxy(function(results_){
			this._style = results_['style'];
			this._script = results_['script'];
			this._update_page_settings_btn();
		},this));
	}

	pb_page_builder.prototype._update_page_settings_btn = function(){
		var has_data_ = (this._style && this._style !== "") || (this._script && this._script !== "");
		has_data_ = has_data_ === true;
		this._page_settings_btn.toggleClass("has-data", has_data_);
	}
	
	pb_page_builder.prototype.prepend_element = function(element_id_, defaults_){
		var element_content_item_el_ = $("<div>");
		this._page_element_content_list_el.prepend(element_content_item_el_);

		var element_class_ = window.pbpage_builder_element_map[element_id_]['edit_element_class'] ? window.pbpage_builder_element_map[element_id_]['edit_element_class'] : "pb_page_builder_element";
		var element_instance_ = element_content_item_el_[element_class_].apply(element_content_item_el_, [this, element_id_, defaults_]);
		element_instance_.edit();
		this._check_children();

		return element_content_item_el_
	}
	pb_page_builder.prototype.append_element = function(element_id_, defaults_){
		var element_content_item_el_ = $("<div>");
		this._page_element_content_list_el.append(element_content_item_el_);
		var element_class_ = window.pbpage_builder_element_map[element_id_]['edit_element_class'] ? window.pbpage_builder_element_map[element_id_]['edit_element_class'] : "pb_page_builder_element";
		var element_instance_ = element_content_item_el_[element_class_].apply(element_content_item_el_, [this, element_id_, defaults_]);
		element_instance_.edit();
		this._check_children();

		return element_content_item_el_;
	}


	pb_page_builder.prototype.pick_element = function(options_, add_method_){
		options_ = $.extend({
			parent : null,
		},options_);

		add_method_ = add_method_ || "append";

		window._pbpagebuilder_element_picker_modal_module.pick({
			parent : options_['parent']
		},$.proxy(function(element_id_, element_data_){
			this['add_method'].apply(this['module'], [element_id_]);
		}, {
			module : this,
			add_method : add_method_ === "prepend" ? this.prepend_element : this.append_element,
		}));
	}

	pb_page_builder.prototype._check_children = function(){
		var empty_ = (this._page_element_content_list_el.find(".element-content-item").length <= 0);
		if(empty_){
			this._page_element_content_list_el.find(".empty-state-text").remove();
			this._page_element_content_list_el.append(
				'<div class="empty-state-text"> \
					<a href="" data-element-add-element-btn class="add-element-btn"><i class="icon material-icons">add_circle</i>요소추가</a> 하여 페이지를 만들어 보세요! \
				</div>'
			);
			this._page_element_content_list_el.find("[data-element-add-element-btn]").click($.proxy(function(){
				this.pick_element();
				return false;
			},this));
		}else{
			this._page_element_content_list_el.find(".empty-state-text").remove();
		}

		this._page_element_content_list_el.toggleClass("empty", empty_);
		this._page_element_content_list_el.sortable("refresh");
	}

	pb_page_builder.prototype.target = function(){
		return this._target;
	};

	pb_page_builder.prototype.to_xml = function(){
		var document_ = document.implementation.createDocument("", "", null);
		var root_node_ = document_.createElement("pbpagebuilder");
			root_node_.setAttribute("version", window.pb_page_builder_version);

		var settings_node_ = document_.createElement("settings");
		var settings_style_node_ = document_.createElement("style");
		var settings_script_node_ = document_.createElement("script");

		settings_style_node_.appendChild(document.createTextNode(this._style));
		settings_script_node_.appendChild(document.createTextNode(this._script));

		settings_node_.appendChild(settings_style_node_);
		settings_node_.appendChild(settings_script_node_);
		root_node_.appendChild(settings_node_);

		var content_node_ = document_.createElement("pagecontent");

		this._page_element_content_list_el.children(".element-content-item").each(function(){
			var element_node_ = $(this).pb_page_builder_element().to_xml(document_);
			content_node_.appendChild(element_node_);
		});

		root_node_.appendChild(content_node_);
		document_.appendChild(root_node_);

		if(XMLSerializer){
			return (new XMLSerializer()).serializeToString(document_);	
		}

		if(window.ActiveXObject){ //IE
			return document_.xml;
		}

		return "not supported";
		
	};

	pb_page_builder._child_node_by_name = function(parent_, name_, to_array_){
		to_array_ = (to_array_ === true);
		var results_ = [];
		for(var child_index_= 0; child_index_<parent_.childNodes.length;++child_index_){
			var child_node_ = parent_.childNodes[child_index_];

			if(child_node_.nodeType === 1 && child_node_.nodeName === name_){
				results_.push(child_node_);
			}
		}

		if(results_.length > 1 || to_array_) return results_;
		return results_[0];
	}
	pb_page_builder.prototype.apply_xml = function(xml_string_){
		var document_ = null;
		if(window.DOMParser){
			document_ = new window.DOMParser().parseFromString(xml_string_, "text/xml");
		}else if(window.ActiveXObject && new window.ActiveXObject("Microsoft.XMLDOM")){
			document_ = new window.ActiveXObject("Microsoft.XMLDOM");
	        document_.async = "false";
	        document_.loadXML(xml_string_);	
		}else{
			PB.alert({
				title : "호환성오류",
				content : "XML Parsing을 지원하지 않는 브라우져입니다. 신형브라우져로 다시 시도하여 주세요.",
			});
			return;
		}

		var root_children_ = document_.documentElement;

		var settings_node_ = pb_page_builder._child_node_by_name(root_children_, "settings");
		var pagecontent_node_ = pb_page_builder._child_node_by_name(root_children_, "pagecontent");

		if(!settings_node_ || !pagecontent_node_){
			console.error("올바른 형식의 문서가 아닙니다.");
			return;
		}

		var settings_style_node_ = pb_page_builder._child_node_by_name(settings_node_, "style");
		var settings_script_node_ = pb_page_builder._child_node_by_name(settings_node_, "script");

		this._style = settings_style_node_.textContent || "";
		this._script = settings_script_node_.textContent || "";
		this._update_page_settings_btn();

		this._page_element_content_list_el.children(".element-content-item").remove();

		for(var child_index_ = 0; child_index_<pagecontent_node_.childNodes.length; ++child_index_){
			var child_node_ = pagecontent_node_.childNodes[child_index_];
			var element_id_ = child_node_.getAttribute("name");

			var child_item_ = $("<div></div>");
			this._page_element_content_list_el.append(child_item_);
			var element_class_ = window.pbpage_builder_element_map[element_id_]['edit_element_class'] ? window.pbpage_builder_element_map[element_id_]['edit_element_class'] : "pb_page_builder_element";
			var element_instance_ = child_item_[element_class_].apply(child_item_, [this, element_id_]);
			element_instance_.apply_xml(child_node_);
		}

		this._check_children();

	};

	$.fn.pb_page_builder = function(options_){
		var module_ = this.data("pb-page-builder");
		if(module_) return module_;
		return new pb_page_builder(this, options_);
	}

	/***
		PB PAGE BUILDER - ELEMENT LIBRARY
	**/

	window.pb_page_builder_element_edit_library = {};

	pb_page_builder.add_element_edit_library = function(element_id_, data_){
		data_ = $.extend({
			'preview' : $.noop,
		}, data_);
		pb_page_builder_element_edit_library[element_id_] = data_;
	}
});
</script>

<?php
		pb_hook_do_action('pb_page_builder_admin_initialize');
		$pb_page_builder_admin_initialized = true;
	}

?>
<div class="pb-page-builder" id="<?=$builder_id_?>">
	
	<div class="page-builder-navbar"><div class="wrap">
		<div class="col-left">
			<img src="<?=PB_LIBRARY_URL?>img/page-builder/icon.png" class="logo-image">
		</div>
		<div class="col-right">
			<a href="" data-element-add-element-btn class="btn btn-default add-element-btn"><i class="icon material-icons">add_circle</i>요소추가</a>
			<a href="" data-element-setting-btn class="icon-link page-settings-btn"><i class="icon material-icons">settings</i></a>
			<a href="" data-fullscreen-btn class="icon-link fullscreen-btn">
				<i class="icon material-icons on">fullscreen</i>
				<i class="icon material-icons off">fullscreen_exit</i>
			</a>

		</div>
	</div></div>

	<div class="element-content-list empty" data-children-frame data-page-element-item="document">
		<a data-add-element-btn="prepend" class="add-element-btn prepend" href=""><i class="material-icons icon">add_box</i> 요소추가</a>
		<a data-add-element-btn="append" class="add-element-btn append" href=""><i class="material-icons icon">add_box</i> 요소추가</a>
	</div>

	<div class="copyrights">© 2019 Paul&Bro Company All Rights Reserved. v<?=PB_PAGE_BUILDER_VERSION?></div>

</div>
<script type="text/xmldata" id="<?=$builder_id_?>-defaults"><?=htmlentities($content_)?></script>

<script type="text/javascript">
jQuery(document).ready(function(){
	window._pbpagebuilder_page_settings_modal_module = $("#pb-page-builder-page-settings-modal").pb_page_builder_page_settings_modal();
	window._pbpagebuilder_element_picker_modal_module = $("#pb-page-builder-element-picker-modal").pb_page_builder_element_picker_modal();
	window._pbpagebuilder_element_edit_modal_module = $("#pb-page-builder-element-edit-modal").pb_page_builder_element_edit_modal();
	var page_builder_ = $("#<?=$builder_id_?>").pb_page_builder();

	var default_content_ = $('<textarea />').html($("#<?=$builder_id_?>-defaults").html()).text();
	page_builder_.apply_xml(default_content_);
});
</script>

<?php 
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-element.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-ajax.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-builtin.php');
// include(PB_DOCUMENT_PATH . 'includes/page-builder/page-builder-devmenu.php');

?>