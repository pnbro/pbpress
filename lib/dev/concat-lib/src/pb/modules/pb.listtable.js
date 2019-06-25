(function($){

	var PB_list_table = (function(target_){
		this._target = target_;
		this._target.data("pblisttable", this);
		this._global_id = this._target.data("pb-listtable-id");
		this._table_form = this._target.closest("form");
		this._page_index_el = this._table_form.find("input[name='page_index']");
		this._pagenav = $("[data-pb-listtable-pagenav-id='"+this._global_id+"']");

		this.bind_ref_condition_form(this._table_form.attr("data-ref-conditions-form"));

		this._rander_pagenav();
	});
	PB_list_table.prototype.target = (function(){
		return this._target;
	});
	PB_list_table.prototype.page_index = (function(page_index_){
		if(page_index_ !== undefined){
			var ref_cond_form_ = $(this._table_form.attr("data-ref-conditions-form"));

			if(ref_cond_form_.length > 0){
				var ref_cond_form_data_ = ref_cond_form_.serialize_object();

				var listtable_form_el_ = this._table_form;
				$.each(ref_cond_form_data_, function(key_, value_){
					listtable_form_el_.find(":input[name='"+key_+"']").val(value_);
				});
			}

			this._page_index_el.val(page_index_);
			this.load();
		}

		return this._page_index_el.val();
	});
	PB_list_table.prototype.load = (function(){
		this._table_form.submit();
	});

	PB_list_table.prototype._rander_pagenav = (function(){
		var pagenav_buttons_ = this._pagenav.find("a[data-page-index]");
		var that_ = this;

		$.each(pagenav_buttons_, function(){
			var target_ = $(this);
			target_.data("pb-listtable",that_);
			target_.click(function(event_){
				event_.preventDefault();
				var pagenav_btn_ = $(this);
				var page_index_ = pagenav_btn_.data("page-index");
				var listtable_module_ = pagenav_btn_.data("pb-listtable");

				var current_page_index_ = listtable_module_.page_index();

				if(parseInt(current_page_index_) != parseInt(page_index_)){
					listtable_module_.page_index(page_index_);
				}
			});
		});
	});

	PB_list_table.prototype.bind_ref_condition_form = (function(selector_){
		var listtable_form_el_ = this._table_form;
			
		var ref_cond_form_ = $(selector_);	
		ref_cond_form_.submit_handler($.proxy(function(){
			var ref_cond_data_ = this['ref_cond_form'].serialize_object();

			var listtable_form_el_ = this['module']._table_form;
			$.each(ref_cond_data_, function(key_, value_){
				listtable_form_el_.find(":input[name='"+key_+"']").val(value_);
			});
			this['module'].page_index(0);
		}, {
			ref_cond_form : ref_cond_form_,
			module : this,
		}));
	});

	$.fn.pblisttable = (function(){
		var target_ = this;

		if(target_.data("pblisttable") !== undefined && target_.data("pblisttable") !== null){
			return target_.data("pblisttable");
		}

		return new PB_list_table(target_);
	});

	var PB_ajax_list_table = (function(target_){
		PB_list_table.apply(this, [target_]);

		this._options = {
			'before_load' : function(){
				return true;
			}
		};

		this._table_form.data("pbajaxlisttable", this);
		this._table_form.on("submit", function(){
			var listtable_module_ = $(this).data("pbajaxlisttable");
			listtable_module_.load();
			return false;
		});

		var autoload_el_ = this._table_form.find(":input[name='firstload']");
		if(autoload_el_.length <= 0 || autoload_el_.val() !== "N"){
			this.load();
		}
	});

	PB_ajax_list_table.prototype = Object.create(PB_list_table.prototype);

	PB_ajax_list_table.prototype.load = (function(callback_){
		if(!this._options['before_load'].apply(this)) return;

		callback_ = callback_ || $.noop;

		var table_form_ = this._table_form;
		var table_params_ = table_form_.serialize_object();

		var that_ = this;
		var target_ = this._target;
		var thead_el_ = this._target.children("thead");
		var tbody_el_ = this._target.children("tbody");
		var pagenav_el_ = this._pagenav;

		target_.trigger("pblisttablestartload");
		
		tbody_el_.empty();
		pagenav_el_.empty();

		tbody_el_.append("<tr><td class='ajax-loading' colspan='"+thead_el_.find("tr > th").length+"'><center><div class='pb-loading-indicator'></div></center></td></tr>");

		PB.post("pb-listtable-load-html", $.extend(table_params_,{
			global_id : this._global_id
		}),$.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				return;
			}

			this['tbody_el'].empty();
			this['pagenav_el'].empty();
			this['tbody_el'].append(response_json_.body_html);
			this['pagenav_el'].append(response_json_.pagenav_html);
			this['module']._rander_pagenav();
			this['target'].trigger("pblisttableload", [response_json_.orgdata, this['tbody_el'].children()]);
			this['callback'].apply(this['module']);
		},{
			callback : callback_,
			tbody_el : tbody_el_,
			pagenav_el : pagenav_el_,
			tbody_el : tbody_el_,
			pagenav_el : pagenav_el_,
			module : that_,
			target : target_,

		}), false, {
			type : "GET"
		});
	});

	$.fn.pbajaxlisttable = (function(){
		var target_ = this;

		if(target_.data("pblisttable") !== undefined && target_.data("pblisttable") !== null){
			return target_.data("pblisttable");
		}

		return new PB_ajax_list_table(target_);
	});

})(jQuery);

function _pb_list_table_initialize(global_id_, ajax_){
	$ = jQuery;
	if(ajax_ === undefined || ajax_ === null){
		ajax_ = false;
	}

	if(ajax_ === true){
		$("[data-pb-listtable-id='"+global_id_+"']").pbajaxlisttable();
	}else{
		$("[data-pb-listtable-id='"+global_id_+"']").pblisttable();
	}
	
}