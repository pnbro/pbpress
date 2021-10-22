<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_hook_add_action('pb_admin_foot', 'pb_ajax_console_init');
pb_hook_add_action('pb_foot', 'pb_ajax_console_init');
function pb_ajax_console_init(){
	?>
<script type="text/javascript">
$(document).ajaxComplete(function(event_, xhr_, settings_){
	if(xhr_.statusText === "abort") return;
	
	var response_text_ = xhr_.responseText;

	var temp_obj_ = response_text_;

	try{
		temp_obj_ = JSON.parse(response_text_);
	}catch(e){}

	if($.type(temp_obj_) !== "string"){

		var key_list_ = Array.prototype.keys.apply(temp_obj_, [temp_obj_]);
		response_text_ = "";
		$.each(temp_obj_, function(key_, value_){

			value_ = value_.toString();
			value_ = value_.replace(/\\n/g, "<br />");
			value_ = value_.replace(/\\t/g, "&nbsp;&nbsp;&nbsp;&nbsp;");
			value_ = value_.replace(/\\(.)/mg, "$1");

			// value_ = String(value_).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

			response_text_ += "<br/><b>"+key_+"</b><br/>";
			response_text_ += "<br/>"+value_+"<br/>";
			console.log("%c"+key_, "font-weight:bold; background-color:#efefef; color:black;");
			console.log(response_text_);
		});
	}else{
		console.log(response_text_);
	}
	

	
});
</script>
	<?php
}


?>