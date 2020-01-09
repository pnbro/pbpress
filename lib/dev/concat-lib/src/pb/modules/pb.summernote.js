jQuery(function($){
	$.fn.init_summernote_for_pb = (function(options_){
		options_ = $.extend({}, options_);

		var placeholder_ = options_['placeholder'] || this.attr("placeholder");
		
		options_ = $.extend(options_, {
			placeholder: placeholder_,
			height: 200,
			dialogsInBody: true,
			disableDragAndDrop: true,
			toolbar : [
				['font', ['style', 'fontsize', 'bold', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
				['color', ['color']],
				['insert', ['link', 'pbimage', 'video']],
				['para', ['ul', 'ol', 'paragraph']],
				['height', ['height']],
				['misc', ['codeview', 'fullscreen']]
			]
		});
		return this.summernote(options_);
	});
});