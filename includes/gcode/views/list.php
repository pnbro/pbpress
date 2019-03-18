<?php 	

	$master_listable_ = new PB_gcode_list_table("pb-gcode-listtable", "pb-gcode-listtable");
	$master_listable_->set_ajax(true);

	$detail_listable_ = new PB_gcode_dtl_list_table("pb-gcode-dtl-listtable", "pb-gcode-dtl-listtable");
	$detail_listable_->set_ajax(true);

?>

	<div class="splitted-view-frame" id="pb-gcode-splitted-view"><div class="wrap">

		<div class="col-master">
			<h3>공통코드내역 <a href="javascript:_pb_gcode_add();" class="btn btn-primary btn-sm">코드추가</a></h3>
			<form method="GET" class="pb-listtable-cond-form" id="pb-gcode-cond-form" data-master-cond-form>
				<div class="right-frame">
					<input type="hidden" name="page_index" value="0">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="코드명" name="keyword" >
						<span class="input-group-btn">
							<button type="submit" class="btn btn-default" type="button">검색하기</button>
						</span>
					</div>
				</div>
			</form>	

			<form method="GET" data-ref-conditions-form="#pb-gcode-cond-form" data-master-listtable-form>
				<input type="hidden" name="keyword">
			<?php 
				
				echo $master_listable_->html();
			?>
			</form>
		</div>
		<div class="col-detail">

			<div class="notfound-overlay">
				좌측에서 공통코드를 선택하세요
			</div>
			
			<div class="master-info-fram master-info-frame">
				<h3>코드정보</h3>
				<table class="pb-form-table master-info-table" data-master-info-form-table>
					<tbody>
						<tr>
							<th>ID</th>
							<td data-column="CODE_ID"></td>
							<th>코드명</th>
							<td data-column="CODE_NM"></td>
							<th>사용여부</th>
							<td data-column="USE_YN"></td>
						</tr>
					
					</tbody>
				</table>
				
			</div>

			<h3>상세코드내역 <a href="javascript:_pb_gcode_dtl_add();" class="btn btn-primary btn-sm">상세추가</a></h3>

			<form method="GET" class="pb-listtable-cond-form detail-search-cond-form" data-detail-cond-form id="pb-gcode-dtl-cond-form">

				<input type="hidden" name="page_index" value="0">
				<div class="input-group">
					<input type="text" class="form-control" placeholder="상세코드명" name="keyword" >
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default" type="button">검색하기</button>
					</span>	
				</div>
			</form>	


			<div class="detail-listtable-frame">
				<form method="GET" action="" data-ref-conditions-form="#pb-gcode-dtl-cond-form" data-detail-listtable-form>
					<input type="hidden" name="keyword">
					<?php 
						echo $detail_listable_->html();
					?>
				</form>
			</div>
		</div>
	</div></div>


<form id="pb-gcode-edit-form">
	<table class="pb-form-table " >
		<tbody>
			<tr>
				<th>코드ID</th>
				<td>
					<div class="form-group">
						<input type="text" name="CODE_ID" class="form-control" placeholder="코드 입력" required data-error="코드를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

				</td>
				
			</tr>
			<tr>
				<th>코드명</th>
				<td>
					<div class="form-group">
						<input type="text" name="CODE_NM" class="form-control" placeholder="코드명 입력" required data-error="레벨명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

			<tr>
				<th>사용여부</th>
				<td >
					<div class="form-group">
						<label class="radio-inline">
							<input type="radio" name="USE_YN" value="Y"> Y
						</label>

						<label class="radio-inline">
							<input type="radio" name="USE_YN" value="N"> N
						</label>
						
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

			

			
			

			
		</tbody>
	</table>
</form>


