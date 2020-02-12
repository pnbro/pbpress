function _pb_clock_update_time(){
	var clock_el_ = $("#pb-admin-clock");
	clock_el_.text(moment().format(clock_el_.attr("data-clock-format")));
}
jQuery(document).ready(function(){
	setInterval(function(){
		_pb_clock_update_time();
	}, 1000);
	_pb_clock_update_time();

});
