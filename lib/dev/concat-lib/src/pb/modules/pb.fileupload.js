(function($){
	PB.file = $.extend(PB.file, {
		_upload_url : PBVAR['fileupload_url'],
		_chunk_upload_url : PBVAR['chunk_fileupload_url'],
		upload_url : function(param_){
			if(!param_){
				return this._upload_url;				
			}

			return PB.make_url(this._upload_url, param_);
		},
		chunk_upload_url : function(param_){
			if(!param_){
				return this._chunk_upload_url;				
			}

			return PB.make_url(this._chunk_upload_url, param_);
		},
		__upload_xhr : null,
		__upload_chunk_maps : {},
		__upload_chunk(key_, files_, options_, callback_){

			options_ = $.extend({
				upload_url : PB.file.chunk_upload_url(),
				
				progress : $.noop,
				start : $.noop,
				fail : $.noop,
				done : $.noop,
				
			},options_);

			callback_ = callback_ === undefined ? $.noop : callback_;

			if(!PB.file.__upload_chunk_maps[key_]){
				PB.file.__upload_chunk_maps[key_] = {
					file_index : 0,
					file_length: files_.length,
					
					single_chunk_current : 0,
					single_chunk_length : Math.ceil(files_[0].size / PBVAR['file_chunksize']),

					total_chunk_current : 0,
					total_chunk_length : 0,

					chunk_size : PBVAR['file_chunksize'],

					results : [],
				};

				var total_file_size_ = 0;

				for(var file_index_ = 0; file_index_ < files_.length; ++file_index_){
					var target_file_ = files_[0];
					total_file_size_ += target_file_.size;
					PB.file.__upload_chunk_maps[key_]['results'][file_index_] = {
						o_name : target_file_['name'],
						r_name : null,
						thumbnail : null,
					};
				}

				PB.file.__upload_chunk_maps[key_]['total_chunk_length'] = Math.ceil(total_file_size_ / PB.file.__upload_chunk_maps[key_]['chunk_size']);

				options_['start'].apply(window, []);
			}

			var chunk_map_data_ = PB.file.__upload_chunk_maps[key_];

			var current_file_ = files_[chunk_map_data_['file_index']];
			var current_result_data_ = chunk_map_data_['results'][chunk_map_data_['file_index']];

			const start_ = chunk_map_data_['single_chunk_current'] * chunk_map_data_['chunk_size'];
			const end_ = Math.min(start_ + chunk_map_data_['chunk_size'], current_file_.size);
			const chunk_ = current_file_.slice(start_, end_);

			const form_data_ = new FormData();
			form_data_.append('chunk', chunk_);
			form_data_.append('chunk_current', chunk_map_data_['single_chunk_current']);
			form_data_.append('chunk_length', chunk_map_data_['single_chunk_length']);
			form_data_.append('chunk_size', chunk_map_data_['chunk_size']);

			if(current_result_data_['r_name']){
				form_data_.append('r_name', current_result_data_['r_name']);	
			}else{
				form_data_.append('o_name', current_result_data_['o_name']);
			}

			if(PB.file.__upload_xhr){
				PB.file.__upload_xhr.abort();
				PB.file.__upload_xhr = null;
			}

			PB.file.__upload_xhr = $.ajax({
				url : options_['upload_url'],
				type : "POST",
				enctype : 'multipart/form-data',
				processData : false,
				contentType : false,
				data : form_data_,
				xhr: $.proxy(function(){
					var xhr_ = new window.XMLHttpRequest();
					xhr_.upload.addEventListener("progress", $.proxy(function(event_){
						if(event_.lengthComputable){
							var single_percent_ = (event_.loaded / event_.total) * 100;
							var total_percent_ = ((this['chunk_map_data']['total_chunk_current'] + 1) / this['chunk_map_data']['total_chunk_length']) * 100;
							
							this['options']['progress'].apply(window, [total_percent_, single_percent_, this['chunk_map_data']]);
						}
					}, this), false);
					return xhr_;
				}, {
					chunk_map_data : chunk_map_data_,
					options : options_,
				}),
				always : function(){
					PB.file.__upload_xhr = null;
				},
				success: $.proxy(function(response_){
					var response_json_ = null;
					try{
						response_json_ = JSON.parse(response_);
					}catch(ex_){
						this['options']['fail'].apply(window, [response_json_, response_]);
						this['callback'].apply(window, [false, PB.file.__upload_chunk_maps[this['key']]['results'], response_json_, response_]);
						return;
					}

					var chunk_map_data_ = this['chunk_map_data'];

					chunk_map_data_['total_chunk_current']++;
					chunk_map_data_['single_chunk_current']++;

					chunk_map_data_['results'][chunk_map_data_['file_index']] = $.extend(chunk_map_data_['results'][chunk_map_data_['file_index']], response_json_.results);

					if(chunk_map_data_['total_chunk_current'] < chunk_map_data_['total_chunk_length']){

						if(chunk_map_data_['single_chunk_current'] >= chunk_map_data_['single_chunk_length']){
							chunk_map_data_['file_index'] += 1;
							chunk_map_data_['single_chunk_length'] = Math.ceil(files_[chunk_map_data_['file_index']].size / PB.file.__upload_chunk_maps[key_]['chunk_size']);
							chunk_map_data_['single_chunk_current'] = 0;

							var total_percent_ = (this['chunk_map_data']['total_chunk_current'] / this['chunk_map_data']['total_chunk_length']) * 100;

							PB.file.__upload_chunk_maps[this['key']] = chunk_map_data_;

							this['options']['progress'].apply(window, [total_percent_, chunk_map_data_]);
						}

						PB.file.__upload_chunk_maps[this['key']] = chunk_map_data_;
						PB.file.__upload_chunk(this['key'], this['files'], this['options'], this['callback']);
					}else{

						PB.file.__upload_chunk_maps[this['key']] = chunk_map_data_;

						this['options']['done'].apply(window, [response_json_, PB.file.__upload_chunk_maps[this['key']]['results'], response_]);
						this['callback'].apply(window, [true, PB.file.__upload_chunk_maps[this['key']]['results'], response_json_, response_]);

						delete PB.file.__upload_chunk_maps[this['key']];
						
					}

				}, {
					key : key_,
					files : files_,
					chunk_map_data : chunk_map_data_,
					options : options_,
					callback : callback_,
				}),
				error: $.proxy(function(response_){ 
					var response_json_ = null;
					try{
						response_json_ = JSON.parse(response_);
					}catch(ex_){}

					this['options']['fail'].apply(window, [response_json_, response_]);
					this['callback'].apply(window, [false, PB.file.__upload_chunk_maps[this['key']]['results'], response_json_, response_]);
				}, {
					key : key_,
					files : files_,
					chunk_map_data : chunk_map_data_,
					options : options_,
					callback : callback_,
				})
			});

		},
		chunk_upload : function(files_, options_, callback_){
			var upload_key_ = PB.random_string(5);
			PB.file.__upload_chunk(upload_key_, files_, options_, callback_);
		},
		upload : function(files_, options_, callback_){
			options_ = $.extend({
				upload_url : PB.file.upload_url(),
				done : $.noop,
				fail : $.noop,
				progress : $.noop,
				start : $.noop,
			}, options_);
			callback_ = callback_ !== undefined ? callback_ : $.noop;

			var form_data_ = new FormData();

			for(var file_index_ =0; file_index_ < files_.length; file_index_++){
				form_data_.append("files", files_[file_index_]);
			}

			this._current_xhr = $.ajax({
				url : options_['upload_url'] ? options_['upload_url'] : PB.file.upload_url(),
				type : "POST",
				enctype : 'multipart/form-data',
				processData : false,
				contentType : false,
				data : form_data_,
				xhr: $.proxy(function(){
					var xhr_ = new window.XMLHttpRequest();
					xhr_.upload.addEventListener("progress", $.proxy(function(event_){
						if(event_.lengthComputable){
							var percent_ = (event_.loaded / event_.total) * 100;
							this['options']['progress'].apply(this, [percent_, event_]);
						}
					}, this), false);

					this['options']['start'].apply(this, [xhr_]);

					return xhr_;
				}, {
					options : options_
				}),
				success: $.proxy(function(response_){
					var response_json_ = null;
					try{
						response_json_ = JSON.parse(response_);
					}catch(ex_){}

					this['options']['done'].apply(this, [response_json_, response_]);
					this['callback'].apply(window, [false, response_json_, response_]);
				}, {
					options : options_,
					callback : callback_,
				}),
				error: $.proxy(function(response_){ 
					var response_json_ = null;
					try{
						response_json_ = JSON.parse(response_);
					}catch(ex_){}

					this['options']['fail'].apply(this, [response_json_, response_]);
					this['callback'].apply(window, [false, response_json_, response_]);
				}, {
					options : options_,
					callback : callback_,
				})
			});
		}
	});


	var pb_fileupload_btn = (function(target_, options_){
		this._target = target_;

		this._options = $.extend({
			upload_url : PB.file.upload_url(),
			chunk_upload_url : PB.file.chunk_upload_url(),
			chunk_upload : true,
			accept : null,
			limit : null,
			
			progress : $.noop,
			start : $.noop,
			fail : $.noop,
			done : $.noop,
			
			label : __('파일선택'),
			label_uploading : __('업로드'),

		},options_);

		this._target.wrap("<div class='fileupload-button'></div>");

		this._wrap = this._target.parent();
		this._wrap.append("<div class='button-wrapper'></div>");

		this._button_wrapper = this._wrap.children(".button-wrapper");

		this._button_wrapper.append("<span class='label-icon icon'></span>");
		this._button_wrapper.append("<span class='label-text'></span>");

		this._loading_wrapper = $('<div class="loading-indicator"> \
			<div class="pb-loading-indicator icon"></div> \
			<div class="text">'+this._options['label_uploading']+'</div> \
				</div>');
		this._wrap.append(this._loading_wrapper);
		this._loading_label = this._loading_wrapper.find(".text");
		
		this._button_label = this._button_wrapper.find(".label-text");
		this._button_label_icon = this._button_wrapper.find(".label-icon");
		this._loading_indicator = this._button_wrapper.find(".loading-indicator");

		this._button_label.html(this._options['label']);

		this._wrap.addClass(this._options['button_class']);

		this._target.attr("accept", this._options['accept']);
		this._target.on('change', $.proxy(function(){
			this.__upload();
		}, this));
		
		this._target.data('pb-fileupload-btn-module', this);
	});
	pb_fileupload_btn.prototype.options = function(options){
		if(options_ !== undefined){
			this._options = $.extend(this._options, options_);
		}

		if(this._options['label']){
			this._button_label.html(this._options['label']);
		}
		if(this._options['label_uploading']){
			this._loading_label.html(this._options['label_uploading']);
		}
		if(this._options['accept']){
			this._target.attr("accept", this._options['accept']);
		}

		return this._options;
	};

	pb_fileupload_btn.prototype.toggle_loading = (function(bool_){
		this._wrap.toggleClass("loading", bool_);
		this._button_wrapper.toggleClass('disabled', bool_);
		this._target.prop('disabled', bool_);
	});

	pb_fileupload_btn.prototype.__upload = (function(){
		this.toggle_loading(true);

		if(this._options['chunk_upload']){

			var options_ = $.extend(true, {}, this._options);
				options_['upload_url'] = this._options['chunk_upload_url'];

			PB.file.chunk_upload(this._target[0].files, options_, $.proxy(function(result_, upload_results_, response_json_, response_text_){
			if(!result_){
				this.toggle_loading(false);
				return;
			}

			this.toggle_loading(false);
			this._target.val("");
		}, this));
		}else{
			PB.file.upload(this._target[0].files, this._options, $.proxy(function(result_, response_json_){
				if(!result_){
					this.toggle_loading(false);
					return;
				}

				this.toggle_loading(false);
				this._target.val("");
			}, this));
		}
	});

	$.fn.pb_fileupload_btn = (function(options_){
		var module_ = this.data('pb-fileupload-btn-module');
		if(module_) return module_;
		return new pb_fileupload_btn(this, options_);
	});

	
})(jQuery);