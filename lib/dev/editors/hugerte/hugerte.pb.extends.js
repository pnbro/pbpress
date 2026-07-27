jQuery(function($){

	var _pb_hugerte_escape_html_ = function(str_){
		str_ = str_ === undefined || str_ === null ? "" : String(str_);
		return str_
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;");
	};

	window.pb_wysiwyg_editor_hugerte = function(target_, options_){
		window.pb_wysiwyg_editor_interface.apply(this, [target_, options_]);

		if(typeof window.hugerte === 'undefined'){
			console.error('hugerte is not loaded.');
			return;
		}

		options_ = $.extend({
			lang : null,
			min_height : null,
			max_height : null,
			height : 200,
			placeholder : null,
			input : null,
			base_url : null,
			content_css : null,
		}, this._options);

		this._instance = null;
		this._input = options_['input'] ? $(options_['input']) : null;
		this._pending_content_ = undefined;
		this._uploader_modal = null;
		this._sync_timer_ = null;

		var self = this;

		var init_options_ = {
			target : this._target[0],
			base_url : options_['base_url'],
			language : options_['lang'],
			menubar : false,
			branding : false,
			promotion : false,
			statusbar : true,
			resize : true,
			height : options_['height'],
			placeholder : options_['placeholder'],
			skin : 'oxide',
			//본문(iframe)은 관리자 CSS 를 상속하지 않는다 → 관리자 글꼴 css 를 따로 주입
			content_css : options_['content_css'] ? ['default', options_['content_css']] : 'default',
			convert_urls : false,
			relative_urls : false,
			remove_script_host : false,
			entity_encoding : 'raw',
			browser_spellcheck : true,
			contextmenu : false,
			plugins : 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table codesample directionality nonbreaking pagebreak wordcount',
			toolbar : 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link pbimage media table | removeformat code fullscreen',
			//wrap : 툴바가 길어도 "..." 오버플로우로 숨기지 않고 여러 줄로 접어서 전부 노출한다
			toolbar_mode : 'wrap',
			font_size_formats : '8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 22pt 24pt 36pt',
			image_advtab : true,
			automatic_uploads : true,
			images_upload_handler : function(blob_info_, progress_){
				return new Promise(function(resolve_, reject_){
					var blob_ = blob_info_.blob();
					var file_name_ = blob_info_.filename();
					var file_ = new File([blob_], file_name_, { type : blob_.type });

					PB.file.chunk_upload([file_], {
						progress : function(total_percent_){
							progress_(Math.ceil(total_percent_));
						},
						fail : function(response_json_, response_){
							reject_({ message : (response_json_ && response_json_['error_message']) ? response_json_['error_message'] : __("업로드에 실패했습니다."), remove : true });
						},
						done : function(response_json_, results_){
							if(!results_ || !results_.length || !results_[0]['r_name']){
								reject_({ message : __("업로드에 실패했습니다."), remove : true });
								return;
							}
							resolve_(PB.filebase_url(results_[0]['r_name']));
						}
					});
				});
			},
			setup : function(editor_){
				self._instance = editor_;

				var modal_uid_ = PB.random_string(5);

				var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-image-uploader-modal-'+modal_uid_+'">' +
					'<div class="modal-dialog">' +
						'<div class="modal-content">' +
							'<div class="modal-body">' +
								'<input id="pb-image-uploader-modal-input-'+modal_uid_+'" type="file" name="files[]" accept="image/*" multiple>' +
							'</div>' +
							'<div class="modal-footer">' +
								'<button type="button" class="btn btn-default" data-dismiss="modal">'+__("취소")+'</button>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>';

				self._uploader_modal = $(modal_html_);
				self._uploader_modal.appendTo("body");

				var modal_input_ = self._uploader_modal.find("#pb-image-uploader-modal-input-"+modal_uid_);

				modal_input_.pb_file_dragzone({
					limit : null,
					button_label : __("이미지 선택하기"),
					label : __('이미지를 드래그하여 업로드할 수 있습니다.'),
					file_selected : $.proxy(function(selected_files_){

						this['modal_input'].pb_file_dragzone().upload($.proxy(function(result_, response_json_, results_){
							if(!result_) return;

							for(var row_index_ = 0; row_index_<results_.length; ++row_index_){
								var uploaded_data_ = results_[row_index_];
								var image_url_ = _pb_hugerte_escape_html_(PB.filebase_url(uploaded_data_['r_name']));
								var alt_ = _pb_hugerte_escape_html_(uploaded_data_['o_name']);
								this['editor'].insertContent('<img src="'+image_url_+'" alt="'+alt_+'">');
							}

							this['modal'].modal("hide");

						}, this));
					}, {
						modal : self._uploader_modal,
						modal_input : modal_input_,
						editor : editor_
					}),
				});

				editor_.ui.registry.addButton('pbimage', {
					icon : 'image',
					tooltip : __("이미지 추가"),
					onAction : $.proxy(function(){
						this._uploader_modal.modal("show");
					}, self)
				});

				editor_.on('remove', $.proxy(function(){
					if(this._uploader_modal){
						this._uploader_modal.remove();
						this._uploader_modal = null;
					}
				}, self));

				var sync_content_ = $.proxy(function(){
					var content_ = this.content();
					this._options['sync'].apply(this, [content_]);
					if(this._input) this._input.val(content_);
				}, self);

				//타이핑 중에는 지연 동기화 - getContent()는 문서 전체 직렬화라 매 입력마다 돌리면 느려진다
				var sync_content_delayed_ = function(){
					if(self._sync_timer_) clearTimeout(self._sync_timer_);
					self._sync_timer_ = setTimeout(function(){
						self._sync_timer_ = null;
						sync_content_();
					}, 300);
				};

				editor_.on('change undo redo SetContent ExecCommand', sync_content_);
				editor_.on('blur', sync_content_);
				editor_.on('input', sync_content_delayed_);
			},
			init_instance_callback : function(editor_){
				if(self._pending_content_ !== undefined){
					editor_.setContent(self._pending_content_);
					self._pending_content_ = undefined;
				}

				//폼 전송 시 원본 textarea(name 속성)로 값 반영 - hugerte 내장 add_form_submit_trigger 보완
				var parent_form_ = self._target.closest("form");
				if(parent_form_.length > 0){
					parent_form_.on("submit", function(){
						if(self._instance && self._instance.initialized){
							self._instance.save();
						}
						self._options['sync'].apply(self, [self.content()]);
						if(self._input) self._input.val(self.content());
					});
				}

				self._options['sync'].apply(self, [self.content()]);
			}
		};

		if(options_['min_height']) init_options_['min_height'] = options_['min_height'];
		if(options_['max_height']) init_options_['max_height'] = options_['max_height'];

		hugerte.init(init_options_);
	};
	pb_wysiwyg_editor_hugerte.prototype = $.extend({}, window.pb_wysiwyg_editor_interface.prototype);
	pb_wysiwyg_editor_hugerte.prototype.instance = function(){
		return this._instance;
	}
	pb_wysiwyg_editor_hugerte.prototype.content = function(content_){
		var initialized_ = this._instance && this._instance.initialized;

		if(!initialized_){
			if(content_ !== undefined){
				this._pending_content_ = content_;
				return content_;
			}

			return this._pending_content_ !== undefined ? this._pending_content_ : this._target.val();
		}

		if(content_ !== undefined){
			this._instance.setContent(content_);
		}

		return this._instance.getContent();
	}

	$.fn.pb_wysiwyg_editor_hugerte = function(options_){
		var module_ = this.data('pb_wysiwyg_editor_module');
		if(module_) return module_;
		return new pb_wysiwyg_editor_hugerte(this, options_);
	};

});
