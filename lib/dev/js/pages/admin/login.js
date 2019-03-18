jQuery(document).ready(function(){
	var login_form_ = $("#pb-login-form");
	login_form_.validator();

	login_form_.find("[name='user_login']").focus();

	login_form_.submit_handler(function(){
		_pb_login();
	});

});

function _pb_login(){
	var login_form_ = $("#pb-login-form");
	var login_data_ = login_form_.serialize_object();
		login_data_ = pb_apply_filters('encrypt', login_data_, ["user_pass"]);

	var redirect_url_ = login_data_['redirect_url'];

	delete login_data_['redirect_url'];

	PB.post_url("_ajax_do_login.php", {
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