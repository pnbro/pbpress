jQuery(function($){

	/***
		MENU ITEM EDIT MODAL
	**/
	var pb_menu_item_edit_modal = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({

		}, options_);

		this._modal_body_el = this._target.find(".modal-body");
		this._common_form_el = this._modal_body_el.children("#pb-menu-item-edit-modal-form");
		this._meta_form_el = this._modal_body_el.children("#pb-menu-item-edit-modal-meta-form");
		this._modal_footer_el = this._target.find(".modal-footer");
		this._callback = $.noop;

		this._target.data("pb-menu-item-edit-modal", this);

		this._common_form_el.validator();
		this._meta_form_el.validator();

		this._common_form_el.submit_handler($.proxy(function(){
			this._done();
		},this));
		this._meta_form_el.submit_handler($.proxy(function(){
			this._done();
		},this));

		this._submit_btn = this._target.find("[data-submit-btn]");
		this._submit_btn.click($.proxy(function(){
			this._done();
			return false;
		}, this));
	}

	pb_menu_item_edit_modal.prototype.target = function(){
		return this._target;
	};
	
	pb_menu_item_edit_modal.prototype.edit = function(item_data_, item_meta_data_, callback_){
		this._callback = callback_ || $.noop;
		this._common_form_el.html('<div class="loading-frame"> \
				<div class="pb-loading-indicator loading-indicator"></div> \
			</div>');
		this._modal_footer_el.toggle(false);

		PB.post("menu-editor-load-edit-item-form", {
			item_data : item_data_,
			item_meta_data : item_meta_data_,
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "수정폼을 불러오는 중, 에러가 발생했습니다.",
				});
				return;
			}
			this._common_form_el.html(response_json_.form_html);
			this._meta_form_el.html(response_json_.meta_form_html);

			this._common_form_el.validator().refresh();
			this._meta_form_el.validator().refresh();
			this._modal_footer_el.toggle(true);
		},this));

		this._target.modal({
			show : true,
			backdrop : "static",
			keyboard : false,
		});
	};

	pb_menu_item_edit_modal.prototype._done = function(){
		this._common_form_el.validator().validate($.proxy(function(result1_){
			if(!result1_) return;

			this._meta_form_el.validator().validate($.proxy(function(result2_){
				if(!result2_) return;
				
				var item_data_ = this._common_form_el.serialize_object();
				var item_meta_data_ = this._meta_form_el.serialize_object();

				this._callback.apply(this, [item_data_, item_meta_data_]);
				this._target.modal("hide");
			},this))
		}, this));
	}

	$.fn.pb_menu_item_edit_modal = function(options_){
		var module_ = this.data("pb-menu-item-edit-modal");
		if(module_) return module_;
		return new pb_menu_item_edit_modal(this, options_);
	}


	var pb_menu_editor = function(target_){
		this._target = target_;

		this._menu_edit_form = this._target.find("#pb-menu-editor-form");
		this._menu_edit_submit_btn_frame = this._menu_edit_form.find("[data-submit-btn-frame]");

		this._menu_edit_form.find("[name='menu_title']").change($.proxy(function(event_){
			var menu_id_ = this.current_menu_id();
			if(!menu_id_ || menu_id_ < 0) return;

			this._menu_selector.find("[value='"+menu_id_+"']").text($(event_.currentTarget).val());
		}, this));

		this._menu_selector = this._target.find("[data-menu-selector]");
		this._menu_selector.change($.proxy(function(event_){
			var target_el_ = $(event_.currentTarget);

			if(target_el_.find(":selected").attr("data-add-new-menu-option") === "Y"){
				this.add_new_menu();
				return;
			}
			this.load_menu(target_el_.sval());
		}, this));

		this._menu_list = this._target.find("[data-menu-list]");
		this._menu_list_frame = this._menu_list.parent();

		this._menu_list.sortable({
			group : "pb-menu-editor",
			// handle : "[data-handle-btn]",
			// forceFallback: true,
			// swapThreshold: 0.1,
			// invertSwap : true,
			ghostClass: "placeholder",
		});

		this._menu_edit_form.validator();
		this._menu_edit_form.submit_handler($.proxy(function(){
			this.do_update();
		},this));


		this._menu_add_handler = {};
		this._menu_target_tab_content = this._target.find("#pb-menu-target-tab-content");
		this._add_menu_item_btn = this._target.find("[data-add-menu-item-btn]");

		var module_ = this;
		this._menu_target_tab_content.find("form").each(function(){
			var target_form_el_ = $(this);
			target_form_el_.validator();
			target_form_el_.submit_handler($.proxy(function(){

				var current_menu_id_ = this['module'].current_menu_id();

				if(current_menu_id_ === null){
					PB.alert({
						title : "메뉴확인",
						content : "항목을 추가할 메뉴를 먼저 선택하세요",
					});
					return false;
				}

				var target_form_el_ = this['target_form'];
				if(!target_form_el_.validator().is_valid()) return false;

				var results_ = this['module']._menu_add_handler[target_form_el_.attr("data-menu-target-tab-form")].apply(this['module'], [target_form_el_]);
				if(!results_) return false;

				for(var t_index_=0;t_index_<results_.length;++t_index_){
					var target_data_ = results_[t_index_];
					this['module'].add_item(target_data_['item_data'], target_data_['item_meta_data']);
				}
			},{
				module : module_,
				target_form : target_form_el_
			}));
		});

		this._add_menu_item_btn.click($.proxy(function(){
			var target_tab_el_ = this._menu_target_tab_content.find("[data-menu-target-tab].active");
			var target_form_el_ = target_tab_el_.children("form");
			target_form_el_.validator().validate($.proxy(function(results_){
				if(!results_) return;
				this.submit();
			}, target_form_el_));
			return false;
		},this));

		this._delete_btn = this._target.find("[data-delete-btn]");
		this._delete_btn.toggle(false);

		this._delete_btn.click($.proxy(function(){
			PB.confirm({
				title : "삭제확인",
				content : "해당 메뉴를 삭제하시겠습니까?",
				button1 : "삭제하기"
			},$.proxy(function(c_){
				if(!c_) return;
				this.do_delete();
			},this));
		},this));


		this.empty_state();
	}	

	pb_menu_editor.prototype.current_menu_id = function(){
		var current_menu_id_ = parseInt(this._menu_selector.sval());
		if(isNaN(current_menu_id_)) return null;
		return current_menu_id_;
	}

	pb_menu_editor.prototype.register_add_handler = function(key_, func_){
		this._menu_add_handler[key_] = func_;
	}

	pb_menu_editor.prototype.add_item = function(data_, meta_data_, add_to_){

		var category_name_ = window._pb_menu_editor_categories[data_['category']] ? window._pb_menu_editor_categories[data_['category']] : "";

		var item_html_ = '<li class="pb-menu-item" data-menu-item="'+data_['id']+'"> \
			<div class="wrap"> \
				<div class="title" data-menu-item-title>'+data_['title']+'</div> \
				<small>'+category_name_+'</small> \
				<a href="" data-delete-btn class="delete-btn"><i class="icon material-icons">delete</i></a> \
				<a href="" data-edit-btn class="edit-btn"><i class="icon material-icons">edit</i></a> \
			</div> \
			<ul class="submenu-list pb-menu-list"></ul> \
		</li>';

		var menu_item_ = $(item_html_);

		if(add_to_){
			add_to_.append(menu_item_);
		}else{
			this._menu_list.append(menu_item_);	
		}
		

		menu_item_.find(".pb-menu-list").sortable({
			group : "pb-menu-editor",
			// handle : "[data-handle-btn]",
			fallbackOnBody: true,
			swapThreshold: 0.5,
			ghostClass: "placeholder",
		});

		menu_item_.data("item-data", data_);
		menu_item_.data("item-meta-data", meta_data_);
		menu_item_.find("[data-edit-btn]").click($.proxy(function(event_){
			var t_menu_item_el_ = $(this);
			var item_data_ = t_menu_item_el_.data("item-data");
			var item_meta_data_ = t_menu_item_el_.data("item-meta-data");
			window.pb_menu_item_edit_modal.edit(item_data_, item_meta_data_, $.proxy(function(t_item_data_, t_item_meta_data_){
				this.data("item-data", t_item_data_);
				this.data("item-meta-data", t_item_meta_data_);
				this.find("> .wrap > [data-menu-item-title]").text(t_item_data_['title']);
			},t_menu_item_el_));

			return false;
		}, menu_item_));

		menu_item_.find("[data-delete-btn]").click($.proxy(function(event_){

			PB.confirm({
				title : "삭제확인",
				content : "해당 메뉴항목을 삭제하시겠습니까?",
				button1 : "삭제하기",
				button1Classes : "btn btn-danger"
			}, $.proxy(function(c_){
				if(!c_) return;
				this['module'].remove_item(this['menu_item']);
			}, this));
			
			return false;
		}, {
			module : this,
			menu_item : menu_item_
		}));

		this._check_menu_list_state();
		return menu_item_;
	}

	pb_menu_editor.prototype.remove_item = function(menu_item_){
		var children_ = menu_item_.children(".pb-menu-list").children();

		menu_item_.children(".wrap").animate({opacity : 0}, 300, $.proxy(function(){
			if(this['children'].length > 0){
				this['children'].detach();
				this['menu_item'].after(this['children']);
			}
			
			this['menu_item'].remove();	
		},{
			children : children_,
			menu_item : menu_item_,
		}));
		
	};

	pb_menu_editor.prototype.empty_state = function(){
		this._target.toggleClass("editing loading", false);
		this._menu_edit_submit_btn_frame.toggle(false);
	}
	pb_menu_editor.prototype.toggle_editable = function(toggle_){
		this._target.toggleClass("editing", toggle_);
		this._menu_edit_submit_btn_frame.toggle(toggle_);
	}
	pb_menu_editor.prototype.toggle_loading = function(toggle_){
		this._target.toggleClass("loading", toggle_);
		this._menu_edit_submit_btn_frame.toggle(toggle_);
	}

	pb_menu_editor.prototype.add_new_menu = function(){
		this.toggle_editable(true);
		this._menu_edit_form.find("[name='menu_title']").val("");
		this._menu_edit_form.find("[name='menu_slug']").val("");
		this._menu_edit_form.validator().refresh();
		this._menu_list.empty();
		this._check_menu_list_state();
		this._menu_edit_form.validator().refresh();

		this._delete_btn.toggle(false);
	} 
	pb_menu_editor.prototype.load_menu = function(menu_id_){
		this.toggle_loading(false);
		this._menu_list.empty();

		PB.post("menu-editor-load-menu", {
			menu_id : menu_id_
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "메뉴정보를 불러오는 중, 에러가 발생했습니다",
				});
				return;
			}

			var menu_data_ = response_json_.menu_data;
			var menu_tree_ = response_json_.menu_tree;
			this._menu_edit_form.find("[name='menu_title']").val(menu_data_['title']);
			this._menu_edit_form.find("[name='menu_slug']").val(menu_data_['slug']);
			this.toggle_editable(true);


			function _pb_menu_tree_recv_add(module_, item_, children_){
				children_ = (!children_ ? [] : children_);

				$.each(children_, function(){
					var added_item_ = module_.add_item(this['item_data'], this['item_meta_data'], item_.children(".submenu-list"));	
					_pb_menu_tree_recv_add(module_, added_item_, this['children']);
				});
			}

			var module_ = this;
			$.each(menu_tree_, function(){
				var added_item_ = module_.add_item(this['item_data'], this['item_meta_data']);
				_pb_menu_tree_recv_add(module_, added_item_, this['children']);
			});

			this._menu_list_frame.toggleClass("empty-state", (menu_tree_.length <= 0));
			this._delete_btn.toggle(true);

		}, this), true);
	} 

	pb_menu_editor.prototype._check_menu_list_state = function(){
		var empty_toggled_ = this._menu_list.children().length <= 0;
		this._menu_list_frame.toggleClass("empty-state", empty_toggled_);
	}

	pb_menu_editor.prototype.do_delete = function(){
		var menu_id_ = this.current_menu_id();
			menu_id_ = (menu_id_ === -9 ? null : menu_id_);

		if(!menu_id_) return;

		PB.post("menu-editor-do-delete", {
			menu_id : menu_id_,
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "메뉴 삭제 중, 에러가 발생했습니다",
				});
				return;
			}

			var option_group_ = this['module']._menu_selector.children("[data-menu-list-option-group]");
			option_group_.find("[value='"+this['menu_id']+"']").remove();
			this['module']._menu_selector.change();
		}, {
			module : this,
			menu_id : menu_id_,
		}), true);
	};

	pb_menu_editor.prototype.do_update = function(){
		var menu_id_ = this.current_menu_id();
			menu_id_ = (menu_id_ === -9 ? null : menu_id_);

		var update_data_ = {
			menu_id : menu_id_,
			menu_title : this._menu_edit_form.find("[name='menu_title']").val(),
			menu_slug : this._menu_edit_form.find("[name='menu_slug']").val(),
			children : []
		};

		var target_menu_ids_ = [];

		function _pb_menu_editor_recv_parse_item(item_){
			var results_ = {
				item_data : item_.data("item-data"),
				item_meta_data : item_.data("item-meta-data"),
				children : [],
			};

			var submenu_list_ = item_.find("> .submenu-list");

			var child_els_ = submenu_list_.children("[data-menu-item]");
			var children_ = [];

			child_els_.each(function(){
				var child_data_ = _pb_menu_editor_recv_parse_item($(this));
				children_.push(child_data_);
			});

			if(results_['item_data']['id'] && results_['item_data']['id'] !== ""){
				target_menu_ids_.push(results_['item_data']['id']);
			}

			results_['children'] = children_;
			return results_;
		}

		var results_ = [];
		var root_children_ = this._menu_list.children("[data-menu-item]");
		for(var root_index_=0;root_index_<root_children_.length;++root_index_){
			var child_el_ = $(root_children_[root_index_]);
			var child_data_ = _pb_menu_editor_recv_parse_item(child_el_);
			results_.push(child_data_);
		}

		update_data_['children'] = results_;

		PB.post("menu-editor-do-update", {
			menu_data : update_data_,
			target_menu_ids : target_menu_ids_
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "메뉴 저장 중, 에러가 발생했습니다",
				});
				return;
			}

			this['module'].load_menu(response_json_.menu_id);

			if(this['update_data']['menu_id'] === null){ //inserted
				var option_group_ = this['module']._menu_selector.children("[data-menu-list-option-group]");
				option_group_.append("<option value='"+response_json_.menu_id+"'>"+this['update_data']['menu_title']+"</option>");
				this['module']._menu_selector.val(response_json_.menu_id);
			}

		}, {
			module : this,
			update_data : update_data_,
		}), true);
	}

	$.fn.pb_menu_editor = function(){
		var module_ = this.data("pb-menu-editor");
		if(!module_) return new pb_menu_editor(this);
		return module_;
	}
});
jQuery(document).ready(function(){
	window.pb_menu_item_edit_modal = $("#pb-menu-item-edit-modal").pb_menu_item_edit_modal();
	window.pb_menu_editor = $("#pb-menu-editor").pb_menu_editor();
	window.pb_menu_editor._menu_selector.change();
});