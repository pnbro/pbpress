<?php 	

	$page_listable_ = new PB_page_list_table("pb-user-listtable", "pb-user-listtable");

	$status_ = isset($_GET['status']) ? $_GET['status'] : null;
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-page/list.css">
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-page/list.js"></script>
<h3>사용자내역</h3>

<form method="GET" class="pb-listtable-cond-form" id="pb-page-cond-form" data-master-cond-form>
	<div class="left-frame">
		<a href="<?=pb_admin_url("manage-page/add")?>" class="btn btn-default">페이지 추가</a>
	</div>
	<div class="right-frame">
		<input type="hidden" name="page_index" value="0">
		<div class="form-group">
			<select class="form-control" name="status">
				<option value="">-등록상태-</option>
				<?= pb_gcode_make_options(array("code_id" => "PAG01"), $status_); ?>
			</select>
		</div>
		<div class="input-group">
			<input type="text" class="form-control" placeholder="통합검색" name="keyword" value="<?=$keyword_?>">
			<span class="input-group-btn">
				<button type="submit" class="btn btn-default" type="button">검색하기</button>
			</span>
		</div>
	</div>
</form>	

<form method="GET" data-ref-conditions-form="#pb-page-cond-form" data-master-listtable-form id="pb-page-littable">
	<input type="hidden" name="status">
	<input type="hidden" name="keyword">
<?php 
	
	echo $page_listable_->html();
?>
</form>
<script type="text/javascript">
	function pb_manage_page_remove(page_id_){
	PB.confirm({
		title : "삭제확인",
		content : "해당 페이지를 삭제합니다. 계속하시겠습니까?",
		button1 : "삭제하기",
	}, function(c_){
		if(!c_) return;
		_pb_manage_page_remove(page_id_);
	});
}

function _pb_manage_page_remove(page_id_){
	PB.post("delete-page", {
		page_id : page_id_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지 삭제 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : "페이지가 삭제되었습니다.",
		}, function(){
			location.reload();
		});

	}, true);
}
</script>