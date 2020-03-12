(function($){

	var PBDB_ss_table = (function(target_){
		this._target = target_;
		this._target.data("pbsstable", this);
		this._table_id = this._target.attr("id");
		this._table_form = this._target.closest("form");
		this._page_index_el = this._table_form.find("input[name='page_index']");
		this._pagenav = this._table_form.find("[data-sstable-pagenav-id='"+this._table_id+"']");

		this.bind_ref_condition_form(this._table_form.attr("data-ref-conditions-form"));
		this._rander_pagenav();
	});
	PBDB_ss_table.prototype.target = (function(){
		return this._target;
	});
	PBDB_ss_table.prototype.page_index = (function(page_index_){
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
	PBDB_ss_table.prototype.load = (function(){
		this._table_form.submit();
	});

	PBDB_ss_table.prototype._rander_pagenav = (function(){
		var pagenav_buttons_ = this._pagenav.find("a[data-page-index]");
		var that_ = this;

		$.each(pagenav_buttons_, function(){
			var target_ = $(this);
			target_.data("pb-sstable",that_);
			target_.click(function(event_){
				event_.preventDefault();
				var pagenav_btn_ = $(this);
				var page_index_ = pagenav_btn_.data("page-index");
				var listtable_module_ = pagenav_btn_.data("pb-sstable");

				var current_page_index_ = listtable_module_.page_index();

				if(parseInt(current_page_index_) != parseInt(page_index_)){
					listtable_module_.page_index(page_index_);
				}
			});
		});
	});

	PBDB_ss_table.prototype.bind_ref_condition_form = (function(selector_){
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

	$.fn.pbsstable = (function(){
		var target_ = this;

		if(target_.data("pbsstable")){
			return target_.data("pbsstable");
		}

		return new PBDB_ss_table(target_);
	});

})(jQuery);