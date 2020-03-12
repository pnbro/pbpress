<?php 	

	$page_index_ = isset($_GET['page_index']) ? $_GET['page_index'] : 0;
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;
	
	$statement_ = pb_user_statement(array(
		"keyword" => $keyword_,
	));

	$user_listtable_ = pb_database_ss_table("pb-user-listtable", $statement_, pb_hook_apply_filters("pb_adminpage_user_listtable_data", array(
		'seq' => array(
			'name' => "",
			'class' => "col-seq text-center",
		),
		'user_name' => array(
			'name' => "사용자명",
			'class' => "col-2",
		),
		'user_login' => array(
			'name' => "id",
			'class' => "col-2 text-center",
		),
		'user_email' => array(
			'name' => "이메일",
			'class' => "col-2 text-center hidden-xs",
		),
		'status' => array(
			'name' => "상태",
			'class' => "col-2 text-center",
			'render' => function($listtable_, $item_, $page_index_){
				echo $item_['status_name'];
			}
		),
		'button_area' => array(
			'name' => "",
			'class' => "col-4 text-right",
			'render' => function($listtable_, $item_, $page_index_){
				?>

				<a href="<?=pb_admin_url("manage-user/edit/".$item_['id'])?>" class="btn btn-default">수정</a>

				<?php
			}
		),
	)), pb_hook_apply_filters("pb_adminpage_user_listtable_options", array(
		"no_rowdata" => "조회된 사용자가 없습니다.",
		'per_page' => 15,
		'class' => 'table table-hover table-striped pb-listtable pb-user-listtable',
	)));


?>

<h3>사용자내역</h3>

<form method="GET" class="pb-listtable-cond-form" id="pb-user-cond-form" data-master-cond-form>
	<div class="left-frame">
		<a href="<?=pb_admin_url("manage-user/add")?>" class="btn btn-default">사용자 추가</a>
	</div>
	<div class="right-frame">
		<input type="hidden" name="page_index" value="0">
		<div class="input-group">
			<input type="text" class="form-control" placeholder="통합검색" name="keyword" value="<?=$keyword_?>">
			<span class="input-group-btn">
				<button type="submit" class="btn btn-default" type="button">검색하기</button>
			</span>
		</div>
	</div>
</form>	

<form method="GET" data-ref-conditions-form="#pb-user-cond-form" data-master-listtable-form>
	<input type="hidden" name="keyword">
<?php 
	$user_listtable_->display($page_index_);
?>
</form>
