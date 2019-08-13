<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PBMenuWalker(){

	function __construct(){}

	abstract function menu_start($menu_data_, $level_){
		?>
		<ul>
		<?php
	}
	abstract function menu_end($menu_data_, $level_){
		?>
		</ul>
		<?php
	}


	abstract function menu_start($menu_data_, $item_, $level_){
		?>
		<li>
			<a href=""></a>
		<?php
	}
	abstract function menu_end($menu_data_, $level_){
		?>
		</li>
		<?php
	}




}

?>