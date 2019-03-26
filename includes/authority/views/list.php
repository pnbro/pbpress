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
							<td data-column="id"></td>
							<th>권한명</th>
							<td data-column="auth_name"></td>
							<th>슬러그</th>
							<td data-column="slug"></td>
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
						<input type="text" name="auth_name" class="form-control" placeholder="권한명 입력" required data-error="권한명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th>슬러그</th>
				<td>
					<div class="form-group">
						<input type="text" name="slug" class="form-control" placeholder="슬러그 입력" required data-error="슬러그를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

				</td>
				
			</tr>
			
		</tbody>
	</table>
</form>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/authority/list.js"></script>