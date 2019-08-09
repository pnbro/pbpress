<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


// pb_page_builder();

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">

<div class="pb-page-builder-element-margin-editor">
		
	<div class="square-edit-area margin">
		<input type="text" name="margin_top" class="input top" placeholder="위">
		<input type="text" name="margin_right" class="input right" placeholder="좌">
		<input type="text" name="margin_bottom" class="input bottom" placeholder="아래">
		<input type="text" name="margin_left" class="input left" placeholder="우">
		<label class="subject">바깥쪽여백</label>
	</div>
	<div class="square-edit-area padding">
		<input type="text" name="padding_top" class="input top" placeholder="위">
		<input type="text" name="padding_right" class="input right" placeholder="좌">
		<input type="text" name="padding_bottom" class="input bottom" placeholder="아래">
		<input type="text" name="padding_left" class="input left" placeholder="우">
		<label class="subject">안쪽여백</label>
	</div>

</div>