jQuery(document).ready(function(){
	var page_edit_form_ = $("#pb-page-edit-form");

	page_edit_form_.validator();
	page_edit_form_.submit_handler(function(){
		pb_page_edit_form_submit(page_edit_form_.serialize_object());
	});

	var url_slug_group_ = $("#pb-page-edit-form-url-slug-group");
	var url_slug_input_ = url_slug_group_.find("[name='slug']");
	$("[data-slug-edit-btn]").click(function(){
		url_slug_group_.toggleClass("editing", true);
		url_slug_input_.focus().select();
		return false;
	});

	url_slug_input_.keydown(function(event_){
		if(event_.keyCode === 13){
			if(page_edit_form_.find("[name='id']").val() !== ""){
				pb_page_update_slug();	
			}
			return false;
		}
	});

	$("[data-slug-edit-update-btn]").click(function(){
		pb_page_update_slug();
		return false;
	});
	$("[data-slug-edit-cancel-btn]").click(function(){
		url_slug_group_.toggleClass("editing", false);
		url_slug_input_.val(url_slug_input_.attr("data-original-slug"));
		return false;
	});
});
function pb_page_edit_form_submit(page_data_){
	var pb_editors_ = $("#pb-page-html-editor").pb_editors();
	var actived_editor_id_ = pb_editors_.actived_editor_id();
	page_data_['page_html'] = pb_editors_.html();
	page_data_['actived_editor_id'] = actived_editor_id_;
	PB.post("edit-page", {
		page_data : page_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("페이지수정 중, 에러가 발생했습니다."),
			});
			return;
		}

		document.location = response_json_.redirect_url;

	}, true);
}
function pb_page_edit_form_delete(page_id_){
	PB.confirm({
		title : __("삭제확인"),
		content : __("해당 페이지를 삭제합니다. 계속하시겠습니까?"),
		button1 : __("삭제하기"),
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
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("페이지 삭제 중, 에러가 발생했습니다."),
			});
			return;
		}

		PB.alert({
			title : __("삭제완료"),
			content : __("페이지가 삭제되었습니다. 페이지내역으로 돌아갑니다."),
		}, function(){
			document.location = response_json_.redirect_url;
		});

	}, true);
}
function pb_page_update_slug(){
	var page_edit_form_ = $("#pb-page-edit-form");
	var url_slug_group_ = $("#pb-page-edit-form-url-slug-group");
	var url_slug_input_ = url_slug_group_.find("[name='slug']");
	var page_data_ = page_edit_form_.serialize_object();

	url_slug_group_.find(":input,button").prop("disabled", true);

	var slug_ = url_slug_input_.val();

	PB.post("update-page-slug", {
		page_id : page_data_['id'],
		slug : slug_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("슬러그 수정중, 에러가 발생했습니다."),
			});
			return;
		}

		var updated_slug_ = response_json_.slug;

		url_slug_group_.find(":input,button").prop("disabled", false);
		url_slug_input_.attr("data-original-slug", updated_slug_);
		url_slug_input_.val(updated_slug_);

		var page_link_ = $("[data-page-link]");
		page_link_.find(".slug").text(updated_slug_);
		page_link_.attr("href", PBVAR['home_url']+updated_slug_);
		url_slug_group_.toggleClass("editing", false);

	});
}