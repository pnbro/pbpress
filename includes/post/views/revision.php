<?php 	
	
	global $pbpost, $pbpost_meta_map;

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/posts/admin/manage-post/revision.css">
<?php 
	
pb_easy_splited_view("pb-post-revision-splitted-view", "pb-admin-post-revision-table", null, array(
	'master' => array(
		'loader' => 'pb-admin-post-revision-load',
		'header' => function(){ 
			global $pbpost;
		?>
			<h3><?=$pbpost['post_title']?> 리비젼내역 </h3>
			<input type="hidden" name="post_id" value="<?=$pbpost['id']?>">
		<?php },
	),

	'detail' => array(
		'header' => function(){ ?>
			<h3>리비젼내용 
				<div class="pull-right">
					<a href="javascript:pb_admin_post_restore();" class="btn btn-primary btn-sm">이 버젼으로 복구</a>
				</div>
				<div class="clearfix"></div>
			</h3>

			<div class="revision-iframe-group" id="pb-revision-iframe-group"></div>
		<?php },
	),
	'placeholder' => "좌측에서 리비젼을 선택하세요",
));

?>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/posts/admin/manage-post/revision.js"></script>