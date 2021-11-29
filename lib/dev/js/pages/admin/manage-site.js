jQuery(document).ready(function(){
	var settings_form_ = $("#pb-manage-site-form");
	settings_form_.validator();

	settings_form_.submit_handler(function(){
		pb_manage_site_update_settings();
	});

});

function pb_manage_site_update_settings(){
	PB.confirm({
		title : "작업확인",
		content : "변경사항을 저장하시겠습니까?",
		button1 : "저장하기",

	}, function(c_){
		if(!c_) return;
		_pb_manage_site_update_settings();
	});
}

function _pb_manage_site_update_settings(){
	var settings_form_ = $("#pb-manage-site-form");
	var setting_data_ = pb_apply_filters('pb-manage-site-update-settings', settings_form_.serialize_object());
	var crypted_columns_ = [];

	settings_form_.find("[data-crypt-field='Y']").each(function(){
		var column_name_ = $(this).attr('name');
		console.log(column_name_);
		setting_data_[column_name_] = PB.crypt.encrypt(setting_data_[column_name_]);
		crypted_columns_.push(column_name_);
	});

	PB.post_url(PB.append_url(PBVAR['home_url'], "admin/_ajax_update_site_settings.php"), {
		settings_data : setting_data_,
		crypted_columns : crypted_columns_,
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

function pb_manage_site_reinstall_tables(){
	PB.confirm({
		title : "작업확인",
		content : "테이블 재설치를 진행하시겠습니까?",
		button1 : "재설치하기",

	}, function(c_){
		if(!c_) return;
		_pb_manage_site_reinstall_tables();
	});
}

function _pb_manage_site_reinstall_tables(){

	var settings_form_ = $("#pb-manage-site-form");
	var settings_data_ = settings_form_.serialize_object();
	PB.post_url(PB.append_url(PBVAR['home_url'], "admin/_ajax_reinstall_tables.php"), {
		'request_chip' : settings_data_['_request_chip']
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "테이블 재설치 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "작업완료",
			content : "테이블 재설치를 완료하였습니다.",
		},function(){
			document.location = response_json_.redirect_url;
		});
		return;

	}, true);
}