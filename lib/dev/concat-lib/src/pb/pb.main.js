(function($){

	window.$ = jQuery;

	PB = $.extend(PB, {
		ajax : function(options_, callback_, indicator_){
			if(indicator_){
				PB.indicator(true, $.proxy(function(){
					$.ajax(options_, $.proxy(function(response_){
						PB.indicator(false);
						this(response_); 
					}, this));		
				}, callback_));		
			}else{
				$.ajax(options_, callback_);		
			}
		}
	});

	return PB;

})(jQuery);