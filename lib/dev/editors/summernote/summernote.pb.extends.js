jQuery(function($){
	$.extend($.summernote.plugins, {
		
		'pbimage': function(context_){
			var self = this;

			context_.memo('button.pbimage', $.proxy(function(){

				var button_ = $.summernote.ui.button({
					contents: '<i class="note-icon-picture"></i>',
					click: $.proxy(function(event_){
						this._uploader_modal.modal("show");
					},this)
				});

				this._button = button_.render();
				return this._button;
			}, this));

			this.initialize = $.proxy(function(){
				var module_ = this['module'];

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
								this['context'].invoke('editor.insertImage', image_url_, uploaded_data_['o_name']);	
							}

							this['modal'].modal("hide");

						}, this));
					}, {
						modal : module_._uploader_modal,
						modal_input : modal_input_,
						context : context_
					}),
				});

			}, {
				module : this,
				context : context_
			});

			this.destroy = function () {
				/*this.$panel.remove();
				this.$panel = null;*/
			};
		}
	});
});

jQuery(function($){
	window.pb_wysiwyg_editor_summernote = function(target_, options_){
		window.pb_wysiwyg_editor_interface.apply(this, [target_, options_]);

		options_ = $.extend({
			min_height : null,
			max_height : null,
			height : 200,
		}, options_);

		var placeholder_ = options_['placeholder'];
		
		options_ = $.extend(options_, {
			placeholder: placeholder_,
			minHeight: options_['min_height'],
			maxHeight: options_['max_height'],
			height: options_['height'],
			fullscreen : true,
			dialogsInBody: true,
			disableDragAndDrop: true,
			fontSizes: ['8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '22', '24', '36'],
			callbacks : {
				onBlur : $.proxy(function(){
					this._options['sync'].apply(this, [this.content()])
				}, this),
				onChange : $.proxy(function(){
					this._options['sync'].apply(this, [this.content()])
				}, this),
			},
			toolbar : [
				['font', ['style', 'fontsize', 'bold', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
				['color', ['color']],
				['insert', ['link', 'pbimage', 'video']],
				['para', ['ul', 'ol', 'paragraph']],
				['height', ['height']],
				['misc', ['codeview', 'fullscreen']]
			]
		});

		this._instance = this._target.summernote(options_);
	};
	pb_wysiwyg_editor_summernote.prototype = $.extend({}, window.pb_wysiwyg_editor_interface.prototype);
	pb_wysiwyg_editor_summernote.prototype.instance = function(){
		return this._instance;
	}
	pb_wysiwyg_editor_summernote.prototype.content = function(content_){
		if(content_ !== undefined){
			this._target.summernote("code", content_);
		}

		return this._target.summernote("code");
	}

	$.fn.pb_wysiwyg_editor_summernote = function(options_){
		var module_ = this.data('pb_wysiwyg_editor_module');
		if(module_) return module_;
		return new pb_wysiwyg_editor_summernote(this, options_);
	};


});