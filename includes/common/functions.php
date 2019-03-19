<?

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_home_url($sub_path_ = "", $params_ = array()){
	return pb_make_url(pb_append_url(PB_DOCUMENT_URL, $sub_path_), $params_);
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
	$admin_login_url_ = pb_admin_url("login.php");
	return pb_make_url($admin_login_url_, array("redirect_url" => $redirect_url_));
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

function pb_redirect_error($code_, $message_, $title_ = "ERROR!"){
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


?>