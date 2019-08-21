<?php

define('PB_VERSION', '1.1.5');

//check exists config file
if(!file_exists(dirname( __FILE__ )."/pb-config.php")){
	die("config file not found");
}

include(dirname( __FILE__ )."/pb-config.php");

define("PB_DOCUMENT_PATH", dirname( __FILE__ )."/");
define("PB_DOCUMENT_URL", (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH));

if(defined("PB_DEV") && PB_DEV){
	define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dev/");
	define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dev/");
}else{
	define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dist/");
	define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dist/");
}

?>