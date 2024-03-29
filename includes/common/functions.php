<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_home_url($sub_path_ = "", $params_ = array()){
	return pb_hook_apply_filters('pb_home_url', pb_make_url(pb_append_url(PB_DOCUMENT_URL, $sub_path_), $params_), $sub_path_, $params_);
}
function _pb_home_url_add_to_header_pbvar($results_){
	$results_['home_url'] = pb_home_url();
	return $results_;
};
pb_hook_add_filter('pb-admin-head-pbvar', "_pb_home_url_add_to_header_pbvar");
pb_hook_add_filter('pb-head-pbvar', "_pb_home_url_add_to_header_pbvar");

function pb_admin_url($sub_path_ = "", $params_ = array()){
	return pb_make_url(pb_append_url(PB_DOCUMENT_URL."admin/", $sub_path_), $params_);
}

function pb_admin_login_url($redirect_url_ = null){
	$adminpage_login_url_ = pb_hook_apply_filters("pb_admin_login_url", pb_home_url("admin/login.php"));

	if(strlen($redirect_url_)){
		$adminpage_login_url_ = pb_make_url($adminpage_login_url_, array(
			"redirect_url" => $redirect_url_,
		));
	}

	return $adminpage_login_url_;

}
function pb_admin_lgout_url($redirect_url_ = null){
	$admin_login_url_ = pb_admin_url("logout.php");
	return pb_make_url($admin_login_url_, array("redirect_url" => $redirect_url_));
}

function pb_redirect($redirect_url_ = null){
	header('Location: '.$redirect_url_);
}
function pb_redirect_404(){
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
	include(PB_DOCUMENT_PATH."404.php");
}

function pb_redirect_error($code_, $message_ = null, $title_ = "ERROR!"){
	if(pb_is_error($code_)){
		$message_ = $code_->error_message();		
		$title_ = $code_->error_title();		
		$code_ = $code_->error_code();
	}

	if($code_ == 404){
		pb_redirect_404();
		return;
	}

	header($_SERVER["SERVER_PROTOCOL"]." Error", true, $code_);

	$error_map_ = array(
		'error_code' => $code_,
		'error_message' => $message_,
		'error_title' => $title_,
	);

	extract($error_map_);
	include(PB_DOCUMENT_PATH."error.php");
}

function pb_head(){
	$pbvar_ = pb_hook_apply_filters('pb-head-pbvar', array());

	$jquery_script_url_ = pb_hook_apply_filters('pb-jquery-script-url', PB_LIBRARY_URL."js/jquery.js");

	global $pb_config;
?>
<script type="text/javascript" src="<?=$jquery_script_url_?>"></script>
<script type="text/javascript">window.PBVAR = <?=json_encode($pbvar_)?>;</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>comp-lib/all-main.js"></script>

<?php

	pb_hook_do_action("pb_head");
}

function pb_foot(){
	pb_hook_do_action("pb_foot");
}

function pb_end(){
	pb_hook_do_action('pb_ended');
	exit;	
}

global $_pb_includes_for_after_init;
$_pb_includes_for_after_init = array();

function __iinclude($include_){
	global $_pb_includes_for_after_initialized, $_pb_includes_for_after_init;

	if(!$_pb_includes_for_after_initialized){
		$_pb_includes_for_after_init[] = $include_;	
	}else{
		include($include_);
	}
	
}
function pb_include_after_init($include_){
	__iinclude($include_);
}

pb_hook_add_action('pb_init', '_p_hook_includes_for_after_init');
pb_hook_add_action('pb_admin_init', '_p_hook_includes_for_after_init');

function _p_hook_includes_for_after_init(){
	global $_pb_includes_for_after_initialized, $_pb_includes_for_after_init;
	$_pb_includes_for_after_initialized = true;
	foreach($_pb_includes_for_after_init as $include_){
		include($include_);
	}
}

?>