<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

if(!pb_hook_apply_filters('pb_fileupload_before', true)){
	echo json_encode(array(
		'success' => false,
		'deny' => true,
	));
	pb_end();
}

if(empty($_FILES)){
	echo json_encode(array(
		'success' => false,
		'error_title' =>"업로드실패",
		'error_message' =>"업로드 파일이 비어있습니다.",
	));
	pb_end();
}

$upload_dir_ = _GET('upload_dir');

$results_ = array();
$files_ = $_FILES['files'];

$results_ = pb_fileupload_handle($files_, array(
	'upload_dir' => $upload_dir_,
));

if(pb_is_error($results_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => $results_->error_title(),
		'error_message' => $results_->error_message(),
	));
	pb_end();
}

echo json_encode(array(
	'success' => true,
	'files' => $results_,
));
pb_end();

?>