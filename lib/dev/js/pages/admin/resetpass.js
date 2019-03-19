jQuery(document).ready(function(){
	var resetpass_form_ = $("#pb-resetpass-form");
	resetpass_form_.validator();

	resetpass_form_.find("[name='user_pass']").focus();

	resetpass_form_.submit_handler(function(){
		_pb_resetpass();
	});

});

function _pb_resetpass(){
	var resetpass_form_ = $("#pb-resetpass-form");
	var request_data_ = resetpass_form_.serialize_object();
		request_data_ = pb_apply_filters('encrypt', request_data_, ["user_pass"]);

	PB.post_url("_ajax_do_resetpass.php", {
		request_data : request_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "비밀번호 재설정 중, 에러가 발생했습니다.", 
			});
			return;
		}

		PB.alert({
			title : "재설정 완료",
			content : "비밀번호를 재설정 하였습니다.<br/>로그인페이지로 이동합니다."
		}, function(){
			document.location = response_json_.redirect_url;	
		});

	}, true);
}