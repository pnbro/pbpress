(function($){
	PB.lang = $.extend(PB.file, {
		default_domain : PBVAR['locale_domain'],
		__ : function(text_, domain_){
			if(!domain_) domain_ = this.default_domain;
			if(!window.PBLANG || !PBLANG[domain_] || !PBLANG[domain_][text_]) return text_;
			return PBLANG[domain_][text_];
		},
		__f : function(text_, array_, domain_){
			var text_ = this.__(text_, domain_);

			var array_length_ = array_.length;
			for(array_index_=0; array_index_ < array_length_; ++array_index_){
				var ptext_ = array_[array_index_];
				text_ = text_.replace("%" + (array_index_+1) + "s", ptext_);
			}

			return text_;
		}
	});

	window.__ = function(text_, domain_){
		return PB.lang.__(text_, domain_);
	}
	window.__f = function(text_, array_, domain_){
		return PB.lang.__f(text_, array_, domain_);
	}
	
})(jQuery);