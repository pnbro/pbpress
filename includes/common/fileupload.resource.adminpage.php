<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_authority_task_add_type('manage_resource', array(
	'name' => __('리소스관리'),
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_resource");

pb_authority_task_add_type('manage_fileupload_resource', array(
	'name' => __('리소스업로드관리'),
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_fileupload_resource");


?>