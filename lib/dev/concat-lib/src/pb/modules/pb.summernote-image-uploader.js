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
				module_._uploader_modal.find("#pb-image-uploader-modal-input-"+module_._modal_uid).pb_fileupload_btn({
					upload_url : PB.file.upload_url(),
					label : "이미지 업로드",
					button_class : "btn-default btn-sm",
					dropzone : module_._uploader_modal,
					acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
					autoupload : true,
					limit : 10,
					done : $.proxy(function(files_){
						var module_ = this['module'];
						var context_ = this['context'];

						$.each(files_, function(){
							context_.invoke('editor.insertImage', PBVAR['home_url']+"uploads/"+this['upload_path']+this['r_name'], this['o_name']);	
						});
						
						module_._uploader_modal.modal("hide");
					}, this)
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