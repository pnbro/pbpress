<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


define('PB_REQUEST_TOKEN_CRYPT_SHARED_KEY',"_PB_REQUEST_TOKEN_CRYPT_SHARED_KEY_");

function _pb_request_token_shared_keys(){
	$shared_key_ = pb_session_get(PB_REQUEST_TOKEN_CRYPT_SHARED_KEY);

	if(isset($shared_key_)){
		return $shared_key_;
	}

	global $pb_config;

	$key_ = openssl_pkey_new(array(
		'digest_alg' => 'sha256',
		'private_key_bits' => 512,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	));

	$private_key_ = null;
	openssl_pkey_export($key_, $private_key_, $pb_config->crypt_password);

	$public_key_ = openssl_pkey_get_details($key_);
	$public_key_ = $public_key_['key'];
	
	$matches_ = array();
    
	$shared_key_ = array(
		'private_key' => $private_key_,
		'public_key' => $public_key_,
	);

	pb_session_put(PB_REQUEST_TOKEN_CRYPT_SHARED_KEY, $shared_key_);
	return $shared_key_;
}

function _pb_request_token_encrypt($plaintext_){
	$shared_key_ = _pb_request_token_shared_keys();
	return pb_crypt_encrypt($plaintext_, $shared_key_['public_key']);
}

function _pb_request_token_decrypt($ciphertext_, $private_key_ = null, $password_ = null){
	$shared_key_ = _pb_request_token_shared_keys();
	return pb_crypt_decrypt($ciphertext_, $shared_key_['private_key']);
}


function pb_request_token($name_){
    return urlencode(_pb_request_token_encrypt($name_));
}
function pb_verify_request_token($name_, $chiper_){
    $plain_text_ = _pb_request_token_decrypt(urldecode($chiper_));
    if($plain_text_ === FALSE) return false;
    return ($plain_text_ === $name_);
}
?>