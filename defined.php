<?php

define('__PB_VERSION', '7.9.3');
define('__PB_SCRIPT_VERSION', '7.9.3');

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

define('PB_HTTPS', $https_);

define("PB_DOCUMENT_PATH", str_replace(DIRECTORY_SEPARATOR, "/",  dirname( __FILE__ ))."/");
define("PB_DOCUMENT_DOMAIN", rtrim($_SERVER['HTTP_HOST'] . str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH), "/"));
define("PB_DOCUMENT_URL", ($https_ ? 'https' : 'http') . '://' . PB_DOCUMENT_DOMAIN.'/');

include(dirname( __FILE__ )."/pb-config.php");

//웹서버 종류: apache(기본, .htaccess 사용) 또는 nginx(서버 설정 파일에 rewrite 규칙 직접 작성, nginx.conf.sample 참고)
if(!defined('PB_SERVER_TYPE')){
	define('PB_SERVER_TYPE', 'apache');
}

if(defined("PB_DEV") && PB_DEV){
	define('PB_VERSION', __PB_VERSION."_".time());
	define('PB_SCRIPT_VERSION', __PB_SCRIPT_VERSION."_".time());
	define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dev/");
	define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dev/");

}else{
	define('PB_VERSION', __PB_VERSION);
	define('PB_SCRIPT_VERSION', __PB_SCRIPT_VERSION);
	define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dist/");
	define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dist/");
}

if(!defined('OPENSSL_CONF_PATH')){
	define('OPENSSL_CONF_PATH', null);
}

?>
