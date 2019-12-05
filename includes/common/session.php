<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

if(!session_id()){
    @session_start();
}

function pb_session_put($key_, $value_){
    if(!session_id()){
        @session_start();
    }
    $_SESSION[$key_] = $value_;
    return $_SESSION[$key_];
}
function pb_session_get($key_){
    if(!session_id()){
        @session_start();
    }
    if(!isset($_SESSION[$key_])) return null;
    return $_SESSION[$key_];
}
function pb_session_remove($key_){
    if(!session_id()){
        @session_start();
    }
    if(!isset($_SESSION[$key_])) return null;
    unset($_SESSION[$key_]);
    return $_SESSION;
}

define("PB_COOKIE_DAY", 86400);

function pb_cookie_put($key_, $value_, $expire_ = 7, $path_ = "/"){
    return setcookie($key_, $value_, (time() + (PB_COOKIE_DAY * $expire_)), $path_);
}
function pb_cookie_get($key_){
    return (isset($_COOKIE[$key_]) ? $_COOKIE[$key_] : null);
}
function pb_cookie_remove($key_, $path_ = "/"){
    return setcookie($key_, '', -1, $path_);
}

?>