<?

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

define('_PB_SESSION_INSTANCE_CHIP_MAP_', "_PB_SESSION_INSTANCE_CHIP_");

function pb_session_instance_token($name_){
    $session_token_maps_ = pb_session_get(_PB_SESSION_INSTANCE_CHIP_MAP_);
    if(!isset($session_token_maps_)) $session_token_maps_ = array();
    $session_token_maps_[$name_] = pb_crypt_hash(pb_random_string(10));
    pb_session_put(_PB_SESSION_INSTANCE_CHIP_MAP_,$session_token_maps_);
    return $session_token_maps_[$name_];
}
function pb_session_verify_instance_token($name_, $tokenped_text_){
    $session_token_maps_ = pb_session_get(_PB_SESSION_INSTANCE_CHIP_MAP_);
    if(!isset($session_token_maps_) || !isset($session_token_maps_[$name_])) return false;
    $bool_ = ($session_token_maps_[$name_] === $tokenped_text_);
    return $bool_;
}

?>