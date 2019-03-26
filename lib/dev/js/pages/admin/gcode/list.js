jQuery(document).ready(function(){
	var splitted_view_el_ = $("#pb-gcode-splitted-view");
	splitted_view_el_.pb_splitted_view({
		'master-info-load-action' : "pb-admin-gcode-load"
	});

	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-gcode-load",
		insert_action : "pb-admin-gcode-insert",
		update_action : "pb-admin-gcode-update",
		delete_action : "pb-admin-gcode-delete",

		before_insert : function(target_data_, callback_){

			PB.confirm({
				title : "추가확인",
				content : "공통코드를 추가하시겠습니까?",
				button1 : "추가하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));

		},
		before_update : function(target_data_, callback_){
			callback_(true);
		},
		before_delete : function(target_data_, callback_){
			PB.confirm({
				title : "삭제확인",
				content : "공통코드를 삭제하시겠습니까?",
				button1 : "삭제하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));
		},

		after_empty_opened : function(form_el_){
			//추가팝업이 열렸을 때 이벤트, 
		},
		after_loaded : function(form_el_, form_data_){
			// form_el_.find("[name='USE_YN'][value='"+form_data_['USE_YN']+"']").prop("checked", true);
		},
		after_inserted : function(){
			PB.alert({
				title : "추가완료",
				content : "추가가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_updated : function(){
			PB.alert({
				title : "수정완료",
				content : "수정이 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_deleted : function(){
			PB.alert({
				title : "삭제완료",
				content : "삭제가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-cond-form").submit(); //리스트테이블 재검색
			});
		}
	});

	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-gcode-dtl-load",
		insert_action : "pb-admin-gcode-dtl-insert",
		update_action : "pb-admin-gcode-dtl-update",
		delete_action : "pb-admin-gcode-dtl-delete",

		before_insert : function(target_data_, callback_){

			PB.confirm({
				title : "추가확인",
				content : "상세코드를 추가하시겠습니까?",
				button1 : "추가하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));

		},
		before_update : function(target_data_, callback_){
			callback_(true);
		},
		before_delete : function(target_data_, callback_){
			PB.confirm({
				title : "삭제확인",
				content : "상세코드를 삭제하시겠습니까?",
				button1 : "삭제하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));
		},

		after_empty_opened : function(form_el_){
			//추가팝업이 열렸을 때 이벤트, 
			form_el_.find("[name='code_id']").val(splitted_view_el_.pb_splitted_view().current_master_id());
		},
		after_loaded : function(form_el_, form_data_){
			// form_el_.find("[name='USE_YN'][value='"+form_data_['USE_YN']+"']").prop("checked", true);
			
		},
		after_inserted : function(){
			PB.alert({
				title : "추가완료",
				content : "추가가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-dtl-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_updated : function(){
			PB.alert({
				title : "수정완료",
				content : "수정이 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-dtl-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_deleted : function(){
			PB.alert({
				title : "삭제완료",
				content : "삭제가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-dtl-cond-form").submit(); //리스트테이블 재검색
			});
		}
	});
});

function _pb_gcode_add(){
	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal();
		code_edit_form_module_.open_for_insert();
}
function _pb_gcode_edit(code_id_){
	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal();
		code_edit_form_module_.open_for_update(code_id_);
}
function _pb_gcode_remove(code_id_){
	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal();
		code_edit_form_module_.do_delete(code_id_);
}

function _pb_gcode_dtl_add(){
	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal();
		code_dtl_edit_form_module_.open_for_insert();
}
function _pb_gcode_dtl_edit(code_id_,code_did_){
	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal();
		code_dtl_edit_form_module_.open_for_update([code_id_,code_did_]);
}
function _pb_gcode_dtl_remove(code_id_,code_did_){
	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal();	
		code_dtl_edit_form_module_.do_delete([code_id_,code_did_]);
}