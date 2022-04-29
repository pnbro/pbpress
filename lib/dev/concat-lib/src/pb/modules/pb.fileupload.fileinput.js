(function($){
	
	var _pb_file_input = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(true, {
			'upload_path' : null,
			'modal_label' : __('파일업로드'),
			
			'o_file_input' : null,

		}, options_);

		this._target.wrap("<div class='pb-file-input-uploader pb-file-list-frame'></div>");
		this._wrap = this._target.parent();
		
		this._add_btn = $("<a href='#' class='btn btn-default btn-xs add-btn'><i class='icon material-icons'>note_add</i></a>");
		this._add_btn.appendTo(this._wrap);

		if(!this._options['upload_path']){
			this._options['upload_path'] = this._target.attr("data-upload-path") || null;
		}

		if(!this._options['o_file_input']){
			this._options['o_file_input'] = this._target.attr("data-o-file-ipnut") || null;
		}

		this._target.data('pb-file-input-module', this);

		this._modal_uid = PB.random_string(5);

		var modal_html_ = '<div class="pb-fileupload-dropzone-modal modal" id="pb-file-input-uploader-modal-'+this._modal_uid+'">' +
			'<div class="modal-dialog">' +
				'<div class="modal-content">' +
					'<div class="modal-body">' +
						'<input id="pb-file-input-uploader-modal-input-'+this._modal_uid+'" type="file" name="files[]" accept="file/*" multiple>' +
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

		this._uploader_modal.find("#pb-file-input-uploader-modal-input-"+this._modal_uid).pb_fileupload_btn({
			upload_url : PB.file.upload_url({
				upload_dir : this._options['upload_path']
			}),
			label : this._options['modal_label'],
			button_class : "btn-default btn-sm",
			dropzone : this._uploader_modal,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			autoupload : true,
			limit : 1,
			done : $.proxy(function(files_){
				var module_ = this;
				$.each(files_, function(){
					module_.update_file(this);
				});
			
				this._uploader_modal.modal("hide");
			}, this)
		});

		this._add_btn.click($.proxy(function(){
			$("#pb-file-input-uploader-modal-"+this._modal_uid).modal("show");
			return false;
		}, this));

		var default_ = this._target.val();

		if(default_ !== null && default_ !== ""){
			var o_file_input_ = $(this._options['o_file_input']);

			var o_file_name_ = null;

			if(o_file_input_.length > 0){
				o_file_name_ = o_file_input_.val();	
				if(o_file_name_ === null || o_file_name_ === "") o_file_name_ = default_;

			}else{
				o_file_name_ = default_;
			}

			this.update_file({
				upload_path : this._options['upload_path'],
				o_name : o_file_name_,
				r_name : default_,
			});

		}

	};

	_pb_file_input.prototype.target = (function(){
		return this._target;
	});

	_pb_file_input.prototype.update_file = (function(data_){
		this._wrap.find(".file-item").remove();
		this._add_btn.toggle(true);
		this._target.val("");
		var o_file_input_ = $(this._options['o_file_input']);
		
		if(o_file_input_.length > 0){
			o_file_input_.val("");
		}

		if(!data_) return;

		var preview_item_ = $("<div class='preview'><i class='icon material-icons'>insert_drive_file</i> "+data_['o_name']+"</div>");

		var file_item_el_ = $("<div class='file-item'></div>");
			file_item_el_.append(preview_item_);
			file_item_el_.append("<a href='#' class='delete-btn'></a>");

		this._target.val(data_['r_name']);

		if(o_file_input_.length > 0){
			o_file_input_.val(data_['o_name']);
		}

		this._add_btn.before(file_item_el_);

		file_item_el_.find(".delete-btn").click($.proxy(function(event_){
			this.update_file(null);
			return false;
		}, this));

		this._add_btn.toggle(false);
	});
		
	$.fn.pb_file_input = (function(options_){
		var module_ = this.data('pb-file-input-module');
		if(module_) return module_;
		return new _pb_file_input(this, options_);
	});

	
})(jQuery);