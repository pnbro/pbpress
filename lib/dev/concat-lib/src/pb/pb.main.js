(function($){

	window.$ = jQuery;

	PB = $.extend(PB, {
		modal : function(options_, callback_){
			options_ = $.extend({
				show : true,
				classes : "pb-common-popup",
				closebtn : true,
				escbtn : true,
				backdrop : 'static',
				callback_input : false,
				
				button1ID : null,
				button2ID : null,
				button3ID : null,

				button1Classes : "btn btn-primary",
				button2Classes : "btn btn-default",
				button3Classes : "btn btn-default",
				appendTo : null
			},options_);

			var unique_id_ = this.random_string(10,"abcdefg1234567890");
			var has_title_ = (options_.title !== undefined && options_.title !== null);

			options_._unique_id = unique_id_;

			var html_ = '<div class="modal fade '+(options_.classes)+' '+(has_title_ ? '' : 'notitle')+'" id="pb-common-modal-'+unique_id_+'" tabindex="-1" role="dialog" aria-labelledby="pb-common-modal-label" data-unique-id="'+unique_id_+'">' +
				'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">';

			if(options_.closebtn){
				html_ +=	'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i aria-hidden="true" class="fa fa-times"></i></button>';
			}

			html_ += '<div class="modal-body">';

			html_ += '<div class="content-wrap">';
			html_ +=	'<h4 class="title">'+options_.title+'</h4>';
			if(options_.content){
				html_ += '<div class="content">';
				html_ += options_.content;
				html_ += '</div>';
			}
			html_ += '</div>';

			html_ += '</div>';

			if(!options_.button1Classes.indexOf("btn-")){
				options_.button1Classes += " btn-primary ";
			}

			if(!options_.button2Classes.indexOf("btn-")){
				options_.button2Classes += " btn-default ";
			}
			
			if(options_.button1 !== undefined && options_.button1 !== null && options_.button1 !== ""){
				
				html_ +=	'<div class="modal-footer">';

				var _temp_func_make_button2 = function(){
					if(options_.button2 !== undefined && options_.button2 !== null && options_.button2 !== ""){

						var button_html_ = '<button type="button" class="button3 '+options_.button2Classes+'  ';
							
						if(options_.button2ID){
							button_html_ += ' id="'+options_.button2ID+'" ';	
						}
						
						button_html_ += ' ">'+options_.button2+'</button>';
						return button_html_;

					}else return "";
				}

				var _temp_func_make_button3 = function(){
					if(options_.button3 !== undefined && options_.button3 !== null && options_.button3 !== ""){

						var button_html_ = '<button type="button" class="button3 '+options_.button3Classes+'  ';
						if(options_.button3ID){
							button_html_ += ' id="'+options_.button3ID+'" ';	
						}
						
						button_html_ += ' ">'+options_.button3+'</button>';


						return button_html_;

					}else return "";
				}
				
			
				html_ += '<button type="button" class="button1 '+options_.button1Classes+' " ';

				if(options_.button1ID){

					html_ += ' id="'+options_.button1ID+'" ';
				}

				html_ += '>'+options_.button1+'</button>';

				html_ += _temp_func_make_button2();
				html_ += _temp_func_make_button3();
				html_ += '</div>';
			}

			html_ += '</div>' +
				'</div>' +
			'</div>';


			var modal_ = $(html_);
			$("body").append(modal_);

			modal_.modal({
				keyboard : options_.escbtn,
				backdrop : options_.backdrop,
				show : options_.show
			});



			if(options_.show && options_.appendTo !== null){
				var appendToElement_ = $(options_.appendTo);
				if(appendToElement_.length > 0){
					$("body").removeClass("modal-open");
					$('.modal-backdrop').appendTo(appendToElement_); 
					modal_.appendTo(appendToElement_); 
					modal_.addClass('appendto-element');
					$('.modal-backdrop').addClass("appendto-element");
					$('.modal-backdrop').off('focusin.bs.modal');

					$('.modal-backdrop').one('bsTransitionEnd', function(){
						$(document).off('focusin.bs.modal');
					});
					
				}
			}
			
			// modal_.modal("show");

			var button1_ = modal_.find(".modal-footer .button1");
			var button2_ = modal_.find(".modal-footer .button2");
			var button3_ = modal_.find(".modal-footer .button3");

			button1_.data("pb-modal-options", options_);
			button1_.data("pb-modal-callback", callback_);
			button1_.click(function(){
				var options_ = $(this).data("pb-modal-options");
				var modal_ = $("#pb-common-modal-"+options_._unique_id);
				var callback_ = $(this).data("pb-modal-callback");

				var inputs_ = {};
				if(options_.callback_input){
					var input_els_ = modal_.find('.modal-body :input');

					if(input_els_.length > 0){
						input_els_.each(function(){
							inputs_[$(this).attr('name')] = $(this).val();
						});
					}
				}

				modal_.modal("hide");
				if(callback_ !== undefined) callback_(true, inputs_);
			});

			if(button2_.length > 0){
				button2_.data("pb-modal-options", options_);
				button2_.data("pb-modal-callback", callback_);
				button2_.click(function(){
					var options_ = $(this).data("pb-modal-options");
					var modal_ = $("#pb-common-modal-"+options_._unique_id);
					var callback_ = $(this).data("pb-modal-callback");

					modal_.modal("hide");
					if(callback_ !== undefined) callback_(false, 1);
				});
			}

			if(button3_.length > 0){
				button3_.data("pb-modal-options", options_);
				button3_.data("pb-modal-callback", callback_);
				button3_.click(function(){
					var options_ = $(this).data("pb-modal-options");
					var modal_ = $("#pb-common-modal-"+options_._unique_id);
					var callback_ = $(this).data("pb-modal-callback");

					modal_.modal("hide");
					if(callback_ !== undefined) callback_(false, 2);
				});
			}

			modal_.on("hidden.bs.modal", function(){
				$(this).remove();
			});

			return modal_;
		},confirm : function(options_, callback_){
			return this.modal($.extend({
				closebtn : false,
				escbtn : false,
				button1 : "확인",
				button2 : "취소"
			},options_), callback_);
		},
		alert : function(options_, callback_){
			return this.modal($.extend({
				closebtn : false,
				escbtn : false,
				button1 : "확인"
			},options_), callback_);
		},

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

	$.fn.scrollFocus = (function(animate_){
		animate_ = (animate_ === undefined ? 200 : 0);
		var offset_ = this.offset();
		$("body").animate({scrollTop : offset_['top']}, animate_);
	});


	$(document).ready(function(){
		moment.locale('en');
	});


	$(document).on('hidden.bs.modal', '.modal', function () {
		$('.modal:visible').length && $(document.body).addClass('modal-open');
	});

	$(document).on('show.bs.modal', '.modal', function () {
		var zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function() {
			$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
		}, 0);
	});

	pb_validator.DEFAULTS.validators = $.extend(pb_validator.DEFAULTS.validators, {
		'idtype' : function(input_, callback_){
			var input_value_ = pb_validator.input_value(input_);
			if(!(/^[a-zA-Z0-9]{8,15}$/.test(input_value_))){
				callback_(false, pb_validator.error_message(input_, 'idtype', 'idtype'));
				return;
			}
			var check1_ = !(/[0-9]{1,}/.test(input_value_));
			var check2_ = !(/[a-zA-Z]{1,}/.test(input_value_));

			if(check1_ || check2_){
				callback_(false, pb_validator.error_message(input_, 'idtype', 'idtype'));
				return;
			}

			callback_(true);
		},
		"passwordtype" : function(input_, callback_){
			var input_value_ = pb_validator.input_value(input_);
			if(!(/^[a-zA-Z0-9!@#$%^*+=\-\&]{8,15}$/.test(input_value_))){
				callback_(false, pb_validator.error_message(input_, 'passwordtype', 'passwordtype'));
				return;
			}
			var check1_ = !(/[0-9]{1,}/.test(input_value_));
			var check2_ = !(/[a-zA-Z]{1,}/.test(input_value_));
			var check3_ = !(/[!@#$%^*+=\-\&]{1,}/.test(input_value_));		

			if(check1_ || check2_ || check3_){
				callback_(false, pb_validator.error_message(input_, 'passwordtype', 'passwordtype'));
				return;
			}
			callback_(true);
		},
		"password" : function(input_, callback_){
			this._options['validators']['passwordtype'].apply(this, [input_, callback_]);
		},
	});

	$.fn.submit_handler = (function(success_, fail_){
		this.data('form-callback-success', success_) || $.fn.noop;
		this.data('form-callback-fail', fail_) || $.fn.noop;

		this.on('submit', function(event_){
			var target_ = $(this);
			var callback_success_ = target_.data('form-callback-success')  || $.noop;
			var callback_fail_ = target_.data('form-callback-fail') || $.noop;

			if(event_.isDefaultPrevented()){
				callback_fail_.apply(this, [target_]);
				return;
			}

			return callback_success_.apply(this, [target_]) || false;
		});
	});
	
	return PB;

})(jQuery);