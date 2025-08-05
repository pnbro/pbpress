(function($){

	window.pb_image_input = {
		default : {
			'button_label' : __('이미지업로드'),
			'dropzone_label' : __('이미지를 드래그하여 업로드할 수 있습니다.'),
			'accept' : "image/*",
		}
	};
	
	window._pb_image_input = function(target_, options_){

		var accept_ = target_.attr("data-accept");
			accept_ = !accept_ ? window.pb_image_input.default['accept'] : accept_;

		var temp_options = $.extend({
			'upload_url' : PB.file.chunk_upload_url(),
			'accept' : accept_,
			'button_label' : window.pb_image_input.default['button_label'],
			'dropzone_label' : window.pb_image_input.default['dropzone_label'],

		}, options_);

		window._pb_file_input.apply(this, [target_, temp_options]);

		this._target.data('pb-image-input-module', this);
		this._wrap.addClass("for-image");

	};
	window._pb_image_input.prototype = $.extend({},  window._pb_file_input.prototype);

	_pb_image_input.prototype.add_file = (function(data_){
		if(!this.check_limit()) return;

		var o_name_ = !data_['o_name'] ? data_['r_name'] : data_['o_name'];
		var preview_file_item_ = $("<div class='file-item'>\
			<div class='icon image'><img src='"+PB.filebase_url((data_['thumbnail'] || data_['r_name']))+"' class='preview'></div> \
			<span class='text'>"+o_name_+"</span> \
			<a class='delete-btn' href='#' data-delete-button></a> \
		</div>");

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

	$.fn.pb_image_input = (function(options_){
		var module_ = this.data('pb-image-input-module');
		if(module_) return module_;
		return new _pb_image_input(this, options_);
	});

	
})(jQuery);