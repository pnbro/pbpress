jQuery(document).ready(function(){
	var page_edit_form_ = $("#pb-page-edit-form");

	page_edit_form_.validator();
	page_edit_form_.submit_handler(function(){
		pb_page_edit_form_submit(page_edit_form_.serialize_object());
	});
});
function pb_page_edit_form_submit(page_data_){
	var actived_editor_id_ = $("#pb-page-html-editor").pb_editors().actived_editor_id();
	page_data_['actived_editor_id'] = actived_editor_id_;
	PB.post("edit-page", {
		page_data : page_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지수정 중, 에러가 발생했습니다.",
			});
			return;
		}

		document.location = response_json_.redirect_url;

	}, true);
}
function pb_page_edit_form_delete(page_id_){
	PB.confirm({
		title : "삭제확인",
		content : "해당 페이지를 삭제합니다. 계속하시겠습니까?",
		button1 : "삭제하기",
	}, function(c_){
		if(!c_) return;
		_pb_page_edit_form_delete(page_id_);
	});
}

function _pb_page_edit_form_delete(page_id_){
	PB.post("delete-page", {
		page_id : page_id_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지 삭제 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : "페이지가 삭제되었습니다. 페이지내역으로 돌아갑니다.",
		}, function(){
			document.location = response_json_.redirect_url;
		});

	}, true);
}