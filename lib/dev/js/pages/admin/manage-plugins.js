jQuery(document).ready(function(){
	$(document).on("click", "[data-active-plugin-link]",function(){
		var slug_ = $(this).attr("data-active-plugin-link");
		var name_ = $(this).attr("data-active-plugin-name");

		pb_manage_plugins_active([slug_], [name_]);

		return false;
	});

	$(document).on("click", "[data-deactive-plugin-link]",function(){
		var slug_ = $(this).attr("data-deactive-plugin-link");
		var name_ = $(this).attr("data-deactive-plugin-name");
		
		pb_manage_plugins_deactive([slug_], [name_]);

		return false;
	});

	$(document).on("click", ":input[name='all_cb']", function(){
		var toggled_ = !!$(this).prop("checked");
		$("#pb-manage-plugins-form-table :input[name='cb']").prop("checked", toggled_);
	});
	$(document).on("click", ":input[name='cb']", function(){
		var all_cbs_ = $("#pb-manage-plugins-form-table :input[name='cb']");
		var all_toggled_ = all_cbs_.length <= all_cbs_.filter(":checked").length;
		$("#pb-manage-plugins-form-table :input[name='all_cb']").prop("checked", all_toggled_);
	});
});

function pb_manage_plugins_active(slugs_, names_){
	names_ = names_.join(",");
	PB.confirm({
		title : __("작업확인"),
		content : __("<p class='text-center'>%1s</p>플러그인을 활성화 하시겠습니까?").format(names_),
		button1 : __("활성화하기")
	}, function(c_){
		if(!c_) return;
		_pb_manage_plugins_active(slugs_);
	});
}
function _pb_manage_plugins_active(slugs_){
	PB.post("admin-active-plugins",{
		slugs : slugs_,
		request_chip : $("#pb-manage-plugins-form-request-chip").val(),
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("플러그인 활성화 중, 에러가 발생했습니다."),
			});
			return;
		}

		PB.alert({
			title : __("작업완료"),
			content : __("플러그인을 활성화 하였습니다.")
		}, function(){
			location.reload();	
		});
	}, true);
}

function pb_manage_plugins_deactive(slugs_, names_){
	names_ = names_.join(",");
	
	PB.confirm({
		title : __("작업확인"),
		content : __("<p class='text-center'>%1s</p>플러그인을 비활성화 하시겠습니까?").format(names_),
		button1 : __("비활성화하기")
	}, function(c_){
		if(!c_) return;
		_pb_manage_plugins_deactive(slugs_);
	});
}
function _pb_manage_plugins_deactive(slugs_){
	PB.post("admin-deactive-plugins",{
		slugs : slugs_,
		request_chip : $("#pb-manage-plugins-form-request-chip").val(),
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("플러그인 비활성화 중, 에러가 발생했습니다."),
			});
			return;
		}

		PB.alert({
			title : __("작업완료"),
			content : __("플러그인을 비활성화 하였습니다.")
		}, function(){
			location.reload();	
		});

		
	}, true);
}

function pb_manage_plugins_batch(){
	var task_type_ = $("#pb-manage-plugins-form-task-type-selector").val();
	var selected_slugs_ = [];
	var selected_names_ = [];

	var selected_cbs_ = $("#pb-manage-plugins-form-table :input[name='cb']:checked");
		selected_cbs_.each(function(){
			var cb_el_ = $(this);
			selected_slugs_.push(cb_el_.val());
			selected_names_.push(cb_el_.attr("data-plugin-name"));
		});

	if(selected_slugs_.length <= 0){
		PB.alert({
			title : __("확인필요"),
			content : __("선택된 플러그인이 없습니다.")
		});
		return;
	}

	switch(task_type_){
		case  "active" : 
			pb_manage_plugins_active(selected_slugs_, selected_names_);
			break;
		case  "deactive" : 
			pb_manage_plugins_deactive(selected_slugs_, selected_names_);
			break;
		default : 

			PB.alert({
				title : __("확인필요"),
				content : __("선택된 작업이 없습니다.")
			});
			return;

		break;
	}
}