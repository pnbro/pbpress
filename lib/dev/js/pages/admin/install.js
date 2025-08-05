jQuery(document).ready(function(){
	var install_form_ = $("#pb-install-form");
	install_form_.validator();

	install_form_.submit_handler(function(){
		pb_install();
	});
});

function pb_install(){
	PB.confirm({
		title : __("설치확인"),
		content : __("해당정보로 PBPress 설치를 진행하시겠습니까?"),
		button1 : __("설치하기")
	}, function(c_){
		if(!c_) return c_;
		_pb_install();
	});
}

function _pb_install(){
	var install_form_ = $("#pb-install-form");
	var request_data_ = install_form_.serialize_object();
		request_data_ = pb_apply_filters('encrypt', request_data_, ["user_pass"]);

	delete request_data_['user_pass_c'];

	PB.post_url("_ajax_do_install.php", {
		request_data : request_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("설치 중 에러가 발생했습니다."), 
			});
			return;
		}

		PB.alert({
			title : __("축하합니다!"),
			content : __("PBPress 설치가 완료되었습니다.<br/>관리자페이지로 이동합니다."),

		}, function(){
			document.location = "index.php";
		});

	}, true);
}