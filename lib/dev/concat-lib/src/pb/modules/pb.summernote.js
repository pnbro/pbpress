jQuery(function($){
	$.fn.init_summernote_for_pb = (function(options_){
		options_ = $.extend(options_, {
			dialogsInBody: true,
			toolbar : [
				['font', ['style', 'fontsize', 'bold', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
				['color', ['color']],
				['insert', ['link', 'pbimage']],
				['para', ['ul', 'ol', 'paragraph']],
				['height', ['height']]
			]
		});
		return this.summernote(options_);
	});
});