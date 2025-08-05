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
		'error_title' =>__("업로드실패"),
		'error_message' => __("업로드 파일이 비어있습니다."),
	));
	pb_end();
}

$thumbnail_size_ = _POST('thumbnail_size');
$chunk_current_ = _POST('chunk_current');
$chunk_length_ = _POST('chunk_length');
$chunk_size_ = _POST('chunk_size');
$r_name_ = _POST('r_name');
$o_name_ = _POST('o_name');
$chunk_ = $_FILES['chunk'];

$results_ = pb_fileupload_handle_chunk($chunk_, array(
	'chunk_current' => $chunk_current_,
	'chunk_length' => $chunk_length_,
	'chunk_size' => $chunk_size_,
	'r_name' => $r_name_,
	'o_name' => $o_name_,
), array(
	'thumbnail_size' => $thumbnail_size_,
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
	'results' => $results_,
));
pb_end();

?>