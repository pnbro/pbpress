<?php

function _sample_theme_add_rewrite($results_){
	$results_['other-page'] = array(
		'page' => pb_current_theme_path()."other-page.php",
	);

	return $results_;
}
pb_hook_add_filter('pb_rewrite_list', "_sample_theme_add_rewrite");

?>