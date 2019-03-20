(function($){

	function _pb_validator_input_event(event_){
		var module_ = $(event_.target).closest("form").validator();
		var target_input_ = $(event_.currentTarget);
		var input_name_ = target_input_.attr("name");

		module_._deferred_errors[input_name_] = [];

		$.each(module_._options['validators'], function(key_, validator_){
			if(!target_input_.attr("data-"+key_) && module_._options['common_validators'].indexOf(key_) < 0) return true;
			module_._deferred_errors[input_name_].push(key_);
		});

		clearTimeout(target_input_.data("pb-validator-timeout"));
		var timeout_ = setTimeout($.proxy(function(){
			this['module']._validate_input(this['input']);
		},{
			module : module_,
			input : $(event_.currentTarget)
		}), module_._options['delay']);

		target_input_.data("pb-validator-timeout", timeout_);
	}

	var pb_validator = window.pb_validator = (function(target_, options_){
		this._target = $(target_);
		this._options = $.extend(pb_validator.DEFAULTS, options_);	
		
		this._errors = {};
		this._deferred_errors = [];

		this._target.on('submit', $.proxy(function(event_){
			if(!this.is_valid()){

				var first_error_input_name_ = null;

				$.each(this._errors, function(input_name_, error_data_){
					if(Object.keys(error_data_).length > 0){
						first_error_input_name_ = input_name_;
						return false;							
					}
				});

				if(this._options['focus'] && first_error_input_name_){
					var first_error_input_ = this._target.find(":input[name='"+first_error_input_name_+"']");
						first_error_input_.focus();
				}
				
				this.validate();
				event_.preventDefault();
				return false;
			}
		}, this));

		this._target.on("reset", $.proxy(function(){
			this.clear_errors();
		}, this));
		this._target.attr('novalidate', true);
		this._target.data("pb-validator-module", this);

		this.refresh();
	});

	pb_validator.prototype.refresh = (function(){
		this._inputs = this._target.find(this._options['input_selector']);
		this._inputs.off("change keyup", _pb_validator_input_event);
		this._inputs.on("change keyup", _pb_validator_input_event);
		this._errors = {};
        this._deferred_errors = [];
		this.validate($.noop, false);
	});


	pb_validator.prototype.options = (function(options_){
		if(options_ !== undefined){
			this._options = $.extend(pb_validator.DEFAULTS, options_);
		}
		return this._options;
	});

	pb_validator.prototype._validate_input = (function(input_, show_error_){
		var module_ = this;
		var validator_data_ = this._options['validators'];

		show_error_ = (show_error_ !== undefined ? show_error_ : true);

		var promises_ = [];

		var input_name_ = input_.attr("name");

		this._errors[input_name_] = {};
		this._deferred_errors[input_name_] = [];

		$.each(validator_data_, function(key_, validator_){
			if(!input_.attr("data-"+key_) && module_._options['common_validators'].indexOf(key_) < 0) return true;

			var deferred_ = $.Deferred();
			validator_.apply(module_, [input_, $.proxy(function(result_, error_message_){

				var t_input_ = this['input'];
				var t_input_name_ = t_input_.attr("name");
				var t_module_ = this['module'];
				var t_validator_key_ = this['validator_key'];
				var t_deferred_ = this['deferred'];

				var deferred_index_ = t_module_._deferred_errors[t_input_name_].indexOf[t_validator_key_];
					t_module_._deferred_errors[t_input_name_].splice(deferred_index_, 1);

				if(result_){
					delete t_module_._errors[t_input_name_][t_validator_key_];
				}else{
					t_module_._errors[t_input_name_][t_validator_key_] = error_message_;
				}

				t_deferred_.resolve();

			}, {module : module_, input: input_, validator_key : key_, deferred : deferred_})]);

			var promise_ = deferred_.promise();

			if(show_error_){
				promise_.done($.proxy(function(){
					this['module'].show_errors(this['form_group']);
				}, {module : module_, form_group : input_.closest(".form-group")}));
			}
				
			promises_.push(promise_);
			module_._deferred_errors[input_name_].push(key_);
		});

		return $.when.apply($, promises_).then($.proxy(function(){
			var t_module_ = this['module'];
			var t_input_ = this['input'];
			var input_errors_ = t_module_._errors[t_input_.attr("name")];

			var valid_ = (Object.keys(input_errors_).length <= 0);
			t_module_._target.trigger((valid_ ? "pbvalidatorinputvalid" : "pbvalidatorinputinvalid"), [t_input_,input_errors_]);

			t_module_.toggle_submit();
		}, {module : module_, input: input_}));
	});

	pb_validator.prototype.validate = (function(callback_, show_errors_){
		callback_ = callback_ || $.noop;
		var module_ = this;
		var promises_ = [];
		this._inputs.each(function(){
			promises_.push(module_._validate_input($(this), show_errors_));
		});

		return $.when.apply($, promises_).then($.proxy(function(){
			var valid_ = this['module'].is_valid();
			this['module']._target.trigger((valid_ ? 'pbvalidatorvalid' : 'pbvalidatorinvalid'), [this['module']._errors]);
			this['callback'].apply(this['module'], [valid_]);
		}, {module : this, callback : callback_}));
	});
	pb_validator.prototype.is_valid = (function(callback_){
		var has_errors_ = false;
		var has_deferred_errors_ = false;

		$.each(this._errors, function(input_name_, error_data_){
			if(Object.keys(error_data_).length > 0){
				has_errors_ = true;
				return false;				
			}
		});

		$.each(this._deferred_errors, function(input_name_, validators_){
			if(validators_.length > 0){
				has_deferred_errors_ = true;
				return false;				
			}
		});

		return (!has_errors_ && !has_deferred_errors_);
	});

	pb_validator.input_value = (function(input_){
		if(input_[0].tagName === "select") return input_.find(":selected").val();

		var input_type_ = input_.attr("type");

		if(input_type_ === "radio" || input_type_ === "checkbox"){
			return $(":input[name='"+input_.attr("name")+"']:checked").val();
		}

		return input_.val();
	});

	pb_validator.error_message = (function(input_, key_, default_, data_){
		var input_el_ = input_[0];

		if(!default_) default_ = "error";
		if(!data_) data_ = [];

		var error_message_ =  (input_.attr("data-"+key_+"-error") || 
			input_.attr("data-error") ||
			input_el_.validationMessage ||
			default_ || 'error');

		for(var index_=0;index_ < data_.length; ++index_){
			var regex_ = new RegExp("\\{"+index_+"\\}");
			error_message_ = error_message_.replace(regex_, array_[index_]); 
		}
		return error_message_;
	});
	pb_validator.prototype.show_errors = (function(form_group_){
		this.clear_errors(form_group_);

		var target_inputs_ = this._inputs.filter(function(){
			return $(this).closest(".form-group").is(form_group_);
		});

		var module_ = this;
		var first_error_data_ = null;
		var first_error_message_ = null;

		target_inputs_.each(function(){
			var input_ = $(this);
			var error_data_ = module_._errors[input_.attr("name")];

			if(error_data_ && Object.keys(error_data_).length > 0){

				if(!first_error_data_){
					first_error_data_ = error_data_;	
				}

				input_.toggleClass("has-error", true);
				
				return false;
			}


		});

		if(first_error_data_ && Object.keys(first_error_data_).length > 0){
			$.each(first_error_data_, function(){
				first_error_message_ = this;
				return false;
			});
		}


		if(first_error_message_){
			var error_block_ = form_group_.find(".help-block.with-errors");

			var error_message_html_ = this._options['error_label_template'];

			var regex_ = new RegExp("\\{message\\}");
			error_message_html_ = error_message_html_.replace(regex_, first_error_message_); 
			error_block_.html(error_message_html_);

			form_group_.toggleClass("has-error", true);
		}
		
	});
	pb_validator.prototype.clear_errors = (function(form_group_){
		if(!form_group_){
			var module_ = this;
			this._target.find(".form-group").each(function(){
				module_.clear_errors($(this));
			});
			return;
		}

		var error_block_ = form_group_.find(".help-block.with-errors").empty();
			form_group_.toggleClass("has-error", false);

		var target_inputs_ = this._inputs.filter(function(){
			return $(this).closest(".form-group").is(form_group_);
		});
		target_inputs_.toggleClass("has-error", false);
	});

	pb_validator.prototype.toggle_submit = (function(){
		///.prop("disabled", !this.is_valid())
		this._target.find("[type='submit']").toggleClass("disabled", !this.is_valid());
	});
	
	pb_validator.DEFAULTS = {
		delay: 200,
		input_selector : ':input:not([type="hidden"], [type="submit"], [type="reset"], button, .hidden)',
		error_label_template : '<div class="error-message">{message}</div>',
		focus : true,
		validators : {
			
			'common' : function(input_, callback_){ //common
				
				if(input_.attr("required")){
					var input_value_ = pb_validator.input_value(input_);
					if(!input_value_ || input_value_ === ""){
						callback_(false, pb_validator.error_message(input_, 'required', 'required'));
						return;
					}
				}
				
				var input_el_ = input_[0];

				if(input_el_.checkValidity){
					var result_ = (input_el_.checkValidity() || input_el_.validity.valid);
					var error_message_ = null;

					if(!result_){
						error_message_ = pb_validator.error_message(input_, 'native', 'error');
					}
					callback_(result_, error_message_);
				}else{
					callback_(true);
				}
			},
			'remote': function(input_, callback_) {
				var remote_url_ = input_.attr('data-remote');
				var input_val_ = pb_validator.input_value(input_);

				input_.toggleClass("remote-processing", true);

				var remote_data_ = {};
					remote_data_[input_.attr("name")] = input_val_;

				var jqxhr_ = $.get(input_.attr("data-remote"), remote_data_);

				jqxhr_.fail($.proxy(function(t_jqxhr_, textstatus_, error_){
					this['callback'](false, pb_validator.error_message(this['input'], 'remote', 'remote'));
				},{
					module : this,
					input : input_,
					callback : callback_
				}));

				jqxhr_.always($.proxy(function(){
					this.toggleClass("remote-processing", false);
				},input_));

				jqxhr_.done($.proxy(function(){
					this(true)
				},callback_));
				
			},
			'match': function(input_, callback_) {
				var target_ = $(input_.attr('data-match'));
				var result_ = (pb_validator.input_value(input_) === target_.val());
				
				if(result_){
					callback_(true);
				}else{
					var error_message_ = pb_validator.error_message(input_, 'match', 'not matched');
					callback_(false, error_message_);
				}
			},
			'lengthrange': function(input_, callback_) {
				var lengthrange_ = input_.attr('data-lengthrange');
					lengthrange_ = lengthrange_.split(",");

				var minlength_ = parseInt(lengthrange_[0]);
				var maxlength_ = parseInt(lengthrange_[1]);
					maxlength_ = isNaN(maxlength_) ? null : maxlength_;

				var input_value_ = pb_validator.input_value(input_);

				if(input_value_ < minlength_){
					var error_message_ = pb_validator.error_message(input_, 'lengthragne', 'lengthragne {0}-{1}', [minlength_, maxlength_]);
					callback_(false, error_message_);
					return;
				}

				if(maxlength_ && input_value_ > maxlength_){
					var error_message_ = pb_validator.error_message(input_, 'lengthragne', 'lengthragne {0}-{1}', [minlength_, maxlength_]);
					callback_(false, error_message_);
					return;
				}

				callback_(true);
			},
			'minlength': function(input_, callback_) {
				var minlength_ = parseInt(input_.attr('data-minlength'));
				var result_ = (pb_validator.input_value(input_).length >= minlength_);
				
				if(result_){
					callback_(true);
				}else{
					var error_message_ = pb_validator.error_message(input_, 'minlength', 'minlength {0}', [minlength_]);
					callback_(false, error_message_);
				}
			},
			'maxlength': function(input_, callback_) {
				var maxlength_ = parseInt(input_.attr('data-maxlength'));
				var result_ = (pb_validator.input_value(input_).length <= maxlength_);
				
				if(result_){
					callback_(true);
				}else{
					var error_message_ = pb_validator.error_message(input_, 'maxlength', 'maxlength {0}', [minlength_]);
					callback_(false, error_message_);
				}
			},
			'pattern': function(input_, callback_) {
				var pattern_str_ = input_.attr('data-pattern');
				var regex_ = null;

				if(/.*\/([gimy]*)$/.test(pattern_str_)){
					var flags_ = pattern_str_.replace(/.*\/([gimy]*)$/, '$1');
					var pattern_ = pattern_str_.replace(new RegExp('^/(.*?)/'+flags_+'$'), '$1');
					regex_ = new RegExp(pattern_, flags_);
				}else{
					regex_ = new RegExp(pattern_str_);
				}

				var pattern_ = pattern_str_.replace(new RegExp('^/(.*?)/'+flags_+'$'), '$1');
			
				var result_ = regex_.test(pb_validator.input_value(input_));
				
				if(result_){
					callback_(true);
				}else{
					var error_message_ = pb_validator.error_message(input_, 'pattern', 'pattern {0}');
					callback_(false, error_message_);
				}
			}
		},
		common_validators : ["common"],
	};



	$.fn.validator = (function(options_){
		var module_ = this.data('pb-validator-module');
		if(module_){
			if(typeof options_ === 'string') return module_[options_]();
			return module_;
		};
		return new pb_validator(this, options_);
	});

	$.fn.validator.Constructor = pb_validator;

})(jQuery);