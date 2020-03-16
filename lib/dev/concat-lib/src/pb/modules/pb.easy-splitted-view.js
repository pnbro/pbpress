jQuery(function($){
	var _pb_easy_splitted_view = (function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			"master-info-load-action" : this._target.attr("data-master-loader"),
			"master-info-loaded" : $.noop,
			"detail-list-loaded" : $.noop,
		}, options_);
	
		this._master_form = this._target.find("[data-master-table-form]");
		this._detail_form = this._target.find("[data-detail-table-form]");

		this._master_table = $(this._target.attr("data-master-table-id"));
		this._detail_table = $(this._target.attr("data-detail-table-id"));

		this._master_table_module = this._master_table.pbeasytable();
		this._master_info_group = $("[data-master-info-group]");
		this._master_info_group_html = this._master_info_group.html();
		
		this._detail_table_module = this._detail_table.pbeasytable();

		if(this._detail_form.find(":input[name='master_id']").length <= 0){
			this._detail_form.append("<input type='hidden' name='master_id'>");
		}		

		//initialize master table
		this._master_table.on("pbeasytableloaded", $.proxy(function(event_, row_data_, row_el_){
			this.current_master_id(null);
			
			row_el_.find("a[data-master-id]").click($.proxy(function(event_){
				var master_id_ = $(event_.target).attr("data-master-id");
				this.current_master_id(master_id_);
				return false;
			}, this));

		}, this));

		this._col_master_el = this._target.find(".wrap > .col-master");
		this._col_detail_el = this._target.find(".wrap > .col-detail");
		this._col_detail_el.addClass("in out end");

		this._col_detail_el.append('<a href="#" class="mobile-close-btn"><i class="icon material-icons">close</i></a>');

		this._col_detail_el.children(".mobile-close-btn").click($.proxy(function(){
			this.toggle_detail_view();
			return false;
		}, this));

		//initialize master table
		this._detail_table.on("pbeasytableloaded", $.proxy(function(event_, row_data_, row_el_){
			if(this._cached_master_info){
				this._options['detail-list-loaded'].apply(this, [this._cached_master_info, row_data_, row_el_]);
			}
			
		}, this));

		this._current_master_id = null;
		this._target.toggleClass("master-not-selected", true);
		this._target.data("pb-easysplitted-view-module", this);

		this.current_master_id(null);

	});

	_pb_easy_splitted_view.prototype.current_master_id = (function(master_id_){
		if(master_id_ !== undefined){

			this._current_master_id = master_id_;
			this._empty_master_info_group();

			if(this._current_master_id){
				this.load_master_info(master_id_, $.proxy(function(){
					this._target.toggleClass("master-not-selected", false);
					this.load_detail_list(master_id_);
				},this));
			}else{
				this._target.toggleClass("master-not-selected", true);

				this._empty_master_info_group();
				this.load_detail_list(-1);
			}
		}

		return this._current_master_id;
	});

	_pb_easy_splitted_view.prototype._empty_master_info_group = (function(){
		var preview_html_ = this._master_info_group_html;
		preview_html_ = preview_html_.replace(/\{\{[\w\_]+\}\}/g, "");

		this._master_info_group.html(preview_html_);
		this._master_info_group.find("[data-column]").each(function(){
			var column_el_ = $(this).text("");
		});
	});

	_pb_easy_splitted_view.prototype._render_master_info_group = (function(master_data_){
		var preview_html_ = this._master_info_group_html;

		$.each(master_data_, function(key_, value_){
			preview_html_ = preview_html_.replace("{{"+key_+"}}", value_);
		});

		preview_html_ = preview_html_.replace(/\{\{[\w\_]+\}\}/g, "&nbsp;");

		this._master_info_group.html(preview_html_);
		this._master_info_group.data_to_tag(master_data_);

	});
	_pb_easy_splitted_view.prototype.cached_master_info = (function(){
		return this._cached_master_info;
	});

	_pb_easy_splitted_view.prototype.load_master_info = (function(master_id_, callback_){
		callback_ = callback_ ? callback_ : $.noop;
		this._cached_master_info = null;
		var all_tr_list_ = this._master_form.find("a[data-master-id]").closest("tr").toggleClass("active", false);

		all_tr_list_.filter(function(){
			return ($(this).find("[data-master-id='"+master_id_+"']").length > 0);
		}).toggleClass("active", true);	

		PB.post(this._options['master-info-load-action'], {
			key : master_id_,
		},$.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "마스터 조회 중, 에러가 발생했습니다.",
				});
				return;
			}
			var t_module_ = this['module'];

			t_module_._cached_master_info = response_json_.results;
			t_module_._render_master_info_group(response_json_.results);
			t_module_.toggle_detail_view(true);
			t_module_._options['master-info-loaded'].apply(t_module_, [t_module_._cached_master_info]);
			this['callback'].apply(t_module_, [t_module_._cached_master_info]);
		}, {
			module : this,
			callback : callback_,
		}), true);
	});

	_pb_easy_splitted_view.prototype.options = function(options_){
		if(options_ !== undefined){
			this._options = $.extend(this._options, options_);
		}

		return this._options;
	};

	_pb_easy_splitted_view.prototype.toggle_detail_view = (function(toggle_){
		if(toggle_ === undefined){
			toggle_ = this._col_detail_el.hasClass("out");
		}

		this._col_detail_el.toggleClass("end", false);

		setTimeout($.proxy(function(){
			if(this['toggle']){
				this['module']._col_detail_el.toggleClass("out", false);
			}else{
				this['module']._col_detail_el.toggleClass("out", true);
				setTimeout($.proxy(function(){
					this._col_detail_el.toggleClass("end", true);
				}, this['module']),300);
			}
		},{
			module : this,
			toggle : toggle_,
		}),100);
	});

	_pb_easy_splitted_view.prototype.load_detail_list = (function(master_id_){
		if(master_id_ === undefined) master_id_= this._current_master_id;
		
		this._detail_form.find(":input[name='master_id']").val(master_id_);
		this._detail_form.submit();
	});

	_pb_easy_splitted_view.prototype.refresh = (function(){
		var master_id_ = this._current_master_id;
		this._master_table_module.load($.proxy(function(){
			this['module'].current_master_id(this['master_id']);
		}, {
			'module' : this,
			'master_id' : master_id_,
		}));
	});

	$.fn.pb_easy_splitted_view = (function(options_){
		var module_ = $(this).data("pb-easysplitted-view-module");
		if(module_) return module_;
		return new _pb_easy_splitted_view(this, options_);
	});
});