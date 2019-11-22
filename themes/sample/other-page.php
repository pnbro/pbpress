<?php pb_theme_header("other"); ?>

<div class="container">
	<h1 class="text-center" class="text-center">Hello World!<br/>
		<small>여기는 다른 페이지입니다.</small></h1>


	<h2>AJAX TEST</h2>
	<a href="javascript:_ajax_test();">AJAX 테스트하기</a>

<script type="text/javascript">
	
function _ajax_test(){
	PB.post("sample-theme-ajax-test", {
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			alert("error!");
			return;
		}

		alert(response_json_['message']);

	});
}
</script>

</div>

<?php pb_theme_footer(); ?>	