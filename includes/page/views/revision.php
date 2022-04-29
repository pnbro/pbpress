<?php 	
	
	global $pbpage, $pbpage_meta_map;

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-page/revision.css">
<?php 
	
pb_easy_splited_view("pb-page-revision-splitted-view", "pb-admin-page-revision-table", null, array(
	'master' => array(
		'loader' => 'pb-admin-page-revision-load',
		'header' => function(){ 
			global $pbpage;
		?>
			<h3><?=sprintf(__('%s 리비젼내역'), $pbpage['page_title'])?> </h3>
			<input type="hidden" name="page_id" value="<?=$pbpage['id']?>">
		<?php },
	),

	'detail' => array(
		'header' => function(){ ?>
			<h3><?=__('리비젼내용')?> 
				<div class="pull-right">
					<a href="javascript:pb_admin_page_restore();" class="btn btn-primary btn-sm"><?=__('이 버젼으로 복구')?></a>
				</div>
				<div class="clearfix"></div>
			</h3>

			<div class="revision-iframe-group" id="pb-revision-iframe-group"></div>
		<?php },
	),
	'placeholder' => __("좌측에서 리비젼을 선택하세요"),
));

?>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-page/revision.js"></script>