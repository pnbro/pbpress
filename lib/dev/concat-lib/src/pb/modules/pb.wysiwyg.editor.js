jQuery(function($){
	window.pb_wysiwyg_editor_interface = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			'sync' : $.noop,
		}, options_);

		this._target.data('pb_wysiwyg_editor_module', this);
	};

	pb_wysiwyg_editor_interface.prototype.target = function(){
		return this._target;
	}
	pb_wysiwyg_editor_interface.prototype.options = function(options_){
		if(options_ !== undefined){
			this._options = $.extend(this._options, options_);
		}
		return this._options;
	}
	pb_wysiwyg_editor_interface.prototype.instance = function(){
		console.error('override pb_wysiwyg_editor_interface.prototype.instance method.');
	}
	pb_wysiwyg_editor_interface.prototype.content = function(content_){ //setter, getter
		console.error('override pb_wysiwyg_editor_interface.prototype.content method.');
	};

	$.fn.pb_wysiwyg_editor = function(){
		var module_ = this.data('pb_wysiwyg_editor_module');
		if(module_) return module_;
		return null;
	};
});