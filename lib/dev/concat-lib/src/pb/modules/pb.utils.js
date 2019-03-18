(function($){

	var NON_CHAR_KEYCODE = window.NON_CHAR_KEYCODE = {
		 BACKSPACE : 8
		,TAB : 9
		,ENTER : 13
		,SHIFT : 16
		,CTRL : 17
		,ALT : 18
		,PAUSE : 19
		,CAPSLOCK : 20
		,ESCAPE : 27
		,PAGEUP : 33
		,PAGEDOWN : 34
		,END : 35
		,HOME : 36
		,LEFT : 37
		,UP : 38
		,RIGHT : 39
		,DOWN : 40
		,INSERT : 45
		,DELETE : 46
	}

	window.is_esc_key = (function(key_code_a_){
		return (NON_CHAR_KEYCODE.ESCAPE === key_code_a_);
	});

	window.is_enter_key = (function(key_code_a_){
		return (NON_CHAR_KEYCODE.ENTER === key_code_a_);
	});

	window.is_whitespace_key = (function(key_code_a_){
		return (NON_CHAR_KEYCODE.ENTER === key_code_a_ || NON_CHAR_KEYCODE.TAB === key_code_a_);
	});

	window.is_null = (function(target_){
		return (target_ === undefined || target_ === null || target_ ==="");
	});


	$.fn.serialize_object = (function(map_data_, key_array_convert_){
		key_array_convert_ = (key_array_convert_ === undefined ? false : key_array_convert_);
		var result_ = {};
		var pass_map_data_ = (map_data_ === undefined || map_data_ === null);
		var that_ = this;
		var array_ = this.serializeArray();
		$.each(array_, function(){
			var converted_name_ = this.name;
			var target_input_ = that_.find('[name="'+converted_name_+'"]');

			if(!pass_map_data_){
				if(map_data_[this.name] !== undefined && map_data_[this.name] !== null)
					converted_name_ = map_data_[this.name];
				else return true;
			}
				
			if(key_array_convert_ === true && converted_name_.indexOf("[]") >= 0){
				converted_name_ = converted_name_.replace("[]","");
			}

			if(result_[converted_name_] !== undefined){
				if(!result_[converted_name_].push){
					result_[converted_name_] = [result_[converted_name_]];
				}

				result_[converted_name_].push(PB.apply_data_filters('pb-serialize-object', this.value, target_input_) || '');
			}else result_[converted_name_] = (PB.apply_data_filters('pb-serialize-object', this.value, target_input_) || '');
		});

		return result_;
	});

	$.fn.update_form = (function(data_){

		var inputs_ = this.find(":input");

		$.each(inputs_, function(){
			var input_ = $(this);
			var value_ = data_[input_.attr("name")];

			if(value_ !== undefined){
				input_.val(value_);
			}
		});

	});

	$.fn.sval = (function(value_){
		if(value_ !== undefined){
			var options_ = $(this).children("option");
			options_.each(function(){
				if($(this).val() === value_) $(this).attr("selected",true);
				else $(this).removeAttr("selected");
			});
		}

		return $(this).find("option:selected").val();
	});

	$.fn.checkbox_mass_toggle = (function(){
		var checkboxes_ = this.data("checkbox-mass-toggle").split(",");

		this.data("mass-checkboxes", checkboxes_);

		this.on("change", function(){
			var checkboxes_ = $(this).data("mass-checkboxes");			
			var checked_ = $(this).is(":checked");

			for(var index_=0;index_<checkboxes_.length;++index_){
				var target_checkbox_ = $(checkboxes_[index_]);
					target_checkbox_.prop("checked", checked_);
					target_checkbox_.change();
			}
		});


		for(var index_=0;index_<checkboxes_.length;++index_){
			var target_checkbox_ = $(checkboxes_[index_]);
				target_checkbox_.data("root-checkbox", $(this));

			target_checkbox_.change(function(){
				var temp_ = $(this);
				var root_checkbox_ = temp_.data("root-checkbox");
				var tcheckboxes_ = root_checkbox_.data("mass-checkboxes");

				var result_ = true;

				for(var tindex_=0;tindex_<tcheckboxes_.length;++tindex_){

					var tmp_checkbox_ = $(tcheckboxes_[tindex_]);

					$.each(tmp_checkbox_, function(){
						if(!$(this).is(":checked")){
							result_ = false;
							return false;
						}
					});

					if(!result_) break;
				}

				root_checkbox_.prop("checked", result_);

			});
		}
	});

	$.fn.indicator = (function(bool_, options_){
		var indicator_message_ = PB.indicator_message();
		
		this.toggleClass('block-ui-in', bool_);
		if(bool_){
			this.block($.extend({
				message : indicator_message_
			},options_));
		}else{
			this.unblock(options_);
		}
	});

	$.fn.tap_event = (function(callback_){
		return this.on('mousedown', callback_);
	});

	$(document).ready(function(){
		var toggle_buttons_ = $("[data-checkbox-mass-toggle]");
		if(toggle_buttons_.length > 0){
			toggle_buttons_.each(function(){
				$(this).checkbox_mass_toggle();
			});
		}
	});
	
})(jQuery);