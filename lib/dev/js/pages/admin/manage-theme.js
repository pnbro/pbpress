jQuery(document).ready(function(){
	var theme_form_ = $("#pb-manage-theme-form");
	theme_form_.validator();

	theme_form_.submit_handler(function(){
		pb_manage_theme_do_update();
	});
});

function _pb_manage_theme_change_theme(theme_){
	$("[data-theme-item]").toggleClass("active", false);
	$("[data-theme-item='"+theme_+"']").toggleClass("active", true);

	var theme_form_ = $("#pb-manage-theme-form");
	var target_theme_input_ = theme_form_.find("[name='theme']");

	target_theme_input_.val(theme_);
}


function pb_manage_theme_do_update(){
	PB.confirm({
		title : "작업확인",
		content : "변경사항을 저장하시겠습니까?",
		button1 : "저장하기",

	}, function(c_){
		if(!c_) return;
		_pb_manage_theme_do_update();
	});
}

function _pb_manage_theme_do_update(){
	var theme_form_ = $("#pb-manage-theme-form");
	PB.post("admin-update-theme", {
		theme_data : pb_apply_filters('pb-manage-theme-update-data', theme_form_.serialize_object()),
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "변경사항 저장 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "작업완료",
			content : "변경사항을 저장하였습니다.",
		}, function(){
			document.location = response_json_.redirect_url;
		});
		return;

	}, true);
}