<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/container.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/row.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/text.php');
include(PB_DOCUMENT_PATH . 'includes/page-builder/elements/image.php');

function _pb_page_builder_admin_initialize_for_common(){
	
?>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/page-builder/editor/common.js"></script>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/common.css">
<?php
}
pb_hook_add_action("pb_page_builder_admin_initialize", "_pb_page_builder_admin_initialize_for_common");

function _pb_page_builder_element_library_for_common(){
	?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/common.css">
	<?php
}
pb_hook_add_action("pb_head", "_pb_page_builder_element_library_for_common");

?>