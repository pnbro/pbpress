jQuery(function($){

	window.PB.crypt = {
		_crypt_module : null,
		//암호화하기
	    encrypt : function(text_){
	      return this._crypt_module.encrypt(text_);
	    }
	}

	//param 필터 등록
	PB.add_data_filter("encrypt", function(params_, add_){
		if(add_ === undefined || add_ === null) return params_;
		var add_count_ = add_.length;
		for(var index_=0;index_<add_count_;++index_){
			var param_name_ = add_[index_];
			params_[param_name_] = PB.crypt.encrypt(params_[param_name_]);
		}

		return params_;
	});

	PB.crypt._crypt_module = new JSEncrypt();
	PB.crypt._crypt_module.setPublicKey($("#pb-crypt-public-key").text());

	return PB.crypt;

});