<?php 	


pb_easy_splited_view("pb-authority-splitted-view","pb-admin-authority-table", "pb-admin-authority-task-table", array(
	'master' => array(
		'loader' => 'pb-admin-authority-load',
		'header' => function(){ ?>
			<h3>권한내역 <a href="javascript:_pb_authority_add();" class="btn btn-primary btn-sm">권한추가</a></h3>
			
			<div class="pb-easytable-conditions">
				<div class="right-frame">	
					<div class="input-group">
						<input type="text" class="form-control" placeholder="권한명" name="keyword" >
						<span class="input-group-btn">
							<button type="submit" class="btn btn-default" type="button">검색하기</button>
						</span>
					</div>
				</div>
			</div>
		<?php },

		'preview_html' => function(){ ?>
			<h3>권한정보 </h3>
			<table class="pb-form-table master-info-table">
				<tbody>
					<tr>
						<th>고유번호</th>
						<td>{{id}}</td>
						<th>권한명</th>
						<td>{{auth_name}}</td>
						<th>슬러그</th>
						<td>{{slug}}</td>
					</tr>
				
				</tbody>
			</table>
		<?php }
	),

	'detail' => array(
		'header' => function(){ ?>
			<h3>권한별 작업내역
				<div class="pull-right">
					<a href="javascript:pb_manage_authority_task_update();" class="btn btn-primary">변경사항 저장</a>
				</div>
				<div class="clearfix"></div>
			</h3>
		<?php },
	),
	'placeholder' => "좌측에서 권한을 선택하세요",
));

?>

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