<form id="pb-gcode-dtl-edit-form">
	<input type="hidden" name="CODE_ID">
	<table class="pb-form-table " >
		<tbody>
			<tr>
				<th>상세코드ID</th>
				<td>
					<div class="form-group">
						<input type="text" name="CODE_DID" class="form-control" placeholder="코드 입력" required data-error="코드를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

				</td>
				
			</tr>
			<tr>
				<th>상세코드명</th>
				<td>
					<div class="form-group">
						<input type="text" name="CODE_DNM" class="form-control" placeholder="코드명 입력" required data-error="레벨명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

			<tr>
				<th>사용여부</th>
				<td >
					<div class="form-group">
						<label class="radio-inline">
							<input type="radio" name="USE_YN" value="Y"> Y
						</label>

						<label class="radio-inline">
							<input type="radio" name="USE_YN" value="N"> N
						</label>
						
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

			<tr>
				<th>정렬순서</th>
				<td >
					<div class="form-group">
						<input type="text" name="SORT_CHAR" class="form-control" placeholder="정수로 입력" required data-error="정렬순서를 입력하세요">
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
	var splitted_view_el_ = $("#pb-gcode-splitted-view");
	splitted_view_el_.pb_splitted_view({
		'master-info-load-action' : "pb-admin-gcode-load"
	});

	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-gcode-load",
		insert_action : "pb-admin-gcode-insert",
		update_action : "pb-admin-gcode-update",
		delete_action : "pb-admin-gcode-delete",

		before_insert : function(target_data_, callback_){

			PB.confirm({
				title : "추가확인",
				content : "공통코드를 추가하시겠습니까?",
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
				content : "공통코드를 삭제하시겠습니까?",
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
				$("#pb-gcode-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_updated : function(){
			PB.alert({
				title : "수정완료",
				content : "수정이 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_deleted : function(){
			PB.alert({
				title : "삭제완료",
				content : "삭제가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-cond-form").submit(); //리스트테이블 재검색
			});
		}
	});

	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-gcode-dtl-load",
		insert_action : "pb-admin-gcode-dtl-insert",
		update_action : "pb-admin-gcode-dtl-update",
		delete_action : "pb-admin-gcode-dtl-delete",

		before_insert : function(target_data_, callback_){

			PB.confirm({
				title : "추가확인",
				content : "상세코드를 추가하시겠습니까?",
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
				content : "상세코드를 삭제하시겠습니까?",
				button1 : "삭제하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));
		},

		after_empty_opened : function(form_el_){
			//추가팝업이 열렸을 때 이벤트, 
			form_el_.find("[name='CODE_ID']").val(splitted_view_el_.pb_splitted_view().current_matser_id());
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
				$("#pb-gcode-dtl-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_updated : function(){
			PB.alert({
				title : "수정완료",
				content : "수정이 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-dtl-cond-form").submit(); //리스트테이블 재검색
			});
		},
		after_deleted : function(){
			PB.alert({
				title : "삭제완료",
				content : "삭제가 완료되었습니다.",
				button1 : "확인"
			}, function(){
				$("#pb-gcode-dtl-cond-form").submit(); //리스트테이블 재검색
			});
		}
	});
});

function _pb_gcode_add(){
	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal();
		code_edit_form_module_.open_for_insert();
}
function _pb_gcode_edit(code_id_){
	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal();
		code_edit_form_module_.open_for_update(code_id_);
}
function _pb_gcode_remove(code_id_){
	var code_edit_form_module_ = $("#pb-gcode-edit-form").pb_edit_form_modal();
		code_edit_form_module_.do_delete(code_id_);
}

function _pb_gcode_dtl_add(){
	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal();
		code_dtl_edit_form_module_.open_for_insert();
}
function _pb_gcode_dtl_edit(code_id_,code_did_){
	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal();
		code_dtl_edit_form_module_.open_for_update([code_id_,code_did_]);
}
function _pb_gcode_dtl_remove(code_id_,code_did_){
	var code_dtl_edit_form_module_ = $("#pb-gcode-dtl-edit-form").pb_edit_form_modal();	
		code_dtl_edit_form_module_.do_delete([code_id_,code_did_]);
}
</script>