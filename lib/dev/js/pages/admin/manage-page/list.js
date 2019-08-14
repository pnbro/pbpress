jQuery(document).ready(function(){

	$("[data-page-status]").change(function(){
		var target_el_ = $(this);
		var page_id_ = target_el_.attr("data-page-status");

		target_el_.prop("disabled", true);
		pb_manage_page_update_status(page_id_, target_el_.val(), $.proxy(function(){
			this.prop("disabled", false);
		}, target_el_));
	});
});

function pb_manage_page_update_status(page_id_, status_, callback_){
	PB.post("change-page-status",{
		page_id : page_id_,
		status : status_
	}, $.proxy(function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지상태 수정 중, 에러가 발생했습니다.",
			});
			return;
		}

		$("[data-page-status='"+this['page_id']+"']").val(this['status']);
		this['callback'].apply(window);
	}, {
		page_id : page_id_,
		status : status_,
		callback : callback_
	}));
}

function pb_manage_page_remove(page_id_){
	PB.confirm({
		title : "삭제확인",
		content : "해당 페이지를 삭제합니다. 계속하시겠습니까?",
		button1 : "삭제하기",
	}, function(c_){
		if(!c_) return;
		_pb_manage_page_remove(page_id_);
	});
}

function _pb_manage_page_remove(page_id_){
	PB.post("delete-page", {
		page_id : page_id_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지 삭제 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : "페이지가 삭제되었습니다.",
		}, function(){
			location.reload();
		});

	}, true);
}
function pb_manage_page_register_front_page(page_id_){
	PB.confirm({
		title : "홈화면 지정확인",
		content : "해당 페이지를 홈화면으로 지정하시겠습니까?",
		button1 : "지정하기"
	}, function(c_){
		if(!c_) return;
		PB.post("register-front-page",{
			page_id : page_id_,
		}, function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "수정 중, 에러가 발생했습니다.",
				});
				return;
			}

			location.reload();
		}, true);
	});
		
}
function pb_manage_page_unregister_front_page(){
	PB.confirm({
		title : "홈화면 지정해제",
		content : "홈화면 지정을 해제하시겠습니까?",
		button1 : "해제하기"
	}, function(c_){
		if(!c_) return;
		PB.post("unregister-front-page",{
		}, function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "수정 중, 에러가 발생했습니다.",
				});
				return;
			}

			location.reload();
		},true);
	});	
}