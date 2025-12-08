<?php 

 // $timezone = $_SESSION;
 // print_r(date_default_timezone_get());
 // $timezone_identifiers = DateTimeZone::listIdentifiers();
$timezone_list_ = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$timezone_offsets_ = array();

foreach($timezone_list_ as $timezone_){
	$timezone_data_ = new DateTimeZone($timezone_);
	$timezone_offsets_[$timezone_] = $timezone_data_->getOffset(new DateTime); 
}

print_r($timezone_offsets_);

?>
<form id="pb-test-form">
	<input type="hidden" id="pb-file-uloader-tester" data-limit="5" name="test">
</form>

<div id="pb-fileuloader-resource-manager">
	

</div>

<script type="text/javascript">
jQuery(document).ready(function(){
	window._test_module = $("#pb-file-uloader-tester").pb_image_input({
		limit: 5,
		// single : true,
		file_uploaded : function(data_){
			// console.log(data_);

			console.log($("#pb-test-form").serialize_object());
		}
	});
});

</script>