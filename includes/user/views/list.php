<?php 	

	$user_listable_ = new PB_user_list_table("pb-user-listtable", "pb-user-listtable");
?>

<h3>사용자내역</h3>

<form method="GET" class="pb-listtable-cond-form" id="pb-user-cond-form" data-master-cond-form>
	<div class="left-frame">
		<a href="<?=pb_admin_url("manage-user/add")?>" class="btn btn-default">사용자 추가</a>
	</div>
	<div class="right-frame">
		<input type="hidden" name="page_index" value="0">
		<div class="input-group">
			<input type="text" class="form-control" placeholder="통합검색" name="keyword" >
			<span class="input-group-btn">
				<button type="submit" class="btn btn-default" type="button">검색하기</button>
			</span>
		</div>
	</div>
</form>	

<form method="GET" data-ref-conditions-form="#pb-user-cond-form" data-master-listtable-form>
	<input type="hidden" name="keyword">
<?php 
	
	echo $user_listable_->html();
?>
</form>
