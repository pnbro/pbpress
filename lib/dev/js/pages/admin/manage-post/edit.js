jQuery(document).ready(function(){
	var post_edit_form_ = $("#pb-post-edit-form");

	post_edit_form_.validator();
	post_edit_form_.submit_handler(function(){
		pb_post_edit_form_submit(post_edit_form_.serialize_object());
	});

	$("#pb-post-featured-image-picker").pb_image_input();

	$("#pb-post-reg-date-picker").datetimepicker({
		format : "YYYY-MM-DD HH:mm",
		maxDate : moment(),
	});

	var url_slug_group_ = $("#pb-post-edit-form-url-slug-group");
	var url_slug_input_ = url_slug_group_.find("[name='slug']");
	$("[data-slug-edit-btn]").click(function(){
		url_slug_group_.toggleClass("editing", true);
		url_slug_input_.focus().select();
		return false;
	});

	url_slug_input_.keydown(function(event_){
		if(event_.keyCode === 13){
			if(post_edit_form_.find("[name='id']").val() !== ""){
				pb_post_update_slug();	
			}
			return false;
		}
	});

	$("[data-slug-edit-update-btn]").click(function(){
		pb_post_update_slug();
		return false;
	});
	$("[data-slug-edit-cancel-btn]").click(function(){
		url_slug_group_.toggleClass("editing", false);
		url_slug_input_.val(url_slug_input_.attr("data-original-slug"));
		return false;
	});
});
function pb_post_edit_form_submit(post_data_){
	var pb_editors_ = $("#pb-post-html-editor").pb_editors();
	var actived_editor_id_ = pb_editors_.actived_editor_id();
	post_data_['post_html'] = pb_editors_.html();
	post_data_['actived_editor_id'] = actived_editor_id_;
	post_data_['type'] = window._pbpost_type;
	PB.post("edit-post", {
		post_data : post_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "글수정 중, 에러가 발생했습니다.",
			});
			return;
		}

		document.location = response_json_.redirect_url;

	}, true);
}
function pb_post_edit_form_delete(post_id_){
	PB.confirm({
		title : "삭제확인",
		content : window._pbpost_type_data['label']['before_delete'],
		button1 : "삭제하기",
	}, function(c_){
		if(!c_) return;
		_pb_post_edit_form_delete(post_id_);
	});
}

function _pb_post_edit_form_delete(post_id_){
	PB.post("delete-post", {
		post_id : post_id_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "글 삭제 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : window._pbpost_type_data['label']['after_delete'],
		}, function(){
			document.location = response_json_.redirect_url;
		});

	}, true);
}
function pb_post_update_slug(){
	var post_edit_form_ = $("#pb-post-edit-form");
	var url_slug_group_ = $("#pb-post-edit-form-url-slug-group");
	var url_slug_input_ = url_slug_group_.find("[name='slug']");
	var post_data_ = post_edit_form_.serialize_object();

	url_slug_group_.find(":input,button").prop("disabled", true);

	var slug_ = url_slug_input_.val();

	PB.post("update-post-slug", {
		post_id : post_data_['id'],
		slug : slug_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "슬러그 수정중, 에러가 발생했습니다.",
			});
			return;
		}

		var updated_slug_ = response_json_.slug;

		url_slug_group_.find(":input,button").prop("disabled", false);
		url_slug_input_.attr("data-original-slug", updated_slug_);
		url_slug_input_.val(updated_slug_);

		var post_link_ = $("[data-post-link]");
		post_link_.find(".slug").text(updated_slug_);
		post_link_.attr("href", PBVAR['home_url']+updated_slug_);
		url_slug_group_.toggleClass("editing", false);

	});
}

function pb_post_add_new_category(){
	var category_name_ = $("[name='new_category_name']").val();
	if(!category_name_ || category_name_ === "") return;

	PB.post("pb-admin-post-category-insert", {
		'target_data' : {
			title : category_name_,
			type : window._pbpost_type,
		}
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert(response_json_.error_title || "에러발생", response_json_.error_message || "카테고리 등록 중, 에러가 발생했습니다.");
			return;
		}

		var added_data_ = response_json_.added_data;

		var category_frame_el_ = $("[data-post-category-frame]");
		category_frame_el_.find("[data-no-category-label]").remove();

		category_frame_el_.append('<div class="checkbox"> \
			<label><input type="checkbox" name="category_id" value="'+added_data_['id']+'">'+added_data_['title']+'</label> \
		</div>');
		 $("[name='new_category_name']").val("");
	});
}