<?php

require(dirname( __FILE__ ) . '/../defined.php');
require(PB_DOCUMENT_PATH . 'includes/includes.php');
include(PB_DOCUMENT_PATH . "admin/admin-hook.php");

header("Content-Type: text/html; CharSet=utf-8");

function pb_admin_head(){

	$pbvar_ = pb_hook_apply_filters('pb-admin-head-pbvar', array());

	global $pb_config;
	?>

<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pb-admin.css">

<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/jquery.js"></script>
<script type="text/javascript">window.PBVAR = <?=json_encode($pbvar_)?>;</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>comp-lib/all-admin.js"></script>

<?php if($pb_config->is_devmode()){ ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pb-dev.js"></script>
<?php }

pb_hook_do_action("pb_admin_head");	
}

function pb_admin_foot(){
	pb_hook_do_action("pb_admin_foot");
}

function pb_admin_end(){
	pb_hook_do_action('pb_admin_ended');
	exit;
}

?>