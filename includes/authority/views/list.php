<?php 	

	$master_listable_ = new PB_authority_list_table("pb-authority-listtable", "pb-authority-listtable");
	$master_listable_->set_ajax(true);

	$detail_listable_ = new PB_authority_task_list_table("pb-authority-task-listtable", "pb-authority-task-listtable");
	$detail_listable_->set_ajax(true);
?>

	<div class="splitted-view-frame" id="pb-authority-splitted-view"><div class="wrap">

		<div class="col-master">
			<h3>권한내역 <a href="javascript:_pb_authority_add();" class="btn btn-primary btn-sm">권한추가</a></h3>
			<form method="GET" class="pb-listtable-cond-form" id="pb-authority-cond-form" data-master-cond-form>
				<div class="right-frame">
					<input type="hidden" name="page_index" value="0">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="권한명" name="keyword" >
						<span class="input-group-btn">
							<button type="submit" class="btn btn-default" type="button">검색하기</button>
						</span>
					</div>
				</div>
			</form>	

			<form method="GET" data-ref-conditions-form="#pb-authority-cond-form" data-master-listtable-form>
				<input type="hidden" name="keyword">
			<?php 
				
				echo $master_listable_->html();
			?>
			</form>
		</div>
		<div class="col-detail">

			<div class="notfound-overlay">
				좌측에서 권한을 선택하세요
			</div>
			
			<div class="master-info-fram master-info-frame">
				<h3>권한정보</h3>
				<table class="pb-form-table master-info-table" data-master-info-form-table>
					<tbody>
						<tr>
							<th>고유번호</th>
							<td data-column="ID"></td>
							<th>권한명</th>
							<td data-column="AUTH_NAME"></td>
							<th>슬러그</th>
							<td data-column="SLUG"></td>
						</tr>
					
					</tbody>
				</table>
				
			</div>

			<h3>권한별 작업내역</h3>

			<form method="GET" class="pb-listtable-cond-form detail-search-cond-form" data-detail-cond-form id="pb-authority-dtl-cond-form">

				<input type="hidden" name="page_index" value="0">
			</form>	


			<div class="detail-listtable-frame">
				<form method="GET" action="" data-ref-conditions-form="#pb-authority-dtl-cond-form" data-detail-listtable-form>
					
					<?php 
						echo $detail_listable_->html();
					?>
				</form>

				<hr/>

				<div class="button-area text-right">
					<a href="javascript:pb_manage_authority_task_update();" class="btn btn-primary">변경사항 저장</a>
				</div>
			</div>
		</div>
	</div></div>


<form id="pb-authority-edit-form">
	<table class="pb-form-table " >
		<tbody>
			
			<tr>
				<th>권한명</th>
				<td>
					<div class="form-group">
						<input type="text" name="AUTH_NAME" class="form-control" placeholder="권한명 입력" required data-error="권한명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th>슬러그</th>
				<td>
					<div class="form-group">
						<input type="text" name="SLUG" class="form-control" placeholder="슬러그 입력" required data-error="슬러그를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

				</td>
				
			</tr>
			
		</tbody>
	</table>
</form>

<script type="text/javascript">
jQuery(document).ready(function(){
	var splitted_view_el_ = $("#pb-authority-splitted-view");
	splitted_view_el_.pb_splitted_view({
		'master-info-load-action' : "pb-admin-authority-load"
	});

	var auth_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-authority-load",
		insert_action : "pb-admin-authority-insert",
		update_action : "pb-admin-authority-update",
		delete_action : "pb-admin-authority-delete",

		before_insert : function(target_data_, callback_){

			PB.confirm({
				title : "추가확인",
				content : "권한을 추가하시겠습니까?",
				button1 : "추가하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));

		},
		before_update : function(target_data_, callback_){
			callback_(true);
		},
		before_delete : function(target_data_, callback_){
			PB.confirm({
				title : "삭제확인",
				content : "권한을 삭제하시겠습니까?",
				button1 : "삭제하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));
		},

		after_empty_opened : function(form_el_){
			//추가팝업이 열렸을 때 이벤트, 
		},
		after_loaded : function(form_el_, form_data_){
			// form_el_.find("[name='USE_YN'][value='"+form_data_['USE_YN']+"']").prop("checked", true);
		},
		after_inserted : function(){
			PB.alert({
				title : "추가완료",
				content : "추가가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-authority-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_updated : function(){
			PB.alert({
				title : "수정완료",
				content : "수정이 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-authority-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_deleted : function(){
			PB.alert({
				title : "삭제완료",
				content : "삭제가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-authority-cond-form").submit(); //리스트테이블 재검색
			});
		}
	});

});

function _pb_authority_add(){
	var level_edit_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal();
		level_edit_form_module_.open_for_insert();
}
function _pb_authority_edit(code_id_){
	var level_edit_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal();
		level_edit_form_module_.open_for_update(code_id_);
}
function _pb_authority_remove(code_id_){
	var level_edit_form_module_ = $("#pb-authority-edit-form").pb_edit_form_modal();
		level_edit_form_module_.do_delete(code_id_);
}

function pb_manage_authority_task_update(){
	var task_table_el_ = $("#pb-authority-task-listtable");
	var all_use_yn_checkboxes_ = task_table_el_.find("[name='GRANT_YN']");

	all_use_yn_checkboxes_ = all_use_yn_checkboxes_.filter(function(){
		return !$(this).prop("disabled")
	});

	if(all_use_yn_checkboxes_.length <= 0){
		PB.alert({
			title : "대상없음",
			content : "권한부여할 작업이 없습니다."
		});
		return;
	}

	PB.confirm({
		title : "권한부여확인",
		content : "해당 작업에 대한 권한을 부여하시겠습니까?",
		button1 : "부여하기"
	}, function(c_){
		if(!c_) return;

		var revoke_list_ = [];
		var grant_list_ = [];

		all_use_yn_checkboxes_.filter(function(){
			return !$(this).prop("checked");
		}).each(function(){
			revoke_list_.push($(this).attr("data-auth-task"));
		});

		all_use_yn_checkboxes_.filter(function(){
			return $(this).prop("checked");
		}).each(function(){
			grant_list_.push($(this).attr("data-auth-task"));
		});

		_pb_manage_authority_task_update(grant_list_, revoke_list_);
	});
}
function _pb_manage_authority_task_update(grant_list_, revoke_list_){
	var splitted_view_el_ = $("#pb-authority-splitted-view");

	PB.post("pb-admin-authority-task-update",{
		auth_id : splitted_view_el_.pb_splitted_view().current_master_id(),
		grant_list : grant_list_,
		revoke_list : revoke_list_,
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "권한부여 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "권한부여완료",
			content : "권한부여가 완료되었습니다."
		}, function(){
			splitted_view_el_.pb_splitted_view().refresh();
		});

	}, true);
}

</script>