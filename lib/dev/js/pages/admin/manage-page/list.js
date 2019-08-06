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