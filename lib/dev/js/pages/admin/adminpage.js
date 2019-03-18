jQuery(document).ready(function(){
	var adminpage_list_ = $("#pb-adminpage-list");
	var adminpage_directory_list_ = adminpage_list_.find("[data-adminpage-aside-directory]");

	adminpage_directory_list_.each(function(){
		var adminpage_directory_ = $(this);

		adminpage_directory_.click(function(){
			var target_directory_ = $(this);
			target_directory_.parent().toggleClass("opened");
			return false;
		});
	});

	var admin_aside_ = $("#pb-admin-aside");
	var admin_aside_overlay_ = $("#pb-admin-aside-overlay");
	var admin_aside_group_ = $([admin_aside_[0], admin_aside_overlay_[0]]);

	$("[data-mobile-aside-toggle-link]").click(function(){
		var toggle_ = admin_aside_.hasClass("out");
		admin_aside_group_.toggleClass("end", false);

		setTimeout(function(){
			if(toggle_){
				admin_aside_group_.toggleClass("out", false);
			}else{
				admin_aside_group_.toggleClass("out", true);
				setTimeout(function(){
					admin_aside_group_.toggleClass("end", true);
				},300);
			}
		},100);
		
		return false;
	});
});