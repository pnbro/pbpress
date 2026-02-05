(function($){

	window.pb_file_dragzone = {
		default : {
			'label' : __('파일을 드래그하여 선택할 수 있습니다.'),
			'button_label' : __('파일선택하기'),
		}
	};

	var pb_file_dragzone = function(target_, options_){
		this._target = $(target_);

		var limit_ = parseInt(this._target.attr("data-limit"));
			limit_ = isNaN(limit_) ? null : limit_;

		this._options = $.extend({
			'label' : window.pb_file_dragzone.default['label'],
			'button_label' : window.pb_file_dragzone.default['button_label'],
			'limit' : limit_,

			'file_selected' : $.noop,

			upload_url : PB.file.chunk_upload_url(),
			upload_fail : $.noop,
			upload_done : $.noop,

			'class' : "",

		}, options_);

		var dropzone_class_ = this._target.attr('data-dropzone-class');
			dropzone_class_ = dropzone_class_ ? dropzone_class_ : "";

		this._options['class'] += " "+dropzone_class_;

		this._target.wrap("<div class='pb-file-dropzone "+this._options['class']+"'></div>");

		this._wrapper = this._target.parent();

		this._inner_noselect = $('<div class="wrapper noselect">\
			<i class="icon"></i> \
			<div class="text" data-label-text>'+this._options['label']+'</div> \
			<span class="btn btn-select" data-select-button>'+this._options['button_label']+'</span> \
		</div>'); 

		this._inner_selected = $('<div class="wrapper selected">\
			<div class="file-item-list" data-preview-item-list></div>\
			<a class="btn btn-select" data-select-button>'+this._options['button_label']+'</a> \
		</div>'); 

		this._inner_loading = $('<div class="wrapper loading">\
			<div class="loading-indicator"><div class="pb-loading-indicator"></div></div>\
			<div class="progress"> \
				<div class="progress-bar" role="progressbar" data-progress-text>0/0</div>\
			</div>\
		</div>'); 

		this._wrapper.append(this._inner_noselect);
		this._wrapper.append(this._inner_selected);
		this._wrapper.append(this._inner_loading);

		this._inner_noselect_label = this._inner_noselect.find("[data-label-text]");
		this._inner_noselect_button = this._inner_noselect.find("[data-select-button]");
		this._preview_item_list = this._inner_selected.find("[data-preview-item-list]");
		this._inner_selected_button = this._inner_selected.find("[data-select-button]");
		this._inner_loading_progress_bar = this._inner_loading.find("[data-progress-text]");

		this._inner_selected_button.click($.proxy(function(){
			this._target.click();
			return false;
		}, this));

		this._target.appendTo("body");
		this._target.addClass('pb-file-hide');

		this._inner_noselect_button.click($.proxy(function(){
			this._target.click();
			return false;
		}, this));
		
		this._target.data("pb-file-dragzone", this);

		this._wrapper.on('dragin', $.proxy(function(event_) {
			this._wrapper.toggleClass("drag-in", true);
		}, this));

		this._wrapper.on('dragover', $.proxy(function(event_){
			event_.preventDefault();
			this.__check_pre_valid(event_.originalEvent.dataTransfer);
			this._wrapper.toggleClass("drag-in", true);
		}, this));

		this._wrapper.on('dragleave', $.proxy(function(event_){
			this._wrapper.toggleClass("drag-in", false);
			this._wrapper.toggleClass("pre-not-valid", false);
		}, this));

		this._wrapper.on('drop', $.proxy(function(event_){
			event_.preventDefault();
			this._wrapper.toggleClass("drag-in", false);
			this._wrapper.toggleClass("pre-not-valid", false);

			var files_ = event_.originalEvent.dataTransfer.files;

			this.add_files(files_);

		}, this));

		this._target.on("change", $.proxy(function(){
			this.add_files(this._target[0].files);
		}, this));

		this._selected_files = [];

	}

	pb_file_dragzone.prototype.options = function(options){
		if(options_ !== undefined){
			this._options = $.extend(this._options, options_);
		}

		if(this._options['label']){
			this._inner_noselect_label.html(this._options['label']);
		}

		if(this._options['button_label']){
			this._inner_noselect_button.html(this._options['button_label']);
		}

		if(this._options['limit']){
			this._target.prop("multiple", (!this._options['limit'] || this._options['limit'] > 1));
		}

		return this._options;
	};

	pb_file_dragzone.prototype.__check_pre_valid = function(data_transfer_){
		var pre_types_ = data_transfer_.types;

		if(!pre_types_){
			this._wrapper.toggleClass("pre-not-valid", true);
			return;
		}

		this._wrapper.toggleClass("pre-not-valid", !(pre_types_.indexOf('Files') >= 0));

		return false;
	};
	pb_file_dragzone.prototype.__check_valid = function(file_){
		var file_type_ = file_['type'];
		var file_extension_ = file_['name'].split(".");
			file_extension_ = file_extension_[file_extension_.length-1];
		var target_accept_ = this._target.attr("accept");
			target_accept_ = target_accept_ === undefined ? ["*"] : target_accept_.split(",");

		file_type_ = file_type_.split("/");

		for(var type_index_ = 0; type_index_<target_accept_.length; ++type_index_){
			var mine_type_ = target_accept_[type_index_];

			if(mine_type_.indexOf("/") > -1){
				var sub_mine_types_ = mine_type_.split("/");

				if(sub_mine_types_[0] === file_type_[0] && (sub_mine_types_[1] === "*" || sub_mine_types_[1] === file_type_[1])){
					return true;
				}
			}else{
				var extension_ = mine_type_.split(".");
				if(extension_[1] === file_extension_){
					return true;
				}
			}

		}



		return false;
	};

	pb_file_dragzone.prototype.add_files = function(files_, check_valid_){
		files_ = files_ === undefined ? [] : files_;
		check_valid_ = check_valid_ === undefined ? true : check_valid_;

		var file_length_ = files_.length;
		var accept_files_ = [];

		if(check_valid_){
			for(var file_index_=0;file_index_<file_length_;++file_index_){
				var file_ = files_[file_index_];

				if(this.__check_valid(file_)){
					accept_files_.push(file_);
				}
			}
		}else{
			accept_files_ = files_;
		}	

		var file_has_limit_ = this._options['limit'] !== null;
		var file_limit_ = this._options['limit'];
		var file_length_ = accept_files_.length;

		for(var file_index_=0;file_index_<file_length_;++file_index_){
			var accept_file_ = accept_files_[file_index_];

			if(file_has_limit_ && file_limit_ <= this._selected_files.length){
				this.check_limit();
				break;
			}

			var is_image_ = accept_file_.type.indexOf("image") >= 0;
			var preview_file_item_ = $("<div class='file-item'>\
				<div class='icon "+(is_image_ ? "image" : "")+"'></div> \
				<span class='text'>"+accept_file_['name']+"</span> \
				<a class='delete-btn' href='#' data-delete-button></a> \
				</div>");

			this._preview_item_list.append(preview_file_item_);

			if(is_image_){
				var file_reader_ = new FileReader();
				file_reader_.onload = $.proxy(function(event_){
					this.find(".icon.image").append("<img src='"+event_.target.result+"' class='preview'>");
				}, preview_file_item_);
				file_reader_.readAsDataURL(accept_file_);
			}

			preview_file_item_.find("[data-delete-button]").click($.proxy(function(){
				this['module'].delete_file(this['preview_item'].index());
				return false;
			}, {
				module : this,
				preview_item : preview_file_item_,
			}));
			this._selected_files.push(accept_file_);
		}

		this._target.val(null);
		this._wrapper.toggleClass("selected", this._selected_files.length > 0);
		this.check_limit();

		if(this._selected_files.length > 0){
			this._options['file_selected'].apply(this, [this._selected_files]);	
		}
	};

	pb_file_dragzone.prototype.selected_files = function(){
		return this._selected_files;
	};


	pb_file_dragzone.prototype.delete_file = function(index_){
		this._selected_files.splice(index_, 1);
		this._preview_item_list.children().eq(index_).remove();
		this._wrapper.toggleClass("selected", this._selected_files.length > 0);
		this.check_limit();
	};
	pb_file_dragzone.prototype.clear_files = function(){
		this._selected_files = [];
		this._preview_item_list.empty();
		this._target.val(null);
		this._wrapper.toggleClass("selected", this._selected_files.length > 0);
		this.check_limit();
	};

	pb_file_dragzone.prototype.check_limit = function(){
		var check_limit_ = !this._options['limit'] || this._selected_files.length < this._options['limit'];
		this._inner_selected.toggleClass("addable", check_limit_);
		return check_limit_;
	};

	pb_file_dragzone.prototype.upload = function(callback_){
		callback_ = callback_ === undefined ? $.noop : callback_;

		if(this._selected_files.length <= 0){
			callback_.apply(this, [false, {
				error_title : __("업로드실패"),
				error_message : __("업로드할 파일이 없습니다."),
			}]);
			return;	
		}

		PB.file.chunk_upload(this._selected_files, {
			upload_url : this._options['upload_url'],
			progress : $.proxy(function(total_percent_, chunk_map_data_){
				total_percent_ = Math.ceil(total_percent_);
				this._inner_loading_progress_bar.width(total_percent_+"%");
				this._inner_loading_progress_bar.text(total_percent_+"%");
			}, this),
			start : $.proxy(function(){
				this._wrapper.toggleClass("selected", false);
				this._wrapper.toggleClass("loading", true);
			}, this),
			fail : $.proxy(function(response_json_, response_){
				this['module']._options['upload_fail'].apply(this['module'], [response_json_, response_]);
				this['module']._wrapper.toggleClass("selected", true);
				this['module']._wrapper.toggleClass("loading", false);
				this['callback'].apply(this['module'], [false, response_json_, response_]);

				this['module']._inner_loading_progress_bar.width(0);
				this['module']._inner_loading_progress_bar.text("0%");
			}, {
				module : this,
				callback : callback_
			}),
			done : $.proxy(function(response_json_, results_){
				this['module']._options['upload_done'].apply(this['module'], [response_json_, results_]);
				this['module']._wrapper.toggleClass("loading", false);
				this['callback'].apply(this['module'], [true, response_json_, results_]);
				this['module'].clear_files();
				this['module']._inner_loading_progress_bar.width(0);
				this['module']._inner_loading_progress_bar.text("0%");
			}, {
				module : this,
				callback : callback_
			}),
		});

		return this._selected_files;
	};
	
	$.fn.pb_file_dragzone = (function(options_){
		var module_ = this.data('pb-file-dragzone');
		if(module_) return module_;
		return new pb_file_dragzone(this, options_);
	});

	
})(jQuery);