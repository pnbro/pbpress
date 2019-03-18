(function($){
	PB.file = $.extend(PB.file, {
		_upload_url : "/PBLibrary/handlers/common/fileupload.php",
		upload_url : function(param_){
			if(!param_){
				return this._upload_url;				
			}

			return PB.make_url(this._upload_url, param_);
		}
	});

	$(document).on('dragover', function(event_){
		var dropZones = $('.btn-fileupload-dropzone'),
			timeout = window.pbfileupload_dropzone_timeout;
		if (timeout) {
			clearTimeout(timeout);
		}

		var hoveredDropZone = $(event_.target).closest(dropZones);
		dropZones.not(hoveredDropZone).removeClass('dropin');
		hoveredDropZone.addClass('dropin');
		window.pbfileupload_dropzone_timeout = setTimeout(function () {
			window.pbfileupload_dropzone_timeout = null;
			dropZones.removeClass('dropin');
		}, 100);
	});


	var pb_fileupload_btn = (function(target_, options_){
		this._target = target_;

		this._options = $.extend({
			upload_url : PB.file.upload_url(),
			autoupload : false,
			dropzone : false,
			accept : null,
			limit : null,
			
			progress : $.noop,
			start : $.noop,
			fail : $.noop,
			done : $.noop,

			label_icon : '<i class="glyphicon glyphicon-plus"></i>',
			label : '파일선택',
			label_dropzone : '파일을 드래그하여 업로드할 수 있습니다.',

			button_class : 'btn-default',

		},options_);

		this._target.wrap("<span class='btn btn-fileupload'></span>");
		this._wrap = this._target.parent();

		this._wrap.addClass(this._options['button_class']);
		this._wrap.toggleClass("btn-fileupload-dropzone", this._options['dropzone']);

	
		this._wrap.append("<span class='label-icon'></span>");
		this._wrap.append("<span class='label-text'></span>");
		this._wrap.append('<i class="loading-indicator"><div class="pb-loading-indicator"></div></i>');

		this._label = this._wrap.find(".label-text");
		this._label_icon = this._wrap.find(".label-icon");
		this._loading_indicator = this._wrap.find(".loading-indicator");

		this._label.html(this._options['label']);
		this._label_icon.html(this._options['label_icon']);

		if(this._options['dropzone']){
			this._wrap.append("<span class='label-dropzone'>"+this._options['label_dropzone']+"</span>");
		}

		this._target.fileupload({
			// xhrFields: {withCredentials: true},
			url: this._options['upload_url'],
			dataType : 'json',
			dropZone : target_,
			autoupload : this._options['autoupload'],
			limitMultiFileUploads : this._options['limit'],
			start : $.proxy(function(event_, data_){
				this.toggle_loading(true);
				this._options['start'].apply(this, [data_]);
			}, this), 
			fail: $.proxy(function(event_, data_){
				this._options['fail'].apply(this, [data_]);
			}, this), 
			done: $.proxy(function(event_, data_){

				var files_ = data_.result.files;
				var result_ = [];

				$.each(files_, function(){
					result_.push({
						'o_name' : this['o_name'],
						'r_name' : this['o_name'],
						'upload_path' : this['upload_path'],
						'thumbnail' : this['thumbnail'],
						'type' : this['type'],
						'size' : this['size'],
					});
				});

				this._options['done'].apply(this, [result_]);
			}, this),
			always :  $.proxy(function(event_, data_){
				this.toggle_loading(false);
			}, this),
			progressall: $.proxy(function(event_, data_){
				var progress_ = parseInt(data_.loaded / data_.total * 100, 10);
				this._options['progress'].apply(this, [progress_]);
			}, this)

		});

		this._target.data('pb-fileupload-btn-module', this);
	});

	pb_fileupload_btn.prototype.toggle_loading = (function(bool_){
		this._wrap.toggleClass("loading", bool_);
		this._wrap.toggleClass('disabled', bool_);
		this._target.prop('disabled', bool_);
	});

	$.fn.pb_fileupload_btn = (function(options_){
		var module_ = this.data('pb-fileupload-btn-module');
		if(module_) return module_;
		return new pb_fileupload_btn(this, options_);
	});

	
})(jQuery);