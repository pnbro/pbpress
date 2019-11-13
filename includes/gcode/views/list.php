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
							<td data-column="code_id"></td>
							<th>코드명</th>
							<td data-column="code_nm"></td>
							<th>사용여부</th>
							<td data-column="use_yn"></td>
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
						<input type="text" name="code_id" class="form-control" placeholder="코드 입력" required data-error="코드를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

				</td>
				
			</tr>
			<tr>
				<th>코드명</th>
				<td>
					<div class="form-group">
						<input type="text" name="code_nm" class="form-control" placeholder="코드명 입력" required data-error="레벨명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

			<tr>
				<th>여분값명1</th>
				<td>
					<div class="form-group">
						<input type="text" name="col1" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th>여분값명2</th>
				<td>
					<div class="form-group">
						<input type="text" name="col2" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th>여분값명3</th>
				<td>
					<div class="form-group">
						<input type="text" name="col3" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th>여분값명4</th>
				<td>
					<div class="form-group">
						<input type="text" name="col4" class="form-control">
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
							<input type="radio" name="use_yn" value="Y"> Y
						</label>

						<label class="radio-inline">
							<input type="radio" name="use_yn" value="N"> N
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
	<input type="hidden" name="code_id">
	<table class="pb-form-table " >
		<tbody>
			<tr>
				<th>상세코드ID</th>
				<td>
					<div class="form-group">
						<input type="text" name="code_did" class="form-control" placeholder="코드 입력" required data-error="코드를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

				</td>
				
			</tr>
			<tr>
				<th>상세코드명</th>
				<td>
					<div class="form-group">
						<input type="text" name="code_dnm" class="form-control" placeholder="코드명 입력" required data-error="레벨명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

			<tr data-extra-col="col1">
				<th data-column="col1_title"></th>
				<td>
					<div class="form-group">
						<input type="text" name="col1" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr data-extra-col="col2">
				<th data-column="col2_title"></th>
				<td>
					<div class="form-group">
						<input type="text" name="col2" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr data-extra-col="col3">
				<th data-column="col3_title"></th>
				<td>
					<div class="form-group">
						<input type="text" name="col3" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr data-extra-col="col4">
				<th data-column="col4_title"></th>
				<td>
					<div class="form-group">
						<input type="text" name="col4" class="form-control">
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
							<input type="radio" name="use_yn" value="Y"> Y
						</label>

						<label class="radio-inline">
							<input type="radio" name="use_yn" value="N"> N
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
						<input type="text" name="sort_char" class="form-control" placeholder="정수로 입력" required data-error="정렬순서를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			
		</tbody>
	</table>
</form>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/gcode/list.js"></script>