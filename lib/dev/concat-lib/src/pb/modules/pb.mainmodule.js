(function($){
	
	var PB = window.PB = {
		nl2br : function(str_){
			return str_.replace(/\n/g, "<br />");  
		},
		make_currency : function(value_){
			$result_ = String(Math.abs(value_)).replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');

			if(value_ < 0){
				$result_ = "-"+$result_;
			}

			return $result_;
		},make_k_number_format : function(value_){
			  return (value_ > 999 ? (value_/1000).toFixed(1) + 'k' : value_);
		},
		random_string : function(length_, possible_){
			var text_ = "";
			var possible_ = (possible_ !== undefined ? possible_ : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");

			for(var i=0; i < length_; i++)
				text_ += possible_.charAt(Math.floor(Math.random() * possible_.length));

			return text_;
		},
		lpad : function(str_, char_, length_){
			if(! str_|| !char_|| str_.length >= length_) {
				return str_;
			}

			var max_ = (length_ - str_.length) / char_.length;

			for(var index_=0;index_<max_; ++index_){
				str_ = char_ + str_;
			}

			return str_;
		},
		rpad : function(str_, char_, length_){
			if(! str_|| !char_|| str_.length >= length_) {
				return str_;
			}

			var max_ = (length_ - str_.length) / char_.length;

			for(var index_=0;index_<max_; ++index_){
				str_ += char_;
			}

			return str_;
		},
		make_options : function(array_, value_column_, title_column_){
			var html_ = '';
			for(var index_=0;index_<array_.length; ++index_){
				html_ += '<option value="'+array_[index_][value_column_]+'">'+array_[index_][title_column_]+'</option>';
			}

			return html_;
		},
		json_equals : function(a_, b_, sort_array_){
			var sort_func_ = function(object_){
				if(sort_array_ === true && Array.isArray(object_)) {
					return object_.sort();
				}else if(typeof object_ !== "object" || object_ === null) {
					return object_;
				}

				return Object.keys(object_).sort().map(function(key_) {
					return {
						key: key_,
						value: sort_func_(object_[key_])
					};
				});
			};
			
			return JSON.stringify(sort_func_(a_)) === JSON.stringify(sort_func_(b_));
		},
		_data_filters : {},
		add_data_filter : function(key_, func_){
			if(this._data_filters[key_] === undefined || this._data_filters[key_] === null){
				this._data_filters[key_] = [];
			}
			this._data_filters[key_].push(func_);
		},
		apply_data_filters : function(key_, params_, add_){
			if(this._data_filters[key_] !== undefined && this._data_filters[key_] !== null){
				var filter_count_ = this._data_filters[key_].length;
				for(var index_=0;index_<filter_count_;++index_){
					params_ = this._data_filters[key_][index_](params_, add_);	
				}
			}
			return params_;
		},
		_data_actions : {},
		add_data_action : function(key_, func_){
			if(this._data_actions[key_] === undefined || this._data_actions[key_] === null){
				this._data_actions[key_] = [];
			}
			this._data_actions[key_].push(func_);
		},
		do_data_action : function(key_, params_){
			if(this._data_actions[key_] !== undefined && this._data_actions[key_] !== null){
				var filter_count_ = this._data_actions[key_].length;
				for(var index_=0;index_<filter_count_;++index_){
					this._data_actions[key_][index_](params_);	
				}
			}
		},
		post : function(action_, data_, callback_, options_){
			options_ = $.extend({
				type: "POST",
				url: PB.append_url(PBVAR['ajax_url'],action_),
				data : $.extend(true,{},data_),
				success : $.proxy(function(response_text_){
					var result_ = true;
					var response_json_ = null;
					if($.type(response_text_) === "string"){
						try{
							response_json_ = JSON.parse(response_text_);
						}catch(ex_){
							result_ = false;
						}
					}else{
						response_json_ = response_text_;
					}
						
					this(result_,response_json_, response_text_);
				},callback_),error : $.proxy(function(xhr_, status_, thrown_){
					if(status_ === "abort") return;
					this(false, status_, thrown_);
				},callback_)
			},options_);
			
			return $.ajax(options_);
		},
		
		_post_url : function(url_, data_, callback_, options_){
			options_ = $.extend({
				type: "POST",
				url: url_,
				data : $.extend(true,{},data_),
				success : $.proxy(function(response_text_){
					var result_ = true;

					if($.type(response_text_) === "string"){
						try{
							response_text_ = JSON.parse(response_text_);
						}catch(ex_){
							result_ = false;
						}
					}
						
					this(result_,response_text_, response_text_);
				}, callback_),error : $.proxy(function(xhr_, status_, thrown_){
					if(status_ === "abort") return;
					this(false, status_, thrown_);
				}, callback_)
			},options_);
			
			return $.ajax(options_);
		},
		post_url : function(url_, data_,callback_,block_, options_){
			if(block_ !== undefined && block_ === true){
				this.indicator(true, function(){
					PB._post_url.apply(PB, [url_, data_, function(result_, response_text_, cause_){
						PB.indicator(false);
						callback_(result_, response_text_, cause_);
					}, options_]);
				});
			}else{
				return PB._post_url.apply(PB,[url_, data_, callback_, options_]);
			}
		},

		make_url : function(url_, parameters_){
			parameters_ = $.extend({},parameters_);

			var concat_char_ = "?";
			if(url_.indexOf(concat_char_) >= 0)
				concat_char_ = "&";
			return url_+concat_char_+$.param(parameters_);
		},append_url : function(url_, append_path_){
			if(url_.lastIndexOf("/") != (url_.length -1)){
				url_ += "/";
			}

			return (url_ + append_path_);
		},modal : function(options_, callback_){
			console.error("please override me!");
			return;
		},confirm : function(options_, callback_){
			return this.modal($.extend({
				closebtn : false,
				escbtn : false,
				button1 : "확인",
				button2 : "취소"
			},options_), callback_);
		},alert : function(options_, callback_){
			return this.modal($.extend({
				closebtn : false,
				escbtn : false,
				button1 : "확인"
			},options_), callback_);
		},redirect : function(url_, params_){
			document.location = PB.make_url(url_, params_);
		},
		url_parameter : function(name_){
			return decodeURIComponent((new RegExp('[?|&]' + name_ + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
		},url_path : function(){

			var t_result_ = location.pathname.split("/")
			var result_ = [];

			for(var index_=0;index_<t_result_.length; ++index_){
				if(t_result_[index_] !== ""){
					result_.push(t_result_[index_]);
				}
			}

			return result_;
		},
		week_of_month : function(date) {
			var day = date.getDate()
            day += (date.getDay() == 0 ? 0 : 7 - date.getDay());
            return Math.ceil(parseFloat(day) / 7);
		},
		geolocation : function(callback_){
			if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(function(position_){
					var results_ = {
						"lat" : position_.coords.latitude,
						"lng" : position_.coords.longitude
					};

					callback_(results_);

				},function(){
					callback_(false, "failed");
				});
			}else{
				callback_(false, "not_supported");
			}
		},
		indicator_message : function(){
			return "<div class='pb-indicator-frame'>"+
				'<div class="lds-spin"><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>' +
			"</div>";
		},
		indicator : function(bool_, callback_){
			callback_ = callback_ !== undefined ? callback_ : $.noop;
			var body_el_ = $("body");
			if(bool_){

				if(body_el_.hasClass("block-ui-in")){
					callback_.apply(PB);
				}else{
					body_el_.toggleClass('block-ui-in', true);
					$.blockUI({
						message : PB.indicator_message(),
						onBlock : callback_
					});
				}
					
			}else{
				if(!body_el_.hasClass("block-ui-in")){
					callback_.apply(PB);
				}else{
					body_el_.toggleClass('block-ui-in', false);
					$.unblockUI({
						onUnblock : callback_
					});
				}

					
			}
		},
		classic_popup : function (url_, options_) {

			options_ = $.extend({
				'width' : 500,
				'height' : 400,
				'resize' : false,
				'title' : "untitled"
			},options_);

			var strResize_ = (options_['resize'] ? 'yes' : 'no');

			var strParam = 'width=' + options_['width'] + ',height=' + options_['height'] + ',resizable=' + strResize_,
				objWindow = window.open(url_, options_['title'], strParam);

			objWindow.focus();

			return objWindow;
		},unix_to_date : function(unix_, format_){
			var moment_ = moment.unix(unix_);
			return moment_.format(format_);
		},
		filebase_url : function(file_path_, upload_path_){
			file_path_ = file_path_ !== undefined && file_path_ !== null ? file_path_ : "";
			upload_path_ = upload_path_ !== undefined && upload_path_ !== null ? upload_path_ : "/";

			var filebase_url_ = PBVAR['filebase_url'];

			filebase_url_ = filebase_url_.replace(/\/$/, '');
			
			upload_path_ = upload_path_.replace(/^\//, '');
			upload_path_ = upload_path_.replace(/\/$/, '');

			file_path_ = file_path_.replace(/^\//, '');
			file_path_ = file_path_.replace(/\/$/, '');

			return filebase_url_+(upload_path_ === "" ? upload_path_ : "/"+upload_path_)+"/"+file_path_;

		}
	}

	$.fn.data_to_tag = (function(data_){
		this.find("[data-column]").each(function(){
			var column_el_ = $(this);
			var column_name_ = column_el_.attr("data-column");
			var is_html_ = column_el_.attr("data-column-html") === "Y";
		
			if(is_html_){
				column_el_.html(data_[column_name_]);
			}else{
				column_el_.text(data_[column_name_]);
			}
		});
	});

	window.pb_add_filter = (function(key_, func_){
		return PB.add_data_filter(key_, func_);
	});
	window.pb_apply_filters = (function(key_, params_, add_){
		return PB.apply_data_filters(key_, params_, add_);
	});
	window.add_data_action = (function(key_, func_){
		return PB.add_data_action(key_, func_);
	});
	window.do_data_action = (function(key_, params_){
		return PB.do_data_action(key_, params_);
	});

	return PB;

})(jQuery);