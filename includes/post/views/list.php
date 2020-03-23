<?php 	

	$post_table_ = pb_easytable("pb-admin-post-table");

	$post_index_ = isset($_GET['post_index']) ? $_GET['post_index'] : 0;
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;
	$status_ = isset($_GET['search_status']) ? $_GET['search_status'] : null;

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/posts/admin/manage-post/list.css">
<h3>글내역</h3>

<form method="GET" class="pb-easytable-group" id="pb-easytable-table-form">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<a href="<?=pb_admin_url("manage-post/add")?>" class="btn btn-default">글 추가</a>
		</div>
		<div class="right-frame">
			<div class="form-group">
				<select class="form-control" name="search_status">
					<option value="">-등록상태-</option>
					<?= pb_gcode_make_options(array("code_id" => "PST01"), $status_); ?>
				</select>
			</div>
			<div class="input-group">
				<input type="text" class="form-control" placeholder="통합검색" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button">검색하기</button>
				</span>
			</div>
		</div>
	</div>

	<?php 
		$post_table_->display($post_index_);
	?>
		
</form>	
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/posts/admin/manage-post/list.js"></script>