<?php

function pb_admin_head(){

	$pbvar_ = pb_hook_apply_filters('pb-admin-head-pbvar', array());

	global $pb_config;
	?>

<link rel="stylesheet" type="text/css" href="<?=pb_hook_apply_filters('adminpage-default-css', PB_LIBRARY_URL."css/pb-admin.css?v=".PB_SCRIPT_VERSION)?>">

<script type="text/javascript" src="<?=pb_hook_apply_filters('adminpage-default-jquery', PB_LIBRARY_URL."js/jquery.js?v=".PB_SCRIPT_VERSION)?>"></script>
<script type="text/javascript">window.PBVAR = <?=json_encode($pbvar_)?>;</script>
<script type="text/javascript" src="<?=pb_hook_apply_filters('adminpage-default-js', PB_LIBRARY_URL."comp-lib/all-admin.js?v=".PB_SCRIPT_VERSION)?>"></script>

<?php if($pb_config->is_devmode()){ ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pb-dev.js?v=<?=PB_SCRIPT_VERSION?>"></script>
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