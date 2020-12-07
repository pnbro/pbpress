<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

include(PB_DOCUMENT_PATH . "includes/common/session.handler.php");

global $pb_config, $pb_session_manager;

$session_manager_class_ = $pb_config->session_manager();

if($session_manager_class_ === "default"){
	$pb_session_manager = new PBSessionHandlerFile();
}else if($session_manager_class_ === "database"){
	include(PB_DOCUMENT_PATH . "includes/common/session.handler.database.php");
	$pb_session_manager = new PBSessionHandlerDatabase();
}else if(strlen($session_manager_class_)){
	if(!class_exists($session_manager_class_)){
		die("session handler not found : ".$session_manager_class_);
	}
	$pb_session_manager = new $session_manager_class_;
}else{
	$pb_session_manager = new PBSessionHandlerFile();
}

session_set_save_handler($pb_session_manager);

ini_set('session.gc_maxlifetime', $pb_config->session_max_time());
ini_set('session.save_path',$pb_config->session_save_path());

function pb_session_put($key_, $value_){
    $_SESSION[$key_] = $value_;
    return $_SESSION[$key_];
}
function pb_session_get($key_){
    if(!isset($_SESSION[$key_])) return null;
    return $_SESSION[$key_];
}
function pb_session_remove($key_){
    if(!isset($_SESSION[$key_])) return null;
    unset($_SESSION[$key_]);
    return $_SESSION;
}

define("PB_COOKIE_DAY", $pb_config->session_max_time());

function pb_cookie_put($key_, $value_, $expire_ = 7, $path_ = "/"){
    return setcookie($key_, $value_, (time() + (PB_COOKIE_DAY * $expire_)), $path_);
}
function pb_cookie_get($key_){
    return (isset($_COOKIE[$key_]) ? $_COOKIE[$key_] : null);
}
function pb_cookie_remove($key_, $path_ = "/"){
    return setcookie($key_, '', -1, $path_);
}

@session_start();

?>