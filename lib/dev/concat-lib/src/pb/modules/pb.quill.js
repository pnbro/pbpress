jQuery(function($){
		
	PB.editor = $.extend(PB.editor, {
		toolbar_defaults : [
			['bold', 'italic', 'underline', 'strike'],
			['blockquote', 'code-block'],

			[{'list': 'ordered'}, { 'list': 'bullet'}],
			[{'script': 'sub'}, { 'script': 'super'}],
			[{'indent': '-1'}, { 'indent': '+1'}],

			[{'size': ['small', false, 'large', 'huge']}],
			[{'header': [1, 2, 3, 4, 5, 6, false]}],

			[{'color': []}, { 'background': []}],
			[{'font': []}],
			[{'align': []}],

			['clean']
		]
	});



});