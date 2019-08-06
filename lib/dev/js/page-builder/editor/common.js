jQuery(document).ready(function(){
	pb_page_builder.add_element_edit_library('container', {
		preview : function(element_data_){
			if(element_data_['container_type'] === "container") return "<div class='preview-item'>박스스타일</div>";
			if(element_data_['container_type'] === "container-fluid") return "<div class='preview-item'>꽉채움</div>";
			else return "<div class='preview-item'>박스스타일</div>";
		}
	});
});