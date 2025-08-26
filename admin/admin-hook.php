<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_hook_add_action("pb_admin_head","_pb_crypt_load_scripts");

pb_easytable_update_default_loading_indicator("<div class='pb-indicator-frame small'><div class='pb-loading-spinner'></div></div>");
pb_easylist_update_default_loading_indicator("<div class='pb-indicator-frame small'><div class='pb-loading-spinner'></div></div>");


?>