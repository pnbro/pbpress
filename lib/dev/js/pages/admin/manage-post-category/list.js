jQuery(document).ready(function(){
	var post_category_edit_form_module_ = $("#pb-post-category-edit-form").pb_edit_form_modal({
		
		load_action : "pb-admin-post-category-load",
		insert_action : "pb-admin-post-category-insert",
		update_action : "pb-admin-post-category-update",
		delete_action : "pb-admin-post-category-delete",

		before_insert : function(target_data_, callback_){
			target_data_['type'] = window._pbpost_type;
			callback_(true);
		},
		before_update : function(target_data_, callback_){
			target_data_['type'] = window._pbpost_type;
			callback_(true);
		},
		before_delete : function(target_data_, callback_){
			PB.confirm({
				title : "삭제확인",
				content : "분류를 삭제하시겠습니까?",
				button1 : "삭제하기"
			}, $.proxy(function(c_){
				this(c_);
			}, callback_));
		},

		after_empty_opened : function(form_el_){
			var parent_id_selector_ = form_el_.find("[name='parent_id']");			
			parent_id_selector_.children().toggle(true);
		},
		after_loaded : function(form_el_, form_data_){

			var parent_id_selector_ = form_el_.find("[name='parent_id']");
			parent_id_selector_.children().toggle(true);

			parent_id_selector_.children("[value='"+form_data_['id']+"']").toggle(false)
			// form_el_.find("[name='USE_YN'][value='"+form_data_['USE_YN']+"']").prop("checked", true);

		},
		
		after_inserted : function(){
			$("#pb-admin-post-category-table-form").submit(); //리스트테이블 재검색
		},
		after_updated : function(){
			$("#pb-admin-post-category-table-form").submit(); //리스트테이블 재검색
		},
		after_deleted : function(){
			$("#pb-admin-post-category-table-form").submit(); //리스트테이블 재검색
		}
	});

});

function pb_manage_post_category_add(){
	var post_category_edit_form_module_ = $("#pb-post-category-edit-form").pb_edit_form_modal();
		post_category_edit_form_module_.open_for_insert();
}

function pb_manage_post_category_edit(post_category_id_){
	var post_category_edit_form_module_ = $("#pb-post-category-edit-form").pb_edit_form_modal();
		post_category_edit_form_module_.open_for_update(post_category_id_);
}

function pb_manage_post_category_remove(post_category_id_){
	var post_category_edit_form_module_ = $("#pb-post-category-edit-form").pb_edit_form_modal();
		post_category_edit_form_module_.do_delete(post_category_id_);
}