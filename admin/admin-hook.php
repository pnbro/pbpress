<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_hook_add_action("pb_admin_head","_pb_crypt_load_scripts");
pb_hook_add_action('pb_admin_ended', "_pb_database_close_hook");

pb_easytable_update_default_loading_indicator("<div class='pb-indicator-frame small'><div class='lds-spin'><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div></div>");

pb_easylist_update_default_loading_indicator("<div class='pb-indicator-frame'><div class='lds-spin'><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div></div>");


?>