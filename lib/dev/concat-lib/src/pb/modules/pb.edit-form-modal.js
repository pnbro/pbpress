jQuery(function(){

	var _pb_edit_form_modal = (function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			'load_action' : null,
			'insert_action' : null,
			'update_action' : null,
			'delete_action' : null,

			'btn_insert_text' : "추가하기",
			'btn_update_text' : "수정하기",
			'btn_delete_text' : "삭제",
			'btn_cancel_text' : "취소",

			'title_text_for_insert' : "추가하기",
			'title_text_for_update' : "수정하기",

			'before_insert' : function(target_data_, callback_){callback_(true)},
			'before_update' : function(target_data_, callback_){callback_(true)},
			'before_delete' : function(target_data_, callback_){callback_(true)},

			'after_opened' : $.noop,
			'after_loaded' : $.noop,

			'after_inserted' : $.noop,
			'after_updated' : $.noop,
			'after_deleted' : $.noop,
		
		}, options_);


		var modal_html_ = '<div class="modal fade" tabindex="-1" role="dialog">'+
			'<div class="modal-dialog" role="document">'+
				'<div class="modal-content">'+
					'<div class="modal-header">'+
						'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
						'<h4 class="modal-title"></h4>'+
					'</div>'+
					'<div class="modal-body"></div>'+
					'<div class="modal-footer"></div>'+
				'</div>'+
			'</div>'+
		'</div>';

		this._modal = $(modal_html_);
		this._modal.appendTo("body");

		this._modal_title = this._modal.find(".modal-header > .modal-title");
		this._modal_body = this._modal.find(".modal-body");
		this._modal_footer = this._modal.find(".modal-footer");

		this._target.detach();
		this._target.appendTo(this._modal_body);

		this._btn_submit = $("<button type='button' class='btn btn-primary'>"+this._options['btn_insert_text']+"</button>");
		this._btn_delete = $("<button type='button' class='btn btn-dark'>"+this._options['btn_delete_text']+"</button>");
		this._btn_cancel = $("<button type='button' class='btn btn-default' data-dismiss='modal'>"+this._options['btn_cancel_text']+"</button>");

		this._modal_footer.append(this._btn_submit, this._btn_delete, this._btn_cancel);
		this._current_work_type = "insert";
		this._current_key = null;

		this._target.submit_handler($.proxy(function(){
			var target_func_ = (this._current_work_type === "insert" ? this.do_insert : this.do_update);
			target_func_.apply(this);
		}, this));

		this._btn_submit.click($.proxy(function(){
			this._target.submit();	
			return false;
		},this));

		this._btn_delete.click($.proxy(function(){
			this.do_delete();
		},this));

		this._target.data("pb-edit-form-modal", this);
	});
	_pb_edit_form_modal.prototype.data_to_tag = (function(data_){
		this._target.data_to_tag(data_);
	});

	_pb_edit_form_modal.prototype.open_for_insert = (function(){
		this._current_work_type = "insert";
		this._current_key = null;

		this._modal_title.text(this._options['title_text_for_insert']);
		this._btn_submit.text(this._options['btn_insert_text']);
		this._btn_delete.toggle(false);

		this._target[0].reset();

		this._modal.modal({
			keyboard : false,
			backdrop : "static",
			show : true
		});
		this._options['after_empty_opened'].apply(this, [this._target]);
	});

	_pb_edit_form_modal.prototype.open_for_update = (function(key_){
		this._current_work_type = "update";
		this._current_key = key_;

		this._modal_title.text(this._options['title_text_for_update']);
		this._btn_submit.text(this._options['btn_update_text']);
		this._btn_delete.toggle((this._options['delete_action'] ? true : false));

		this._target[0].reset();

		PB.post(this._options['load_action'], {
			key : this._current_key,
		}, $.proxy(function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "자료를 불러오는 중, 에러가 발생했습니다.",
				});
				return;
			}

			var target_ = this._target;
			var form_data_ = response_json_.results;

			$.each(form_data_, function(column_name_, column_value_){
				var target_input_ = target_.find(":input[name='"+column_name_+"']");

				if(target_input_.attr("type") === "checkbox" || target_input_.attr("type") === "radio"){
					target_input_.prop("checked", false);
					target_input_.filter(function(){
						return ($(this).val() === column_value_);
					}).prop("checked", true);
				}else{
					target_input_.val(column_value_);
				}
			});

			this._options['after_loaded'].apply(this, [this._target, form_data_]);
			this._modal.modal({
				keyboard : false,
				backdrop : "static",
				show : true
			});

		}, this), true);

	});

	_pb_edit_form_modal.prototype.do_insert = (function(){
		this._options['before_insert'].apply(this, [this._target.serialize_object(),$.proxy(function(result_){
			if(!result_) return;

			var target_data_ = this._target.serialize_object();

			PB.post(this._options['insert_action'], {
				target_data : target_data_
			}, $.proxy(function(result_, response_json_){
				if(!result_ || response_json_.success !== true){
					PB.alert({
						title : response_json_.error_title || "에러발생",
						content : response_json_.error_message || "자료 추가 중, 에러가 발생했습니다.",
					});
					return;
				}

				this._modal.modal("hide");
				this._options['after_inserted'].apply(this, [this._target]);
			
			}, this), true);


		},this)]);
	});

	_pb_edit_form_modal.prototype.do_update = (function(){
		if(!this._current_key) return;

		this._options['before_update'].apply(this, [this._target.serialize_object(),$.proxy(function(result_){
			if(!result_) return;

			var target_data_ = this._target.serialize_object();

			PB.post(this._options['update_action'], {
				key : this._current_key,
				target_data : target_data_
			}, $.proxy(function(result_, response_json_){
				if(!result_ || response_json_.success !== true){
					PB.alert({
						title : response_json_.error_title || "에러발생",
						content : response_json_.error_message || "자료 수정 중, 에러가 발생했습니다.",
					});
					return;
				}

				this._modal.modal("hide");
				this._options['after_updated'].apply(this, [this._target]);

				
			}, this), true);
			
		},this)]);

	});

	_pb_edit_form_modal.prototype.do_delete = (function(key_){
		if(key_) this._current_key = key_;
		if(!this._current_key) return;

		this._options['before_delete'].apply(this, [this._target.serialize_object(),$.proxy(function(result_){
			if(!result_) return;

			var target_data_ = this._target.serialize_object();

			PB.post(this._options['delete_action'], {
				key : this._current_key,
				target_data : target_data_
			}, $.proxy(function(result_, response_json_){
				if(!result_ || response_json_.success !== true){
					PB.alert({
						title : response_json_.error_title || "에러발생",
						content : response_json_.error_message || "자료 삭제 중, 에러가 발생했습니다.",
					});
					return;
				}

				if(this._modal.hasClass("in")){
					this._modal.modal("hide");l
				}
				this._options['after_deleted'].apply(this, [this._target]);

				
			}, this), true);
			
		},this)]);
	});

	$.fn.pb_edit_form_modal = (function(options_){
		var module_ = $(this).data("pb-edit-form-modal");
		if(module_) return module_;
		return new _pb_edit_form_modal(this, options_);
	});

});