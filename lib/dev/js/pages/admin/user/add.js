jQuery(document).ready(function(){
	var edit_form_ = $("#pb-manage-user-add-form");
	edit_form_.validator();
	edit_form_.submit_handler(function(){
		PB.confirm({
			title : __("추가확인"),
			content : __("사용자를 추가하시겠습니까?")
		}, function(c_){
			if(!c_) return;
			_pb_manage_user_do_add();
		});
	});

	var user_authority_el_ = edit_form_.find("[name='user_authority']");
	if(user_authority_el_.length > 0){
		user_authority_el_.selectpicker({
			noneSelectedText : __("권한없음"),
		});
	}
});

function _pb_manage_user_do_add(){
	var edit_form_ = $("#pb-manage-user-add-form");
	var request_data_ = edit_form_.serialize_object();

	if(!request_data_['user_pass'] || request_data_['user_pass'] === ""){
		delete request_data_['user_pass'];
	}else{
		request_data_['user_pass'] = PB.crypt.encrypt(request_data_['user_pass']);
	}

	if(!request_data_['user_authority']){
		request_data_['user_authority'] = [];
	}

	if($.type(request_data_['user_authority']) === "string"){
		request_data_['user_authority']	= [request_data_['user_authority']];
	}

	PB.post("pb-admin-manage-user-do-add",{
		request_data : request_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("사용자 추가 중, 에러가 발생했습니다."),
			});
			return;
		}

		PB.alert({
			title : __("추가완료"),
			content : __("사용자 정보가 추가되었습니다.")
		}, function(){
			document.location = response_json_.redirect_url;
		});

	}, true);
}