(function($){

	window.pb_file_input = {
		default : {
			'upload_url' : PB.file.chunk_upload_url(),
			'accept' : "image/*,.zip,.pdf,.hwp,.xls,.xlsx,.doc,.docx,*.ppt,*.pptx",
			'button_label' : __('파일업로드'),
			'dropzone_label' : __('파일을 드래그하여 업로드할 수 있습니다.'),
		}
	};

	window._pb_file_input = function(target_, options_){
		this._target = $(target_);

		var accept_ = this._target.attr("data-accept");
			accept_ = !accept_ ? window.pb_file_input.default['accept'] : accept_;

		var single_ = this._target.attr("data-single");
			single_ = !!(single_ === "Y");
		
		var limit_ = this._target.attr("data-limit");
			limit_ = (!limit_ || single_) ? 1 : limit_;

		this._options = $.extend({
			'upload_url' : window.pb_file_input.default['upload_url'],
			'accept' : accept_,
			'limit' : limit_,
			'button_label' : window.pb_file_input.default['button_label'],
			'dropzone_label' : window.pb_file_input.default['dropzone_label'],
			'single' : single_,
			
			'class' : "",
			'dropzone_class' : "",

			'file_uploaded' : $.noop,

		}, options_);

		var dropzone_class_ = this._target.attr('data-dropzone-class');
			dropzone_class_ = dropzone_class_ ? dropzone_class_ : "";

		var wrapper_class_ = this._target.attr('data-wrapper-class');
			wrapper_class_ = wrapper_class_ ? wrapper_class_ : "";

		this._options['class'] += " "+wrapper_class_;
		this._options['dropzone_class'] += " "+dropzone_class_;
		this._options['limit'] = (!this._options['limit'] || this._options['single']) ? 1 : this._options['limit'];

		this._input_name = this._target.prop("name");

		this._target.wrap("<div class='pb-fileinput "+this._options['class']+"'></div>");
		this._wrap = this._target.parent();

		this._file_item_list = $("<div class='file-item-list'></div>");
		this._file_item_list.appendTo(this._wrap);

		this._target.data('pb-file-input-module', this);

		var upload_url_ = this._options['upload_url'];

		this._dropzone_input = $('<input id="file-dropzone-input-'+this._modal_uid+'" type="file" name="files[]" accept="'+this._options['accept']+'" '+(!this._options['limit'] || this._options['limit'] > 1 ? "multiple" : "")+'>');
		this._dropzone_input.appendTo("body");

		this._dropzone_module = this._dropzone_input.pb_file_dragzone({
			upload_url : upload_url_,
			limit : this._options['limit'],
			button_label : this._options['button_label'],
			label : this._options['dropzone_label'],
			class : this._options['dropzone_class'],
			file_selected : $.proxy(function(selected_files_){
				this._dropzone_module.upload($.proxy(function(result_, response_json_, results_){
					if(!result_) return;

					for(var row_index_ =0; row_index_<results_.length; ++row_index_){
						this.add_file(results_[row_index_]);	
					}

					this._options['file_uploaded'].apply(this, [results_]);

				}, this));
			}, this)
		});
		
		this._dropzone_module._wrapper.appendTo(this._wrap);
		
		this._selected_files = [];
		this.check_limit();

		var default_data_ = this._target.val();

		var parsed_json_ = null;
		try{
			parsed_json_ = JSON.parse(default_data_);
		}catch(ex_){
			parsed_json_ = null;
		}	

		console.log("1", parsed_json_, "2", default_data_);

		if(parsed_json_ && parsed_json_ !== "" && !this._options['single']){
			this.apply_json(parsed_json_);
		}else if(default_data_ && default_data_ !== ""){
			this.apply_json([{
				r_name : default_data_,
				o_name : default_data_,
			}]);
		}
		
	};

	_pb_file_input.prototype.target = (function(){
		return this._target;
	});

	_pb_file_input.prototype.options = function(options){
		if(options_ !== undefined){
			this._options = $.extend(this._options, options_);
		}

		if(this._options['upload_url']){
			this._dropzone_module.options({upload_url : this._options['upload_url']});
		}

		if(this._options['accept']){
			this._dropzone_input.prop("accept", this._options['accept']);
		}

		if(this._options['button_label']){
			this._dropzone_module.options({button_label : this._options['button_label']});
		}

		if(this._options['dropzone_label']){
			this._dropzone_module.options({label : this._options['dropzone_label']});
		}

		if(this._options['limit'] || this._options['single']){
			var multiple_ = !this._options['single'] && (!this._options['limit'] || this._options['limit'] > 1);
			this._target.prop("multiple", multiple_);
			this._dropzone_module.options({multiple : multiple_});
		}

		return this._options;
	};

	_pb_file_input.prototype.add_file = (function(data_){
		if(!this.check_limit()) return;

		var o_name_ = !data_['o_name'] ? data_['r_name'] : data_['o_name'];
		var is_image_ = !!data_['thumbnail'];
		var preview_file_item_ = $("<div class='file-item'>\
			<div class='icon "+(is_image_ ? "image" : "")+"'></div> \
			<span class='text'>"+o_name_+"</span> \
			<a class='delete-btn' href='#' data-delete-button></a> \
		</div>");

		if(is_image_){
			preview_file_item_.find(".icon.image").append("<img src='"+PB.filebase_url(data_['thumbnail'])+"' class='preview'>");
		}

		this._file_item_list.append(preview_file_item_);

		preview_file_item_.find("[data-delete-button]").click($.proxy(function(){
			this['module'].delete_file(this['preview_item'].index());
			return false;
		}, {
			module : this,
			preview_item : preview_file_item_,
		}));

		this._selected_files.push(data_);
		this._wrap.toggleClass("file-selected", this._selected_files.length > 0);
		this.check_limit();
	});
	_pb_file_input.prototype.delete_file = (function(index_){
		this._selected_files.splice(index_, 1);
		this._file_item_list.children().eq(index_).remove();
		this._wrap.toggleClass("file-selected", this._selected_files.length > 0);
		this.check_limit();
	});
	_pb_file_input.prototype.clear_files = function(){
		this._selected_files = [];
		this._file_item_list.empty();
		this._wrap.toggleClass("file-selected", false);
		this.check_limit();
	};

	_pb_file_input.prototype.check_limit = function(){
		var check_limit_ = !this._options['limit'] || this._selected_files.length < this._options['limit'];
		this._wrap.toggleClass("addable", check_limit_);
		return check_limit_;
	};

	_pb_file_input.prototype.to_json = function(){
		return this._selected_files;
	};

	_pb_file_input.prototype.apply_json = function(json_){

		this.clear_files();
		
		for(row_index_=0;row_index_<json_.length;++row_index_){
			this.add_file(json_[row_index_]);
		}
	};
		
	$.fn.pb_file_input = (function(options_){
		var module_ = this.data('pb-file-input-module');
		if(module_) return module_;
		return new _pb_file_input(this, options_);
	});

	PB.add_data_filter('pb-serialize-object', function(val_, input_){
		var check_module_ = input_.data('pb-file-input-module');
		if(!check_module_) return val_;

		var results_ = check_module_.to_json();

		if(check_module_._options['single']){
			var first_row_data_ = results_[0];
			if(!first_row_data_) return null;
			return first_row_data_['r_name'];
		}

		return check_module_.to_json();
	});

	
})(jQuery);