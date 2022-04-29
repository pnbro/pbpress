<?php 	

	$user_listtable_ = pb_easytable("pb-user-listtable");

	$page_index_ = _GET('page_index', 0, PB_PARAM_INT);
	$keyword_ = _GET('keyword');
?>

<h3><?=__('사용자내역')?></h3>

<form method="GET" id="pb-user-listtable-form" class="pb-easytable-group">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<a href="<?=pb_admin_url("manage-user/add")?>" class="btn btn-default"><?=__('사용자 추가')?></a>
		</div>
		<div class="right-frame">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="<?=__('통합검색')?>" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button"><?=__('검색하기')?></button>
				</span>
			</div>
		</div>
	</div>	
	<?php $user_listtable_->display($page_index_); ?>
</form>
