<?php

pb_easy_splited_view("pb-gcode-splitted-view", "pb-admin-gcode-table", "pb-admin-gcode-dtl-table", array(
	'master' => array(
		'loader' => 'pb-admin-gcode-load',
		'header' => function(){ ?>
			<h3><?=__('공통코드내역')?> <a href="javascript:_pb_gcode_add();" class="btn btn-primary btn-sm"><?=__('코드추가')?></a></h3>
			<div class="pb-easytable-conditions">
				<div class="right-frame">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="<?=__('코드명')?>" name="keyword" >
						<span class="input-group-btn">
							<button type="submit" class="btn btn-default" type="button"><?=__('검색하기')?></button>
						</span>
					</div>
				</div>
			</div>
		<?php },

		'preview_html' => function(){ ?>
			<h3><?=__('코드정보')?></h3>
			<table class="pb-form-table">
				<tbody>
					<tr>
						<th><?=__('ID')?></th>
						<td>{{code_id}}</td>
						<th><?=__('코드명')?></th>
						<td>{{code_nm}}</td>
						<th><?=__('사용여부')?></th>
						<td>{{use_yn}}</td>
					</tr>
				
				</tbody>
			</table>
		<?php }
	),

	'detail' => array(
		'header' => function(){ ?>
			<h3><?=__('상세코드내역')?> <a href="javascript:_pb_gcode_dtl_add();" class="btn btn-primary btn-sm"><?=__('상세추가')?></a></h3>
		<?php },
	),
	'placeholder' => "좌측에서 공통코드를 선택하세요",
));

?>
<?php pb_hook_do_action('pb-admin-gcode-edit-page-before'); ?>
<form id="pb-gcode-edit-form">
	<?php pb_hook_do_action('pb-admin-gcode-edit-form-before'); ?>
	<table class="pb-form-table " >
		<tbody>
			<?php pb_hook_do_action('pb-admin-gcode-edit-form-tr-before'); ?>
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
						<input type="text" name="code_nm" class="form-control" placeholder="코드명 입력" required data-error="코드명을 입력하세요">
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
			<?php pb_hook_do_action('pb-admin-gcode-edit-form-tr-after'); ?>
		</tbody>
	</table>
	<?php pb_hook_do_action('pb-admin-gcode-edit-form-after'); ?>
</form>


<form id="pb-gcode-dtl-edit-form">
	<?php pb_hook_do_action('pb-admin-gcode-dtl-edit-form-before'); ?>
	<input type="hidden" name="code_id">
	<table class="pb-form-table " >
		<tbody>
			<?php pb_hook_do_action('pb-admin-gcode-dtl-edit-form-tr-before'); ?>
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
						<input type="text" name="code_dnm" class="form-control" placeholder="코드명 입력" required data-error="상세코드명을 입력하세요">
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
			<?php pb_hook_do_action('pb-admin-gcode-dtl-edit-form-tr-after'); ?>
			
		</tbody>
	</table>
	<?php pb_hook_do_action('pb-admin-gcode-dtl-edit-form-after'); ?>
</form>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/gcode/list.js"></script>
<?php pb_hook_do_action('pb-admin-gcode-edit-page-after'); ?>