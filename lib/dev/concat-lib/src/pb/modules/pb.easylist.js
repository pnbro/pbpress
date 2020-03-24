(function($){

	var pb_easylist = (function(target_){
		this._target = target_;
		this._target.data("pbeasylist", this);
		this._table_id = this._target.attr("id");
		this._table_form = this._target.closest("form");
		this._page_index_el = this._table_form.find("input[name='page_index']");
		this._pagenav = this._table_form.find("[data-easylist-pagenav-id='"+this._table_id+"']");
		this._is_ajax = this._target.attr("data-ajax") === "Y";

		this._loading_indicator = this._target.attr("data-loading-indicator");
		this._hide_pagenav = this._target.attr("data-hide-pagenav") === "Y";

		if(this._is_ajax){
			this._table_form.submit_handler($.proxy(function(){
				this._ajax_load();
			},this));
		}

		this._rander_pagenav();
		if(this._is_ajax) this.load();
	});
	pb_easylist.prototype.target = (function(){
		return this._target;
	});
	pb_easylist.prototype.page_index = (function(page_index_){
		if(page_index_ !== undefined){
			var ref_cond_form_ = $(this._table_form.attr("data-ref-conditions-form"));
			this._page_index_el.val(page_index_);
			this.load(false);
		}

		return this._page_index_el.val();
	});
	pb_easylist.prototype.load = (function(reset_page_index_){
		reset_page_index_ = (reset_page_index_ !== undefined ? !!reset_page_index_ : true);
		if(reset_page_index_) this._page_index_el.val(0);
		this._table_form.submit();	
	});
	pb_easylist.prototype._ajax_load = (function(){
		var request_data_ = this._table_form.serialize_object();
		var target_ = this._target;
		var pagenav_el_ = this._pagenav;
		
		target_.empty();
		target_.append("<div class='ajax-loading'>"+this._loading_indicator+"</div>");

		target_.trigger("pbeasylistload");

		PB.post("pb-easylist-load-html", $.extend(request_data_,{
			table_id : this._table_id,
		}),$.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				return;
			}

			this['target'].html(response_json_.list_html);
			this['pagenav_el'].html(response_json_.pagenav_html);
			this['module']._rander_pagenav();
			this['target'].trigger("pbeasylistloaded", [response_json_.orgdata, this['target'].children()]);
		},{
			pagenav_el : pagenav_el_,
			module : this,
			target : target_,

		}), false, {
			type : "GET"
		});
	});

	pb_easylist.prototype._rander_pagenav = (function(){
		if(this._hide_pagenav) return;

		var pagenav_buttons_ = this._pagenav.find("a[data-page-index]");
		var that_ = this;

		$.each(pagenav_buttons_, function(){
			var target_ = $(this);
			target_.data("pb-easylist",that_);
			target_.click(function(event_){
				event_.preventDefault();
				var pagenav_btn_ = $(this);
				var page_index_ = pagenav_btn_.data("page-index");
				var listtable_module_ = pagenav_btn_.data("pb-easylist");

				var current_page_index_ = listtable_module_.page_index();

				if(parseInt(current_page_index_) != parseInt(page_index_)){
					listtable_module_.page_index(page_index_);
				}
			});
		});
	});

	$.fn.pbeasylist = (function(){
		var target_ = this;

		if(target_.data("pbeasylist")){
			return target_.data("pbeasylist");
		}

		return new pb_easylist(target_);
	});

})(jQuery);