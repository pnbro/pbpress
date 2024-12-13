jQuery(function ($) {
	'use strict';

	$.trumbowyg.defaultOptions['autogrow'] = true;
	$.trumbowyg.defaultOptions['btns'] = [
		['viewHTML'],
		['undo', 'redo'],
		['formatting'],
		['strong', 'em', 'del'],
		['superscript', 'subscript'],
		['link', 'pb_image_upload'],
		['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
		['unorderedList', 'orderedList'],
		['horizontalRule'],
		['removeformat'],
	];

	$.extend(true, $.trumbowyg, {
		
		langs: {
			en: {
				pb_image_upload: 'Insert Image',
				pb_image_upload_dropzone_placeholder : "Upload Image"
			},
			ko: {
				pb_image_upload: __('이미지 추가'),
				pb_image_upload_dropzone_placeholder : __("이미지 업로드")
			},
		},
		plugins: {
			resizimg: {
				minSize: 64,
				step: 16,
			},
			pb_image_upload: {
				init: function(trumbowyg_){

					trumbowyg_._pb_image_upload_modal_uid = PB.random_string(5);

					var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-image-uploader-modal-'+trumbowyg_._pb_image_upload_modal_uid+'">' +
						'<div class="modal-dialog">' +
							'<div class="modal-content">' +
								'<div class="modal-body">' +
									'<input id="pb-image-uploader-modal-input-'+trumbowyg_._pb_image_upload_modal_uid+'" type="file" name="files[]" accept="image/*" multiple>' +
								'</div>' +
								'<div class="modal-footer">' +
									'<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>';

					trumbowyg_._pb_image_uploader_modal = $(modal_html_);
					trumbowyg_._pb_image_uploader_modal.appendTo("body");
					trumbowyg_._pb_image_uploader_modal.find("#pb-image-uploader-modal-input-"+trumbowyg_._pb_image_upload_modal_uid).pb_fileupload_btn({
						upload_url : PB.file.upload_url(),
						label : trumbowyg_.lang.pb_image_upload_dropzone_placeholder,
						button_class : "btn-default btn-sm",
						dropzone : trumbowyg_._pb_image_uploader_modal,
						acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
						autoupload : true,
						limit : 10,
						done : $.proxy(function(files_){
							var trumbowyg_ = this;
							$.each(files_, function(){
								var image_url_ = PB.filebase_url(this['r_name'], this['upload_path']);
								trumbowyg_.execCmd('insertImage', image_url_, false, true);
							});
							
							trumbowyg_._pb_image_uploader_modal.modal("hide");
						}, trumbowyg_)
					});
					
					trumbowyg_.addBtnDef('pb_image_upload', {
						ico : "insert-image",
						fn : $.proxy(function(){
							this._pb_image_uploader_modal.modal("show");
						},trumbowyg_)
					});
				},
				destroy: function(trumbowyg_){
					this._pb_image_uploader_modal.remove();
					delete this._pb_image_uploader_modal;
					delete this._pb_image_upload_modal_uid;
				}
			}
		}
	});	

	window.pb_wysiwyg_editor_trumbowyg = function(target_, options_){
		window.pb_wysiwyg_editor_interface.apply(this, [target_, options_]);
		this._input = $(options_['input']);
		this._instance = this._target.trumbowyg({
			lang : options_['lang'],
		});
		this._options['sync'].apply(this, [this.content()])

		this._instance.on('tbwblur', $.proxy(function(){
			var content_ = this.content();
			this._options['sync'].apply(this, [content_])
			this._input.val(content_);
		},this));
		this._instance.on('tbwchange', $.proxy(function(){
			var content_ = this.content();
		
			this._options['sync'].apply(this, [content_])
			this._input.val(content_);
		},this));
	};
	pb_wysiwyg_editor_trumbowyg.prototype = $.extend({}, window.pb_wysiwyg_editor_interface.prototype);
	pb_wysiwyg_editor_trumbowyg.prototype.instance = function(){
		return this._instance;
	}
	pb_wysiwyg_editor_trumbowyg.prototype.content = function(content_){
		if(content_ !== undefined){
			this._instance.html(content_);
		}

		return this._instance.html();	
	}
	$.fn.pb_wysiwyg_editor_trumbowyg = function(options_){
		var module_ = this.data('pb_wysiwyg_editor_module');
		if(module_) return module_;
		return new pb_wysiwyg_editor_trumbowyg(this, options_);
	};

});