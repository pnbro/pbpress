jQuery(document).ready(function(){
	var splitted_view_el_ = $("#pb-authority-splitted-view");
	splitted_view_el_.pb_easy_splitted_view();

	var auth_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-authority-load",
		insert_action : "pb-admin-authority-insert",
		update_action : "pb-admin-authority-update",
		delete_action : "pb-admin-authority-delete",

		before_insert : function(target_data_, callback_){

			PB.confirm({
				title : "추가확인",
				content : "권한을 추가하시겠습니까?",
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
				content : "권한을 삭제하시겠습니까?",
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
				$("#pb-admin-authority-table").submit(); //리스트테이블 재검색
			});
		},
		after_updated : function(){
			PB.alert({
				title : "수정완료",
				content : "수정이 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-admin-authority-table").submit(); //리스트테이블 재검색
			});
		},
		after_deleted : function(){
			PB.alert({
				title : "삭제완료",
				content : "삭제가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-admin-authority-table").submit(); //리스트테이블 재검색
			});
		}
	});

});

function _pb_authority_add(){
	var level_edit_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal();
		level_edit_form_module_.open_for_insert();
}
function _pb_authority_edit(code_id_){
	var level_edit_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal();
		level_edit_form_module_.open_for_update(code_id_);
}
function _pb_authority_remove(code_id_){
	var level_edit_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal();
		level_edit_form_module_.do_delete(code_id_);
}

function pb_manage_authority_task_update(){
	var task_table_el_ = $("#pb-admin-authority-task-table");
	var all_use_yn_checkboxes_ = task_table_el_.find("[name='grant_yn']");

	all_use_yn_checkboxes_ = all_use_yn_checkboxes_.filter(function(){
		return !$(this).prop("disabled")
	});

	if(all_use_yn_checkboxes_.length <= 0){
		PB.alert({
			title : "대상없음",
			content : "권한부여할 작업이 없습니다."
		});
		return;
	}

	PB.confirm({
		title : "권한부여확인",
		content : "해당 작업에 대한 권한을 부여하시겠습니까?",
		button1 : "부여하기"
	}, function(c_){
		if(!c_) return;

		var revoke_list_ = [];
		var grant_list_ = [];

		all_use_yn_checkboxes_.filter(function(){
			return !$(this).prop("checked");
		}).each(function(){
			revoke_list_.push($(this).attr("data-auth-task"));
		});

		all_use_yn_checkboxes_.filter(function(){
			return $(this).prop("checked");
		}).each(function(){
			grant_list_.push($(this).attr("data-auth-task"));
		});

		_pb_manage_authority_task_update(grant_list_, revoke_list_);
	});
}
function _pb_manage_authority_task_update(grant_list_, revoke_list_){
	var splitted_view_el_ = $("#pb-authority-splitted-view");

	PB.post("pb-admin-authority-task-update",{
		auth_id : splitted_view_el_.pb_easy_splitted_view().current_master_id(),
		grant_list : grant_list_,
		revoke_list : revoke_list_,
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "권한부여 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "권한부여완료",
			content : "권한부여가 완료되었습니다."
		}, function(){
			splitted_view_el_.pb_easy_splitted_view().load_detail_list();

		});

	}, true);
}
