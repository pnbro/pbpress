(function($){
	
	var _pb_image_input = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(true, {
			'upload_path' : null,
			'modal_label' : __('이미지업로드'),
			'modal_desc' : __('파일을 드래그하여 업로드할 수 있습니다.'),

			'accept' : "image/*",
			'o_file_input' : null,
			'thumbnail_input' : null,

		}, options_);

		this._target.wrap("<div class='pb-image-input-uploader pb-image-list-frame'></div>");
		this._wrap = this._target.parent();
		
		this._add_btn = $("<a href='#' class='add-btn'>+</a>");
		this._add_btn.appendTo(this._wrap);

		if(!this._options['upload_path']){
			this._options['upload_path'] = this._target.attr("data-upload-path") || null;
		}

		if(!this._options['o_file_input']){
			this._options['o_file_input'] = this._target.attr("data-o-file-ipnut") || null;
		}

		if(!this._options['thumbnail_input']){
			this._options['thumbnail_input'] = this._target.attr("data-thumbnail-ipnut") || null;
		}

		this._target.data('pb-image-input-module', this);

		this._modal_uid = PB.random_string(5);

		var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-image-input-uploader-modal-'+this._modal_uid+'">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					'<div class="modal-body">' +
						'<input id="pb-image-input-uploader-modal-input-'+this._modal_uid+'" type="file" name="files[]" accept="'+this._options['accept']+'" multiple>' +
					'</div>' +
					'<div class="modal-footer">' +
						'<button type="button" class="btn btn-default" data-dismiss="modal">'+__('취소')+'</button>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

		this._uploader_modal = $(modal_html_);
		this._uploader_modal.appendTo("body");

		var upload_url_ = this._options['upload_url'];

		this._uploader_modal.find("#pb-image-input-uploader-modal-input-"+this._modal_uid).pb_fileupload_btn({
			upload_url : PB.file.upload_url({
				upload_dir : this._options['upload_path']
			}),
			label : this._options['modal_label'],
			label_dropzone : this._options['modal_desc'],
			button_class : "btn-default btn-sm",
			dropzone : this._uploader_modal,

			autoupload : true,
			limit : 1,
			done : $.proxy(function(files_){
				var module_ = this;
				$.each(files_, function(){
					module_.update_image(this);
				});
			
				this._uploader_modal.modal("hide");
			}, this)
		});

		this._add_btn.click($.proxy(function(){
			$("#pb-image-input-uploader-modal-"+this._modal_uid).modal("show");
			return false;
		}, this));

		var default_ = this._target.val();

		if(default_ !== null && default_ !== ""){
			var o_file_input_ = $(this._options['o_file_input']);
			var thumbnail_input_ = $(this._options['thumbnail_input']);

			var o_file_name_ = null;
			var thumbnail_ = null;

			if(o_file_input_.length > 0){
				o_file_name_ = o_file_input_.val();	
				if(o_file_name_ === null || o_file_name_ === "") o_file_name_ = default_;

			}else{
				o_file_name_ = default_;
			}

			if(thumbnail_input_.length > 0){
				thumbnail_ = thumbnail_input_.val();	
				if(thumbnail_ === null || thumbnail_ === "") thumbnail_ = default_;
				
			}else{
				thumbnail_ = default_;
			}

			
			this.update_image({
				upload_path : this._options['upload_path'],
				thumbnail : thumbnail_,
				o_name : o_file_name_,
				r_name : default_,
			});

		}

	};

	_pb_image_input.prototype.target = (function(){
		return this._target;
	});

	_pb_image_input.prototype.update_image = (function(data_){
		this._wrap.find(".image-item").remove();
		this._add_btn.toggle(true);
		this._target.val("");
		var o_file_input_ = $(this._options['o_file_input']);
		var thumbnail_input_ = $(this._options['thumbnail_input']);

		if(o_file_input_.length > 0){
			o_file_input_.val("");
		}

		if(thumbnail_input_.length > 0){
			thumbnail_input_.val("");
		}

		if(!data_) return;

		data_['upload_path'] = data_['upload_path'] === undefined || data_['upload_path'] === null ? "" : data_['upload_path'];

		var preview_el_ = $("<div class='preview'></div>");
		var preview_url_ = PB.filebase_url(data_['thumbnail'], data_['upload_path']);
			preview_el_.css({
				'backgroundImage' : "url(\""+preview_url_+"\")"
			});

		var image_item_el_ = $("<div class='image-item'></div>");
			image_item_el_.append(preview_el_);
			image_item_el_.append("<a href='#' class='delete-btn'></a>");

		this._target.val(data_['r_name']);

		if(o_file_input_.length > 0){
			o_file_input_.val(data_['o_name']);
		}

		if(thumbnail_input_.length > 0){
			thumbnail_input_.val(data_['thumbnail']);
		}

		this._add_btn.before(image_item_el_);

		image_item_el_.find(".delete-btn").click($.proxy(function(event_){
			this.update_image(null);
			return false;
		}, this));

		this._add_btn.toggle(false);
	});
		
	$.fn.pb_image_input = (function(options_){
		var module_ = this.data('pb-image-input-module');
		if(module_) return module_;
		return new _pb_image_input(this, options_);
	});

	
})(jQuery);