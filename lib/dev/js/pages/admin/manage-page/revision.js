jQuery(document).ready(function(){
	var splitted_view_el_ = $("#pb-page-revision-splitted-view");
	var splitted_view_module_ = splitted_view_el_.pb_easy_splitted_view({
		'master-info-loaded' : function(master_data_){
			var iframe_el_ = $("<iframe></iframe");
				iframe_el_.attr("src", PB.append_url(PBVAR['home_url'], "__page-revision/"+master_data_['id']));
			$("#pb-revision-iframe-group").empty().append(iframe_el_);
		}
	});
});


function pb_admin_page_restore(){
	PB.confirm({
		title : "복구확인",
		content : "해당 버젼으로 복구하시겠습니까?",
		button1 : "복구하기",
	}, function(c_){
		if(!c_) return;
		var splitted_view_el_ = $("#pb-page-revision-splitted-view");	
		var revision_id_ = splitted_view_el_.pb_easy_splitted_view().current_master_id();
		_pb_admin_page_restore(revision_id_);

	});
}
function _pb_admin_page_restore(revision_id_){
	PB.post("pb-admin-restore-page-from-revision",{
		revision_id : revision_id_
	},function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지 복구 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "복구완료",
			content : "해당 리비젼으로 복구가 완료되었습니다.",
		}, function(){
			$("#pb-page-revision-splitted-view").pb_easy_splitted_view().refresh();
		});

	}, true);
}

function pb_admin_revision_delete(revision_id_){
	PB.confirm({
		title : "삭제확인",
		content : "해당 버젼을 삭제하시겠습니까?",
		button1 : "삭제하기",
	}, function(c_){
		if(!c_) return;
		_pb_admin_revision_delete(revision_id_);

	});	
}
function _pb_admin_revision_delete(revision_id_){
	PB.post("pb-admin-delete-page-revision",{
		revision_id : revision_id_
	},function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "리비젼 삭제 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : "해당 리비젼 삭제가 완료되었습니다.",
		}, function(){
			$("#pb-page-revision-splitted-view").pb_easy_splitted_view().refresh();
		});

	}, true);
}