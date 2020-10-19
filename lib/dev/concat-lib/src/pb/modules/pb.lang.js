(function($){
	PB.lang = $.extend(PB.file, {
		default_domain : null,
		__ : function(text_, domain_){
			if(!domain_) domain_ = this.default_domain;
			if(!PBLANG[domain_] || !PBLANG[domain_][text_]) return text_;
			return PBLANG[domain_][text_];
		}
	});

	window.__ = function(text_, domain_){
		return PB.lang.__(text_, domain_);
	}
	
})(jQuery);