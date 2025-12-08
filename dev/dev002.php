<form id="pb-test-form">
	<input type="hidden" id="pb-file-uloader-tester" data-limit="5" name="test">
</form>

<div id="pb-fileuloader-resource-manager">
	

</div>

<script type="text/javascript">
jQuery(function(){

	var pb_fileuploader_resource_manager = (function(target_, options_){
		this._target = target_;

		this._options = $.extend({
			

		},options_);

		this._target.data('pb-fileuploader-resource-manager-module', this);
	});

	PB.fileupload = $.extend(PB.fileupload, {
		_resource_manager : null,
		_resource_manager_module : null,
		resource_manager : function(options_){

			options_ = $.extend({
				modal_loaded : function(modal_){},
			}, options_);

			if(!PB.fileupload._resource_manager){
				PB.post("admin-fileuploader-load-resource-modal", {

				}, $.proxy(function(result_, response_json_){
					if(!result_ || response_json_.success !== true){
						PB.alert(response_json_.error_title || __("에러발생"), response_json_.error_message || __("통신 중, 에러가 발생했습니다."), __("확인"));
						return;
					}

					PB.fileupload._resource_manager = $(response_json_.modal);
					PB.fileupload._resource_manager.appendTo("body");
					PB.fileupload._resource_manager_module = new pb_fileuploader_resource_modal(PB.fileupload._resource_manager);

				}, {options : options_}), true);
			}

			this['options']['modal_loaded'].apply(this, [PB.fileupload._resource_manager]);

			PB.fileupload._resource_manager_module.modal("show");
		}
	});

	


	jQuery.fn.pb_fileuploader = function(options_){
		var module_ = this.data("pb-fileuploader-module");
		if(!module_) return new pb_fileuploader(this, options_);
		return module_;
	};


	var pb_fileuploader = (function(target_, options_){
		this._target = target_;

		this._options = $.extend({
			modal_loaded : function(modal_){}

		},options_);

		this._target.data('pb-fileuploader-module', this);
	});

	pb_fileuploader.prototype.load_modal = function(){

		

		this._resource_modal.modal("show");
	}

	jQuery.fn.pb_fileuploader = function(options_){
		var module_ = this.data("pb-fileuploader-module");
		if(!module_) return new pb_fileuploader(this, options_);
		return module_;
	};

	

});
</script>

<script type="text/javascript">
jQuery(document).ready(function(){
	window._file_uploader = $("#pb-file-uloader");
	window._file_uploader_module = window._file_uploader.pb_fileuploader();
});
</script>