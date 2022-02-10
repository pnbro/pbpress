<?php

define('PB_VERSION', '2.2.1');
define('PB_SCRIPT_VERSION', '1.36.0');

//check exists config file
if(!file_exists(dirname( __FILE__ )."/pb-config.php")){
	die("config file not found");
}

$https_ = false;
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
	$https_ = true;
}else if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'){
	$https_ = true;
}

define("PB_DOCUMENT_PATH", dirname( __FILE__ )."/");
define("PB_DOCUMENT_URL", ($https_ ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH));

include(dirname( __FILE__ )."/pb-config.php");

if(defined("PB_DEV") && PB_DEV){
	define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dev/");
	define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dev/");
}else{
	define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dist/");
	define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dist/");
}

?>