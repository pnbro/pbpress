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
				init: function(module_){


					module_._modal_uid = PB.random_string(5);

					var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-image-uploader-modal-'+module_._modal_uid+'">' +
						'<div class="modal-dialog">' +
							'<div class="modal-content">' +
								'<div class="modal-body">' +
									'<input id="pb-image-uploader-modal-input-'+module_._modal_uid+'" type="file" name="files[]" accept="image/*" multiple>' +
								'</div>' +
								'<div class="modal-footer">' +
									'<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>';

					module_._uploader_modal = $(modal_html_);
					module_._uploader_modal.appendTo("body");

					var modal_input_ = module_._uploader_modal.find("#pb-image-uploader-modal-input-"+module_._modal_uid);

					modal_input_.pb_file_dragzone({
						limit : null,
						button_label : __("이미지 선택하기"),
						label : __('이미지를 드래그하여 업로드할 수 있습니다.'),
						file_selected : $.proxy(function(selected_files_){
							
							this['modal_input'].pb_file_dragzone().upload($.proxy(function(result_, response_json_, results_){
								if(!result_) return;

								for(var row_index_ =0; row_index_<results_.length; ++row_index_){
									var uploaded_data_ = results_[row_index_];
									var image_url_ = PB.filebase_url(uploaded_data_['r_name']);
									this['context'].execCmd('insertImage', image_url_, false, true);

								}

								this['modal'].modal("hide");

							}, this));
						}, {
							modal : module_._uploader_modal,
							modal_input : modal_input_,
							context : module_
						}),
					});

					module_.addBtnDef('pb_image_upload', {
						ico : "insert-image",
						fn : $.proxy(function(){
							this._uploader_modal.modal("show");
						},module_)
					});

				},
				destroy: function(trumbowyg_){
					this._uploader_modal.remove();
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