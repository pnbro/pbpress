(function($){

	var pb_easytable = (function(target_){
		this._target = target_;
		this._target.data("pbeasytable", this);
		this._table_id = this._target.attr("id");
		this._table_form = this._target.closest("form");
		this._page_index_el = this._table_form.find("input[name='page_index']");
		this._pagenav = this._table_form.find("[data-easytable-pagenav-id='"+this._table_id+"']");
		this._is_ajax = this._target.attr("data-ajax") === "Y";

		this._loading_indicator = this._target.attr("data-loading-indicator");
		this._hide_pagenav = this._target.attr("data-hide-pagenav") === "Y";

		if(this._is_ajax){
			this._table_form.submit_handler($.proxy(function(){
				this._ajax_load();
			},this));
		}else{
			this._table_form.submit_handler($.proxy(function(){
				this._page_index_el.val(0);
				this._table_form[0].submit();
			},this));
		}

		this._sort_key = this._table_form.find("[name='__ez_sort']");
		this._sort_dir = this._table_form.find("[name='__ez_sort_dir']");
			

		this._table_form.on("click", ".sort-link[data-sort-key]", $.proxy(function(event_){
			var target_el_ = $(event_.target);
			var sort_key_ = target_el_.attr("data-sort-key");
			var sort_dir_ = target_el_.attr("data-sort-dir");

			this._sort_dir.val(sort_dir_);
			this._sort_key.val(sort_key_);

			this.load();
			this._rander_sort_link();

			return false;
		}, this));

		this._rander_pagenav();
		if(this._is_ajax) this.load();
	});
	pb_easytable.prototype.target = (function(){
		return this._target;
	});
	pb_easytable.prototype.page_index = (function(page_index_){
		if(page_index_ !== undefined){
			this._page_index_el.val(page_index_);
			this.load(false);
		}

		return this._page_index_el.val();
	});
	pb_easytable.prototype.load = (function(reset_page_index_){
		reset_page_index_ = (reset_page_index_ !== undefined ? !!reset_page_index_ : true);
		if(reset_page_index_) this._page_index_el.val(0);
		if(!this._is_ajax){
			this._table_form[0].submit();	
		}else{
			this._table_form.submit();
		}
	});
	pb_easytable.prototype._ajax_load = (function(){
		var request_data_ = this._table_form.serialize_object();
		var target_ = this._target;
		var thead_el_ = this._target.children("thead");
		var tbody_el_ = this._target.children("tbody");
		var pagenav_el_ = this._pagenav;
		
		tbody_el_.empty();
		pagenav_el_.empty();

		tbody_el_.append("<tr><td class='ajax-loading' colspan='"+thead_el_.find("tr > th").length+"'>"+this._loading_indicator+"</td></tr>");

		target_.trigger("pbeasytableload");

		PB.post("pb-easytable-load-html", $.extend(request_data_,{
			table_id : this._table_id,
		}),$.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				return;
			}

			this['tbody_el'].empty();
			this['pagenav_el'].empty();
			this['tbody_el'].html(response_json_.body_html);
			this['pagenav_el'].html(response_json_.pagenav_html);
			this['module']._rander_pagenav();
			this['target'].trigger("pbeasytableloaded", [response_json_.orgdata, this['tbody_el'].children()]);
		},{
			tbody_el : tbody_el_,
			pagenav_el : pagenav_el_,
			tbody_el : tbody_el_,
			pagenav_el : pagenav_el_,
			module : this,
			target : target_,

		}), false, {
			type : "GET"
		});
	});

	pb_easytable.prototype._rander_pagenav = (function(){
		if(this._hide_pagenav) return;

		var pagenav_buttons_ = this._pagenav.find("a[data-page-index]");
		var that_ = this;

		$.each(pagenav_buttons_, function(){
			var target_ = $(this);
			target_.data("pb-easytable",that_);
			target_.click(function(event_){
				event_.preventDefault();
				var pagenav_btn_ = $(this);
				var page_index_ = pagenav_btn_.data("page-index");
				var listtable_module_ = pagenav_btn_.data("pb-easytable");

				var current_page_index_ = listtable_module_.page_index();

				if(parseInt(current_page_index_) != parseInt(page_index_)){
					listtable_module_.page_index(page_index_);
				}
			});
		});
	});

	pb_easytable.prototype._rander_sort_link = (function(){
		var module_ = this;
		var sort_key_ = this._sort_key.val();
		var sort_desc_ = this._sort_dir.val();
		this._table_form.find("[data-sort-key]").each(function(){
			var target_link_el_ = $(this);

			if(target_link_el_.attr('data-sort-key') === sort_key_){
				target_link_el_.toggleClass("sorted sort-asc sort-desc", false);
				target_link_el_.toggleClass("sorted "+(sort_desc_ === "desc" ? "sort-desc" : "sort-asc"), true);
				target_link_el_.attr('data-sort-dir', (sort_desc_ === "asc" ? "desc" : "asc"));
				var sort_label_ = target_link_el_.closest(".dropdown").find("[data-sort-label]");

				sort_label_.text(target_link_el_.text());
				
				sort_label_.toggleClass("sorted sort-asc sort-desc", false);
				sort_label_.toggleClass("sorted "+(sort_desc_ === "desc" ? "sort-desc" : "sort-asc"), true);
			}else{
				target_link_el_.toggleClass("sorted sort-asc sort-desc", false);
				target_link_el_.attr('data-sort-dir', "asc");
			}



		});
	});

	$.fn.pbeasytable = (function(){
		var target_ = this;

		if(target_.data("pbeasytable")){
			return target_.data("pbeasytable");
		}

		return new pb_easytable(target_);
	});

})(jQuery);