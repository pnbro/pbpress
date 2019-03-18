<?php
	
include(dirname( __FILE__ ) . "/includes.php");

$redirect_url_ = isset($_GET["redirect_url"]) ? $_GET["redirect_url"] : pb_admin_login_url();

pb_user_logout();
pb_redirect($redirect_url_);
pb_admin_end();
?>