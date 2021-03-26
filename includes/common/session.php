<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

// include(PB_DOCUMENT_PATH . "includes/common/session.cors.php");
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

if(PHP_VERSION_ID < 70300){
	$cookie_save_path_ = '/;SameSite='.$pb_config->session_cookie_samesite();
	session_set_cookie_params(
		$pb_config->session_max_time(),
		$cookie_save_path_,
		$pb_config->session_cookie_domain(),
		$pb_config->session_cookie_secure(),
		$pb_config->session_cookie_httponly()
	);
}else{
	session_set_cookie_params(array(
		'lifetime' => $pb_config->session_max_time(),
		'path' => '/',
		'domain' => $pb_config->session_cookie_domain(),
		'samesite' => $pb_config->session_cookie_samesite(),
		'secure' => $pb_config->session_cookie_secure(),
		'httponly' => $pb_config->session_cookie_httponly(),
	));
}

register_shutdown_function('session_write_close');

@session_start();

class PBSession{
	function __construct(){
		global $pb_config;
	}

	function update($key_, $value_){
		$_SESSION[$key_] = $value_;
    	return $_SESSION[$key_];
	}
	function get($key_){
		if(!isset($_SESSION[$key_])) return null;
    	return $_SESSION[$key_];
	}
	function remove($key_){
		if(!isset($_SESSION[$key_])) return null;
	    unset($_SESSION[$key_]);
	    return $_SESSION;
	}


}

class PBCookie{
	private $_expire_time;
	function __construct(){
		global $pb_config;
		$this->_expire_time = $pb_config->session_max_time();		
	}
	function expire_time(){
		return $this->_expire_time;
	}

	function update($key_, $value_){
		return setcookie($key_, $value_, (time() + ($this->_expire_time * $expire_)), $path_);
	}
	function get($key_){
		return (isset($_COOKIE[$key_]) ? $_COOKIE[$key_] : null);
	}
	function remove($key_){
		return setcookie($key_, '', -1, $path_);
	}
}

global $pb_session, $pb_cookie;
	$pb_session = new PBSession();
	$pb_cookie = new PBCookie();

function pb_session_put($key_, $value_){
	global $pb_session;
	return $pb_session->update($key_, $value_);
}
function pb_session_get($key_){
	global $pb_session;
	return $pb_session->get($key_);
}
function pb_session_remove($key_){
	global $pb_session;
	return $pb_session->remove($key_);
}

function pb_cookie_put($key_, $value_){
	global $pb_cookie;
	return $pb_cookie->update($key_, $value_);
}
function pb_cookie_get($key_){
	global $pb_cookie;
	return $pb_cookie->get($key_);
}
function pb_cookie_remove($key_){
	global $pb_cookie;
	return $pb_cookie->remove($key_);
}

?>