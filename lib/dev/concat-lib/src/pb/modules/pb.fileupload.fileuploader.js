(function($){

	var _pb_file_uploader_btn = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(true, {
			'upload_path' : null,
			'maxlength' : 10,
			'modal_label' : '파일업로드',
			'callback' : $.noop,
		}, options_);


		this._modal_uid = PB.random_string(5);

		var modal_html_ = '<div class="pb-fileupload-dropzone-modal modal" id="pb-file-uploader-modal-'+this._modal_uid+'">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					'<div class="modal-body">' +
						'<input id="pb-file-uploader-modal-input-'+this._modal_uid+'" type="file" name="files[]" accept="image/*,.zip,.pdf,.hwp,.xls,.xlsx,.doc,.docx,*.ppt,*.pptx" multiple>' +
					'</div>' +
					'<div class="modal-footer">' +
						'<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

		this._uploader_modal = $(modal_html_);
		this._uploader_modal.appendTo("body");

		this._uploader_modal.find("#pb-file-uploader-modal-input-"+this._modal_uid).pb_fileupload_btn({
			upload_url : PB.file.upload_url({
				upload_dir : this._options['upload_path']
			}),
			label : this._options['modal_label'],
			button_class : "btn-default btn-sm",
			dropzone : this._uploader_modal,
			acceptFileTypes: /(\.|\/)(zip|hwp|pdf|doc|docx|ppt|pptx|xls|xlsx|jpe?g|png)$/i,
			autoupload : true,
			limit : this._options['maxlength'],
			done : $.proxy(function(files_){
				var module_ = this;
				this._options['callback'].apply(this, [files_]);
				this._uploader_modal.modal("hide");
			}, this)
		});

		this._target.click($.proxy(function(){
			$("#pb-file-uploader-modal-"+this._modal_uid).modal("show");
			return false;
		}, this));

		this._target.data('pb-file-uploader-module', this);
	}

	$.fn.pb_file_uploader_btn = (function(options_){
		var module_ = this.data('pb-file-uploader-module');
		if(module_) return module_;
		return new _pb_file_uploader_btn(this, options_);
	});
	
	var _pb_multiple_file_uploader = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(true, {
			'upload_path' : null,
			'maxlength' : 10,
			'modal_label' : '파일업로드',
			'change' : $.noop
		}, options_);

		this._target.wrap("<div class='pb-multiple-file-uploader pb-file-list-frame'></div>");
		this._wrap = this._target.parent();
		
		this._add_btn = $("<a href='#' class='add-btn btn btn-sm btn-default'><i class='material-icons'>add</i> 파일추가</a>");
		this._add_btn.appendTo(this._wrap);

		this._target.data('pb-multiple-file-uploader-module', this);

		this._modal_uid = PB.random_string(5);

		var modal_html_ = '<div class="pb-fileupload-dropzone-modal modal" id="pb-multiple-file-uploader-modal-'+this._modal_uid+'">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					'<div class="modal-body">' +
						'<input id="pb-multiple-file-uploader-modal-input-'+this._modal_uid+'" type="file" name="files[]" accept="file/*" multiple>' +
					'</div>' +
					'<div class="modal-footer">' +
						'<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

		this._uploader_modal = $(modal_html_);
		this._uploader_modal.appendTo("body");

		this._uploader_modal.find("#pb-multiple-file-uploader-modal-input-"+this._modal_uid).pb_fileupload_btn({
			upload_url : PB.file.upload_url({
				upload_dir : this._options['upload_path']
			}),
			label : this._options['modal_label'],
			button_class : "btn-default btn-sm",
			dropzone : true,
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
			$("#pb-multiple-file-uploader-modal-"+this._modal_uid).modal("show");
			return false;
		}, this));

		this._wrap.sortable({
			items: '.file-item',
			draggable : ".file-item",
			onSort: $.proxy(function(){
				this._apply_to_input();
				this._options['change'].apply(this);
			}, this),
		});

		var target_json_ = null;

		try{
			target_json_ = JSON.parse(this._target.val());
		}catch(e_){
			target_json_ = [];
		}

		this.apply_json(target_json_);
	};

	_pb_multiple_file_uploader.prototype.target = (function(){
		return this._target;
	});

	_pb_multiple_file_uploader.prototype.options = (function(options_){
		if(options_ !== undefined){
			this._options = $.extend(true, {
				'maxlength' : 10,
				'modal_label' : '파일업로드'
			}, options_);
		}

		return this._options;
	});

	_pb_multiple_file_uploader.prototype.add = (function(data_){
		
		var file_item_el_ = $("<div class='file-item'></div>");

			file_item_el_.append('<i class="icon material-icons">insert_drive_file</i>');
			file_item_el_.append('<span>' + data_['o_name'] + '</span>');
			file_item_el_.append('<a href="#" class="delete-btn"><i class="material-icons">close</i></a>');			

		file_item_el_.attr("data-r-name", data_['r_name']);
		file_item_el_.attr("data-o-name", data_['o_name']);

		this._add_btn.before(file_item_el_);

		file_item_el_.find(".delete-btn").click($.proxy(function(event_){
			$(event_.currentTarget).closest(".file-item").remove();
			this._update_add_btn();
			this._apply_to_input();
			this._options['change'].apply(this);
			return false;
		}, this));

		this._update_add_btn();
		this._apply_to_input();
		this._options['change'].apply(this);	
		
	});

	_pb_multiple_file_uploader.prototype._update_add_btn = (function(){
		var toggle_ = (this._options['maxlength'] > this._wrap.find(".file-item").length);
		this._add_btn.toggle(toggle_);
	});
		
	_pb_multiple_file_uploader.prototype.apply_json = (function(json_){
		this._wrap.find(".file-item").remove();

		if($.type(json_) !== "array") return;

		var that_ = this;
		$.each(json_, function(){
			that_.add(this);
		});
		this._apply_to_input();
		this._update_add_btn();
	});
	_pb_multiple_file_uploader.prototype.to_json = (function(){
		var file_items_ = this._wrap.children(".file-item");
		var results_ = [];

		if(file_items_.length > 0){
			file_items_.each(function(){
				var file_item_ = $(this);

				results_.push({
					o_name : file_item_.attr("data-o-name"),
					r_name : file_item_.attr("data-r-name"),
				});
			});
		}
			

		return results_;
	});
	_pb_multiple_file_uploader.prototype._apply_to_input = (function(){
		this._target.val(JSON.stringify(this.to_json()));
	});


	$.fn.pb_multiple_file_uploader = (function(options_){
		var module_ = this.data('pb-multiple-file-uploader-module');
		if(module_) return module_;
		return new _pb_multiple_file_uploader(this, options_);
	});

	
})(jQuery);