jQuery(document).ready(function(){
	var login_form_ = $("#pb-login-form");
	login_form_.validator();

	login_form_.find("[name='user_login']").focus();

	login_form_.submit_handler(function(){
		_pb_login();
	});

	var findpass_modal_ = $("#pb-admin-login-findpass-modal");
	var findpass_modal_form_ = $("#pb-admin-login-findpass-form");
	$("#pb-admin-login-findpass-modal").on("shown.bs.modal", function(){
		findpass_modal_.find("[name='user_email']").focus();
	});

	findpass_modal_form_.validator();
	findpass_modal_form_.submit_handler(function(){
		_pb_do_findpass();
	});

});

function _pb_login(){
	var login_form_ = $("#pb-login-form");
	var login_data_ = login_form_.serialize_object();
		login_data_ = pb_apply_filters('encrypt', login_data_, ["user_pass"]);

	var redirect_url_ = login_data_['redirect_url'];

	delete login_data_['redirect_url'];
	
	PB.post_url(PB.append_url(PBVAR['home_url'], "admin/_ajax_do_login.php"), {
		login_data : login_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "로그인 중, 에러가 발생했습니다.", 
			});
			return;
		}

		document.location = redirect_url_;

	}, true);
}

function _pb_do_findpass(){
		var findpass_modal_ = $("#pb-admin-login-findpass-modal");
		var findpass_modal_form_ = $("#pb-admin-login-findpass-form");
		PB.post_url(PB.append_url(PBVAR['home_url'], "admin/_ajax_do_findpass.php"), {
			user_email : findpass_modal_.find("[name='user_email']").val(),
		}, function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "비밀번호 재설정 메일 발송 중, 에러가 발생했습니다.",
				});
				return;
			}

			PB.alert({
				title : "메일발송완료",
				content : "가입하신 이메일로 비밀번호 재설정 링크를 발송하였습니다.<br/>메일을 확인하여 주세요."
			});
			findpass_modal_.modal("hide");

		}, true);
	}