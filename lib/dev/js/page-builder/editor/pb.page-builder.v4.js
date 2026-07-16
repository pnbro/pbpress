/**
 * PBPress Page Builder v4.0 — JSON 직렬화/역직렬화 확장
 * 기존 pb.page-builder.js 프로토타입을 확장하여 JSON 지원 추가
 * 로드 순서: pb.page-builder.js → pb.page-builder.v4.js
 */
jQuery(function($){

	// ============================================================
	// Element 프로토타입 확장
	// ============================================================

	/**
	 * to_json() — to_xml()의 JSON 버전
	 * DOM 요소 데이터를 plain JS object로 직렬화
	 */
	pb_page_builder_element.prototype.to_json = function(){
		var element_map_ = pb_page_builder_element.element_map(this._key);
		var is_loadable_ = element_map_['loadable'] ? true : false;

		var result_ = {
			name : this._key,
			properties : {},
			elementcontent : null
		};

		// 프로퍼티 직렬화 (to_xml()과 동일하게 object는 JSON.stringify)
		$.each(this._element_data, function(key_, value_){
			value_ = (value_ !== null && value_ !== undefined) ? value_ : "";
			if(typeof value_ === "object"){
				value_ = JSON.stringify(value_);
			}
			result_.properties[key_] = value_;
		});

		if(is_loadable_){
			// loadable 요소: 자식 요소 재귀 직렬화
			result_.elementcontent = [];
			this._children_frame_el.children(".element-content-item").each(function(){
				result_.elementcontent.push($(this).pb_page_builder_element().to_json());
			});

			// edit-property: children_collapsed 상태 보존
			var children_collapsed_ = this._children_frame_el.hasClass("closed");
			if(children_collapsed_){
				result_._edit = { children_collapsed : "Y" };
			}
		}else{
			// content 요소: 텍스트 콘텐츠
			result_.elementcontent = this.content() || "";
		}

		return result_;
	};

	/**
	 * apply_json() — apply_xml()의 JSON 버전
	 * plain JS object에서 DOM 요소 복원
	 */
	pb_page_builder_element.prototype.apply_json = function(data_){
		this._element_data = data_.properties || {};

		// edit-property 복원
		if(data_._edit && data_._edit.children_collapsed === "Y"){
			this._children_frame_el.toggleClass("closed", true);
		}

		var element_map_ = pb_page_builder_element.element_map(this._key);

		if(element_map_['loadable'] && Array.isArray(data_.elementcontent)){
			// loadable 요소: 자식 재귀 복원
			this._children_frame_el.children(".element-content-item").remove();

			for(var i_ = 0; i_ < data_.elementcontent.length; i_++){
				var child_data_ = data_.elementcontent[i_];
				var child_el_ = $("<div>");
				this._children_frame_el.append(child_el_);

				var element_class_ = pb_page_builder_element.element_edit_class(child_data_.name);
				var element_instance_ = child_el_[element_class_](this._page_builder, child_data_.name);
				element_instance_.apply_json(child_data_);
			}

			this._check_children();
		}else{
			// content 요소: 텍스트 콘텐츠 설정
			this.content(data_.elementcontent || "");
		}

		this._update_preview();
	};

	// ============================================================
	// Page Builder 프로토타입 확장
	// ============================================================

	/**
	 * export_json() — to_xml()의 JSON 버전
	 * 전체 페이지빌더 데이터를 JSON 문자열로 직렬화
	 */
	pb_page_builder.prototype.export_json = function(){
		var elements_ = [];

		this._page_element_content_list_el.children(".element-content-item").each(function(){
			elements_.push($(this).pb_page_builder_element().to_json());
		});

		return JSON.stringify({
			version : "4.0.0",
			settings : {
				style : this._style || "",
				script : this._script || ""
			},
			elementcontent : elements_
		});
	};

	/**
	 * import_json() — apply_xml()의 JSON 버전
	 * JSON 데이터에서 전체 페이지빌더 복원 (설정 + 요소)
	 */
	pb_page_builder.prototype.import_json = function(data_){
		// 설정 복원
		if(data_.settings){
			this._style = data_.settings.style || "";
			this._script = data_.settings.script || "";
			this._update_page_settings_btn();
		}

		// 기존 요소 제거 후 복원
		this._page_element_content_list_el.children(".element-content-item").remove();

		if(data_.elementcontent){
			for(var i_ = 0; i_ < data_.elementcontent.length; i_++){
				var el_data_ = data_.elementcontent[i_];
				var el_ = $("<div>");
				this._page_element_content_list_el.append(el_);

				var element_class_ = pb_page_builder_element.element_edit_class(el_data_.name);
				var element_instance_ = el_[element_class_](this, el_data_.name);
				element_instance_.apply_json(el_data_);
			}
		}

		this._check_children();
	};

	/**
	 * apply_data() — XML/JSON 자동 감지 로더
	 * 저장된 데이터의 포맷을 자동 감지하여 적절한 메서드 호출
	 */
	pb_page_builder.prototype.apply_data = function(raw_content_){
		if(!raw_content_) return;

		try {
			if(raw_content_.trim().indexOf('{') === 0){
				// JSON 데이터
				var data_ = JSON.parse(raw_content_);
				this.import_json(data_);
			}else{
				// XML 데이터 (하위 호환)
				console.log("[PageBuilder] Legacy XML detected. Will migrate to JSON on next save.");
				this.apply_xml(raw_content_);
			}
		}catch(e_){
			console.error("[PageBuilder] Data load failed", e_);
		}
	};

	/**
	 * import_elements_json() — 저장서식 복사모드용
	 * 요소만 현재 페이지빌더에 삽입 (페이지 설정은 무시)
	 */
	pb_page_builder.prototype.import_elements_json = function(layout_json_string_){
		var data_ = (typeof layout_json_string_ === 'string') ? JSON.parse(layout_json_string_) : layout_json_string_;
		if(!data_ || !data_.elementcontent) return;

		var self_ = this;

		$.each(data_.elementcontent, function(idx_, el_data_){
			var el_ = $("<div>");
			self_._page_element_content_list_el.append(el_);

			var element_class_ = pb_page_builder_element.element_edit_class(el_data_.name);
			var element_instance_ = el_[element_class_](self_, el_data_.name);
			element_instance_.apply_json(el_data_);
		});

		self_._check_children();
	};

	// ============================================================
	// 저장서식 관련 유틸리티
	// ============================================================

	/**
	 * regenerate_ids_in_json() — JSON 요소 데이터의 id/unique_class_name 재귀 재생성
	 * 저장서식 삽입 시 고유성 보장용
	 */
	pb_page_builder_element.regenerate_ids_in_json = function(el_data_){
		if(!el_data_) return el_data_;

		if(el_data_.properties){
			el_data_.properties['id'] = "";
			el_data_.properties['unique_class_name'] =
				"pb-element-class-" + PB.random_string(10, "abcdefghijklmnopqrstuvwxyz0123456789");
		}

		if(Array.isArray(el_data_.elementcontent)){
			for(var i_ = 0; i_ < el_data_.elementcontent.length; i_++){
				pb_page_builder_element.regenerate_ids_in_json(el_data_.elementcontent[i_]);
			}
		}

		return el_data_;
	};

	/**
	 * insert_saved_layout_json() — 저장서식 삽입 (ID 재생성 포함)
	 * @param layout_json_str_ JSON 문자열 또는 파싱된 객체
	 * @param target_el_ (선택) 삽입 대상 요소. null이면 루트에 삽입
	 */
	pb_page_builder.prototype.insert_saved_layout_json = function(layout_json_str_, target_el_){
		var data_ = (typeof layout_json_str_ === 'string') ? JSON.parse(layout_json_str_) : layout_json_str_;
		if(!data_ || !data_.elementcontent) return;

		var self_ = this;
		var container_ = target_el_ ? target_el_._children_frame_el : self_._page_element_content_list_el;

		$.each(data_.elementcontent, function(idx_, el_data_){
			pb_page_builder_element.regenerate_ids_in_json(el_data_);

			var el_ = $("<div>");
			container_.append(el_);

			var element_class_ = pb_page_builder_element.element_edit_class(el_data_.name);
			var element_instance_ = el_[element_class_](self_, el_data_.name);
			element_instance_.apply_json(el_data_);
		});

		if(target_el_) target_el_._check_children();
		self_._check_children();
	};

});
