(function($){

	$(document).ajaxSend(function(event_, jqxhr_, settings_){
		console.log("****AJAX SEND****\r\n");
		console.log({
			jqxhr : jqxhr_,
			settings : settings_
		});
		console.log("*****************\r\n");
	});

	$(document).ajaxComplete(function(event_, jqxhr_, settings_){
		console.log("****AJAX COMPLETE****\r\n");
		console.log({
			jqxhr : jqxhr_,
			settings : settings_
		});
		console.log("*****************\r\n");
	});

	$(document).ajaxError(function(event_, request_, settings_){
		if(request_.statusText === "abort") return;
		
		console.error("****AJAX ERROR****\r\n");
		console.log({
			request : request_,
			settings : settings_
		});
		console.error("*****************\r\n");
	});

	PB.add_data_action("geolocation", function(data_){
		console.log("*******GEOLOCATION*******\r\n");
		console.log("Type : "+data_.type+"\r\n");
		console.log("LAT,LNG : "+data_.latlng["lat"]+","+data_.latlng["lng"]+"\r\n");
		console.log("*************************\r\n");
	});

})(jQuery);