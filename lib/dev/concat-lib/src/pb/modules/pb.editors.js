jQuery(function($){

	window._pb_editor_data_map = {};

	window.pb_add_editor = function(key_, editor_data_){
		window._pb_editor_data_map[key_] = editor_data_;
		window._pb_editor_data_map[key_]['_initialized'] = false;
	}

	var pb_editors = window.pb_editors = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			input : null,
			height : 300,
		}, options_);

		this._el_navbar = this._target.children(".nav");
		this._el_tab_content = this._target.children(".tab-content");
		this._editors = $.extend(true, {}, window._pb_editor_data_map);

		this._el_navbar.find("a").on("shown.bs.tab", $.proxy(function(event_){
			var before_tab_ = $(event_.relatedTarget);
			var before_key_ = before_tab_.parent().attr("data-key");
			var target_tab_ = $(event_.target);
			var target_key_ = target_tab_.parent().attr("data-key");

			if(!this._editors[target_key_]._initialized){
				this._editors[target_key_]['initialize'].apply(this);
				this._editors[target_key_]._initialized = true;
			}
			if(before_key_){
				var source_html_ = this.html(before_key_);
				this.html(target_key_, source_html_);	
			}
			

		}, this));

		var parent_form_ = this._target.closest("form");

		if(parent_form_.length > 0){
			parent_form_.on("submit", $.proxy(function(){
				this.sync_input();
			}, this));
		}

		this._target.data("pb-editor-module", this);
	};

	pb_editors.prototype.actived_editor_id = function(){
		return this._el_navbar.children(".active").attr("data-key");
	}

	pb_editors.prototype.editor = function(key_){
		return this._editors[key_];
	};

	pb_editors.prototype.actived_editor = function(){
		return this._editors[this.actived_editor_id()];
	};

	pb_editors.prototype.toggle_editor = function(key_){
		this._el_navbar.find("[data-key='"+key_+"'] > a").tab('show');
	};

	pb_editors.prototype.add_editor = function(key_, editor_data_){
		this._editors[key_] = editor_data_;
		this._editors[key_]['_initialized'] = false;
	}

	pb_editors.prototype.html = function(key_, html_){
		if(key_ === undefined) key_ = this.actived_editor_id();
		return this._editors[key_]['html'].apply(this, [html_]);
	}
	pb_editors.prototype.sync_input = function(){
		var html_ = this.html();
		$(this._options['input']).val(html_);
	}

	$.fn.pb_editors = function(options_){
		var module_ = this.data("pb-editor-module");
		if(module_) return module_;
		return new pb_editors(this, options_);
	};
});