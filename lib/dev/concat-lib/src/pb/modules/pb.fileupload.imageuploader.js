(function($){

	var _pb_image_uploader_btn = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(true, {
			'upload_path' : null,
			'maxlength' : 10,
			'modal_label' : '이미지업로드',
			'use_thumbnail' : true,
			'callback' : $.noop,
		}, options_);


		this._modal_uid = PB.random_string(5);

		var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-image-uploader-modal-'+this._modal_uid+'">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					'<div class="modal-body">' +
						'<input id="pb-image-uploader-modal-input-'+this._modal_uid+'" type="file" name="files[]" accept="image/*" multiple>' +
					'</div>' +
					'<div class="modal-footer">' +
						'<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

		this._uploader_modal = $(modal_html_);
		this._uploader_modal.appendTo("body");

		this._uploader_modal.find("#pb-image-uploader-modal-input-"+this._modal_uid).pb_fileupload_btn({
			upload_url : PB.file.upload_url({
				upload_dir : this._options['upload_path']
			}),
			label : this._options['modal_label'],
			button_class : "btn-default btn-sm",
			dropzone : this._uploader_modal,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			autoupload : true,
			limit : this._options['maxlength'],
			done : $.proxy(function(files_){
				var module_ = this;
				this._options['callback'].apply(this, [files_]);
				this._uploader_modal.modal("hide");
			}, this)
		});

		this._target.click($.proxy(function(){
			$("#pb-image-uploader-modal-"+this._modal_uid).modal("show");
			return false;
		}, this));

		this._target.data('pb-image-uploader-module', this);
	}

	$.fn.pb_image_uploader_btn = (function(options_){
		var module_ = this.data('pb-image-uploader-module');
		if(module_) return module_;
		return new _pb_image_uploader_btn(this, options_);
	});
	
	var _pb_multiple_image_uploader = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(true, {
			'upload_path' : null,
			'maxlength' : 10,
			'modal_label' : '이미지업로드',
			'change' : $.noop,
			'max_width' : null,
			'input_to_path' : false,
		}, options_);

		this._target.wrap("<div class='pb-multiple-image-uploader pb-image-list-frame'></div>");
		this._wrap = this._target.parent();
		this._
		
		this._add_btn = $("<a href='#' class='add-btn'>+</a>");
		this._add_btn.appendTo(this._wrap);

		this._target.data('pb-multiple-image-uploader-module', this);

		this._modal_uid = PB.random_string(5);

		var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-multiple-image-uploader-modal-'+this._modal_uid+'">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					'<div class="modal-body">' +
						'<input id="pb-multiple-image-uploader-modal-input-'+this._modal_uid+'" type="file" name="files[]" accept="image/*" multiple>' +
					'</div>' +
					'<div class="modal-footer">' +
						'<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

		this._uploader_modal = $(modal_html_);
		this._uploader_modal.appendTo("body");

		var upload_url_ = this._options['upload_url'];

		if(this._options['max_width']){
			upload_url_ = PB.file.upload_url({
				'max_width' : this._options['max_width']
			});
		}

		this._uploader_modal.find("#pb-multiple-image-uploader-modal-input-"+this._modal_uid).pb_fileupload_btn({
			upload_url : PB.file.upload_url({
				upload_dir : this._options['upload_path']
			}),
			label : this._options['modal_label'],
			button_class : "btn-default btn-sm",
			dropzone : this._uploader_modal,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			autoupload : true,
			limit : this._options['maxlength'],
			done : $.proxy(function(files_){
				var module_ = this;
				$.each(files_, function(){
					module_.add(this);
				});
			
				this._uploader_modal.modal("hide");
			}, this)
		});

		this._add_btn.click($.proxy(function(){
			$("#pb-multiple-image-uploader-modal-"+this._modal_uid).modal("show");
			return false;
		}, this));

		this._wrap.sortable({
			items: '.image-item',
			onSort: $.proxy(function(){
				this._apply_to_input();
				this._options['change'].apply(this);
			}, this),
		});

		this._wrap.on("sortupdate",$.proxy(function(){
			this._apply_to_input();
		}, this));

		var target_json_ = null;

		try{
			target_json_ = JSON.parse(this._target.val());
		}catch(e_){
			target_json_ = [];
		}

		this.apply_json(target_json_);
	};

	_pb_multiple_image_uploader.prototype.target = (function(){
		return this._target;
	});

	_pb_multiple_image_uploader.prototype.options = (function(options_){
		if(options_ !== undefined){
			this._options = $.extend(true, {
				'maxlength' : 10,
				'modal_label' : '이미지업로드'
			}, options_);
		}

		return this._options;
	});

	_pb_multiple_image_uploader.prototype.add = (function(data_){
		var preview_el_ = $("<div class='preview'></div>");

			var preview_url_ = PB.filebase_url((this._options['use_thumbnail'] ? data_['thumbnail'] : data_['r_name']), data_['upload_path']);
			
			preview_el_.css({
				'backgroundImage' : "url(\""+preview_url_+"\")"
			});

		var image_item_el_ = $("<div class='image-item'></div>");
			image_item_el_.append(preview_el_);
			image_item_el_.append("<a href='#' class='delete-btn'></a>");

		image_item_el_.attr("data-thumbnail", data_['thumbnail']);
		image_item_el_.attr("data-upload-path", data_['upload_path']);
		image_item_el_.attr("data-o-name", data_['o_name']);
		image_item_el_.attr("data-r-name", data_['r_name']);
		image_item_el_.attr("data-type", data_['type']);

		this._add_btn.before(image_item_el_);

		image_item_el_.find(".delete-btn").click($.proxy(function(event_){
			$(event_.currentTarget).closest(".image-item").remove();
			this._update_add_btn();
			this._apply_to_input();
			this._options['change'].apply(this);
			return false;
		}, this));

		this._update_add_btn();
		this._apply_to_input();
		this._options['change'].apply(this);	
		
	});

	_pb_multiple_image_uploader.prototype._update_add_btn = (function(){
		var toggle_ = (this._options['maxlength'] > this._wrap.find(".image-item").length);
		this._add_btn.toggle(toggle_);
	});
		
	_pb_multiple_image_uploader.prototype.apply_json = (function(json_){
		this._wrap.find(".image-item").remove();

		if($.type(json_) !== "array") return;

		var that_ = this;
		$.each(json_, function(){
			that_.add(this);
		});
		this._apply_to_input();
		this._update_add_btn();
	});
	_pb_multiple_image_uploader.prototype.to_json = (function(){
		var image_items_ = this._wrap.children(".image-item");
		var results_ = [];

		if(image_items_.length > 0){
			image_items_.each(function(){
				var image_item_ = $(this);

				results_.push({
					o_name : image_item_.attr("data-o-name"),
					r_name : image_item_.attr("data-r-name"),
					thumbnail : image_item_.attr("data-thumbnail"),
					upload_path : image_item_.attr("data-upload-path"),
					type : image_item_.attr("data-type"),
				});
			});
		}
			

		return results_;
	});
	_pb_multiple_image_uploader.prototype._apply_to_input = (function(){
		if(this._options['input_to_path']){
			var data_ = this.to_json();
			var results_ = [];

			$.each(data_, function(){
				results_.push(this['path']);
			});
			this._target.val(results_.join(","));
		}else{
			this._target.val(JSON.stringify(this.to_json()));
		}
		
	});


	$.fn.pb_multiple_image_uploader = (function(options_){
		var module_ = this.data('pb-multiple-image-uploader-module');
		if(module_) return module_;
		return new _pb_multiple_image_uploader(this, options_);
	});

	
})(jQuery);