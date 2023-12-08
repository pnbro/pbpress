<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_add_ajax('admin-fileuploader-load-resource-modal', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_resource") && !pb_user_has_authority_task(pb_current_user_id(), "manage_fileupload_resource")){
		pb_ajax_error(__("잘못된 요청"), __("권한이 없습니다."));
	}
	
	ob_start();

	$modal_html_ = ob_get_clean();

	pb_ajax_success(array(
		'modal_html' => $modal_html_,
	));
});

pb_hook_add_filter('pb_adminpage_list', function($results_){
	$results_['dev001'] = array(
		'name' => __('업로드테스트'),
		'type' => 'menu',
		// 'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."dev/dev001.php",
		// 'authority_task' => 'manage_authority',
		'subpath' => null,
		'sort' => 999,
	);
	return $results_;
});

?>