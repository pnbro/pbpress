<?php 	

	$user_listtable_ = pb_easytable("pb-user-listtable");

	$page_index_ = isset($_GET['page_index']) ? $_GET['page_index'] : 0;
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;

?>

<h3>사용자내역</h3>

<form method="GET" id="pb-user-listtable-form">

	<div class="pb-easytable-group">
		<div class="left-frame">
			<a href="<?=pb_admin_url("manage-user/add")?>" class="btn btn-default">사용자 추가</a>
		</div>
		<div class="right-frame">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="통합검색" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button">검색하기</button>
				</span>
			</div>
		</div>
	</div>	
	<?php 
		$user_listtable_->display($page_index_);
	?>
</form>
