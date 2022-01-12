<?php 	

	pb_hook_add_filter('pb_page_builder_element_render_function', function($render_func_, $data_, $content_){
		return '_pb_page_live_edit_recurs_render';
	});
	
	global $pbpage, $pbpage_meta_map;
	$is_new_ = !isset($pbpage);

	if($is_new_){
		$pbpage = array(
			'id' => null,
			'page_title' => null,
			'page_html' => null,
			'status' => PB_PAGE_STATUS::PUBLISHED,
			'slug' => null,
		);
		$pbpage_meta_map = array();
	}

	$element_map_ = pb_page_builder_elements();
	$builder_data_ = pb_page_builder_parse_xml($pbpage['page_html']);
	$settings_ = $builder_data_['settings'];

	function _pb_page_live_edit_recurs_render($data_, $content_){
		global $pb_page_builder_element_classes;
		$element_class_ = $pb_page_builder_element_classes[$data_['name']];

		ob_start();
		call_user_func_array(array($element_class_, "render"), array($data_, $content_));	

		$rendered_html_ = ob_get_clean();
		$rendered_html_ = trim($rendered_html_);
		echo '<div data-live-edit-group="'.pb_random_string(5).'">'.$rendered_html_.'</div>';
	}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<?php pb_head(); ?>
</head>
<body>
<style type="text/css"><?=pb_hook_apply_filters('pb_page_builder_global_style',$settings_['style'], $builder_data_)?></style>
<script type="text/javascript">
window.pb_page_builder_version = "<?=PB_PAGE_BUILDER_VERSION?>";
window.pbpage_builder_element_map = <?=json_encode($element_map_)?>;
</script>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-live-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">
<script type="text/xmldata" id="page-builder-xml-data"><?php pb_page_builder_render($builder_data_); ?></script>
<div class="pb-page-live-builder" id="pb-page-live-builder">
	
	<div class="page-builder-navbar"><div class="wrap">
		<div class="col-left">
			<img src="<?=PB_LIBRARY_URL?>img/page-builder/icon.png" class="logo-image">
		</div>
		<div class="col-right">
			<a href="" data-element-add-element-btn class="btn btn-default add-element-btn"><i class="icon material-icons">add_circle</i>요소추가</a>
			<a href="" data-element-setting-btn class="icon-link page-settings-btn"><i class="icon material-icons">settings</i></a>
			<a href="" data-fullscreen-btn class="icon-link fullscreen-btn">
				<i class="icon material-icons on">fullscreen</i>
				<i class="icon material-icons off">fullscreen_exit</i>
			</a>

		</div>
	</div></div>
</div>
<script type="text/javascript"><?=pb_hook_apply_filters('pb_page_builder_global_script',$settings_['script'], $builder_data_)?></script>
<script type="text/javascript">
jQuery(document).ready(function(){
	window._page_live_builder = $("#pb-page-live-builder");
	window._page_live_edit_groups = {};

	var target_html_el_ = $($("#page-builder-xml-data").html());
		
	var live_editor_groups_ = target_html_el_.find("[data-live-edit-group]");
		live_editor_groups_.each(function(){
			var target_group_el_ = $(this);
			var live_group_id_ = target_group_el_.attr('data-live-edit-group');

			var child_el_ = target_group_el_.children();
				child_el_.detach();

			target_group_el_.after(child_el_);
			target_group_el_.remove();

		});

	$("body").append(target_html_el_);

});
</script>

</body>
</html>