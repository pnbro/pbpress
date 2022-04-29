<?php 	

	$page_table_ = pb_easytable("pb-admin-page-table");

	$page_index_ = _GET('page_index', 0, PB_PARAM_INT);
	$keyword_ = _GET('keyword');
	$status_ = _GET('search_status');

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-page/list.css">
<h3><?=__('페이지내역')?></h3>

<form method="GET" class="pb-easytable-group" id="pb-easytable-table-form">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<a href="<?=pb_admin_url("manage-page/add")?>" class="btn btn-default"><?=__('페이지 추가')?></a>
		</div>
		<div class="right-frame">
			<div class="form-group">
				<select class="form-control" name="search_status">
					<option value=""><?=__('-등록상태-')?></option>
					<?=PB_PAGE_STATUS::make_options($status_)?>
				</select>
			</div>
			<div class="input-group">
				<input type="text" class="form-control" placeholder="<?=__('통합검색')?>" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button"><?=__('검색하기')?></button>
				</span>
			</div>
		</div>
	</div>

	<?php 
		$page_table_->display($page_index_);
	?>
		
</form>	
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-page/list.js"></script>