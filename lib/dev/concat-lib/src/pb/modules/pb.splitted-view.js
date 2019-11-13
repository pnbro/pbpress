jQuery(function($){
	var _pb_splitted_view = (function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			"master-info-load-action" : "",
			"master-info-loaded" : $.noop,
		}, options_);
	
		this._master_cond_form = $("[data-master-cond-form]");
		this._master_listtable_form = $("[data-master-listtable-form]");
		this._master_listtable_module = this._master_listtable_form.find("table").pbajaxlisttable();
		this._master_info_form_table = $("[data-master-info-form-table]");

		this._detail_cond_form = $("[data-detail-cond-form]");
		this._detail_listtable_form = $("[data-detail-listtable-form]");
		this._detail_listtable_module = this._detail_listtable_form.find("table").pbajaxlisttable();

		if(this._detail_cond_form.find(":input[name='master_id']").length <= 0){
			this._detail_cond_form.append("<input type='hidden' name='master_id'>");
			this._detail_listtable_form.append("<input type='hidden' name='master_id'>");
		}

		//initialize master table
		this._master_listtable_form.find("table").on("pblisttableload", $.proxy(function(event_, row_data_, row_el_){
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

		this._current_master_id = null;
		this._target.toggleClass("master-not-selected", true);
		this._target.data("pb-splitted-view-module", this);

	});

	_pb_splitted_view.prototype.current_master_id = (function(master_id_){
		if(master_id_ !== undefined){

			this._current_master_id = master_id_;
			this._empty_master_info_form_table();

			if(this._current_master_id){
				this._target.toggleClass("master-not-selected", false);
				this.load_master_info(master_id_);
				this.load_detail_list(master_id_);
			}else{
				this._target.toggleClass("master-not-selected", true);

				this._empty_master_info_form_table();
				this.load_detail_list(-1);
			}
		}

		return this._current_master_id;
	});

	_pb_splitted_view.prototype._empty_master_info_form_table = (function(){
		this._master_info_form_table.find("[data-column]").each(function(){
			var column_el_ = $(this).text("");
		});
	});

	_pb_splitted_view.prototype._render_master_info_form_table = (function(master_data_){
		this._master_info_form_table.data_to_tag(master_data_);
	});
	_pb_splitted_view.prototype.cached_master_info = (function(){
		return this._cached_master_info;
	});

	_pb_splitted_view.prototype.load_master_info = (function(master_id_){
		this._cached_master_info = null;
		var all_tr_list_ = this._master_listtable_form.find("a[data-master-id]").closest("tr").toggleClass("active", false);

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

			this._cached_master_info = response_json_.results;
			this._render_master_info_form_table(response_json_.results);
			this.toggle_detail_view(true);
			this._options['master-info-loaded'].apply(this, [this._cached_master_info]);

		}, this), true);
	});

	_pb_splitted_view.prototype.toggle_detail_view = (function(toggle_){
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

	

	_pb_splitted_view.prototype.load_detail_list = (function(master_id_){
		if(master_id_ === undefined) master_id_= this._current_master_id;
		
		this._detail_cond_form.find(":input[name='master_id']").val(master_id_);
		this._detail_cond_form.submit();
	});

	_pb_splitted_view.prototype.refresh = (function(){
		var master_id_ = this._current_master_id;
		this._master_listtable_module.load($.proxy(function(){
			this['module'].current_master_id(this['master_id']);
		}, {
			'module' : this,
			'master_id' : master_id_,
		}));
	});

	$.fn.pb_splitted_view = (function(options_){
		var module_ = $(this).data("pb-splitted-view-module");
		if(module_) return module_;
		return new _pb_splitted_view(this, options_);
	});
});