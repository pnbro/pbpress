jQuery(document).ready(function(){

	$("[data-post-status]").change(function(){
		var target_el_ = $(this);
		var post_id_ = target_el_.attr("data-post-status");

		target_el_.prop("disabled", true);
		pb_manage_post_update_status(post_id_, target_el_.val(), $.proxy(function(){
			this.prop("disabled", false);
		}, target_el_));
	});
});

function pb_manage_post_update_status(post_id_, status_, callback_){
	PB.post("change-post-status",{
		post_id : post_id_,
		status : status_
	}, $.proxy(function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("상태 수정 중, 에러가 발생했습니다."),
			});
			return;
		}

		$("[data-post-status='"+this['post_id']+"']").val(this['status']);
		this['callback'].apply(window);
	}, {
		post_id : post_id_,
		status : status_,
		callback : callback_
	}));
}

function pb_manage_post_remove(post_id_){
	PB.confirm({
		title : __("삭제확인"),
		content : window._pbpost_type_data['label']['before_delete'],
		button1 : __("삭제하기"),
	}, function(c_){
		if(!c_) return;
		_pb_manage_post_remove(post_id_);
	});
}

function _pb_manage_post_remove(post_id_){
	PB.post("delete-post", {
		post_id : post_id_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || __("에러발생"),
				content : response_json_.error_message || __("글 삭제 중, 에러가 발생했습니다."),
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : window._pbpost_type_data['label']['after_delete'],
		}, function(){
			location.reload();
		});

	}, true);
}
