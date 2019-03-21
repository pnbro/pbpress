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

include_once(PB_DOCUMENT_PATH."includes/common/lib/UploadHandler.php");

$upload_dir_ = (isset($_GET['upload_dir']) && strlen($_GET['upload_dir'])) ? $_GET['upload_dir'] : "";
$upload_dir_ = '/'.trim($upload_dir_, '/');
$upload_dir_ = rtrim($upload_dir_, '/') . '/';	

$yyymmdd = new DateTime();
$yyymmdd_ = date_format($yyymmdd,"Ymd")."/";

$upload_path_ = PB_DOCUMENT_PATH."uploads/".$upload_dir_;
$upload_url_ = PB_DOCUMENT_URL."uploads/".$upload_dir_;

$upload_handler_ = new UploadHandler(array(
	'upload_dir' => $upload_path_.$yyymmdd_,
	'upload_url' => $upload_url_.$yyymmdd_,
	'print_response' => false,
));

$tmps_ = $upload_handler_->get_response();
$results_ = array();

foreach($tmps_['files'] as $file_){
	$thumbnail_url_ = isset($file_->thumbnailUrl) ? $file_->thumbnailUrl : null;
	$row_data_ = array(
		
		'size' => $file_->size,
		'type' => $file_->type,

		'upload_path' => $upload_dir_,
		
		'o_name' => $yyymmdd_.$file_->name,
		'r_name' => $yyymmdd_.pathinfo($file_->url, PATHINFO_BASENAME),
	);

	if(strlen($thumbnail_url_)){
		$row_data_['thumbnail'] = $yyymmdd_."/thumbnail/".pathinfo($thumbnail_url_, PATHINFO_BASENAME);
	}

	$results_[] = $row_data_;
}

echo json_encode(array(
	'success' => true,
	'files' => $results_,
));
pb_end();

?>