<?php 	
	$page_content_ = _POST('page_content');
		
	pb_theme_header();

	$parsed_data_ = pb_page_builder_parse_xml($page_content_);

	if(!pb_is_error($parsed_data_)){
		pb_page_builder_render($parsed_data_);
	}
	
?>
<script type="text/javascript">
jQuery(document).click("click", "*", function(){return false;});
</script>
<?php pb_theme_footer(); ?>