/**
 * PBPress 라이브에디터 코어 모듈
 *
 * 실제 렌더링된 페이지 위에서 요소를 선택/편집하는 WYSIWYG 에디터
 *
 * 의존성:
 * - jQuery
 * - PB 유틸리티 (PB.post, PB.modal, PB.alert 등)
 * - pb.page-builder.v4.js (regenerate_ids_in_json 등)
 * - Sortable.js
 * - window.pb_live_editor_config (edit.php에서 주입)
 * - window.pbpage_builder_element_map
 */

jQuery(function($){

	// =============================================================================
	// pb_page_live_builder 생성자
	// =============================================================================

	var pb_page_live_builder = window.pb_page_live_builder = function(target_, options_){
		this._target = $(target_);
		this._options = $.extend({
			page_id: null,
			page_title: null,
			page_status: null,
			page_slug: null,
			builder_json: null,
			element_map: null,
			is_new: false,
			admin_url: '',
			home_url: ''
		}, options_);

		// 데이터 모델 (Single Source of Truth)
		this._builder_data = this._options.builder_json || {
			settings: { style: '', script: '' },
			elementcontent: []
		};

		// UI 참조
		this._navbar = this._target.find("[data-live-editor-navbar]");
		this._icon_panel = this._target.find("[data-live-editor-icon-panel]");
		this._side_panel = this._target.find("[data-live-editor-side-panel]");
		this._side_panel_title = this._target.find("[data-side-panel-title]");
		this._side_panel_body = this._target.find("[data-side-panel-body]");
		this._side_panel_footer = this._target.find("[data-side-panel-footer]");
		this._canvas_area = this._target.find("[data-live-editor-canvas-area]");
		this._canvas_device_frame = this._target.find("[data-canvas-device-frame]");
		this._canvas_iframe = this._target.find("[data-live-editor-canvas-iframe]");
		this._overlay_layer = this._target.find("[data-canvas-overlay-layer]");
		this._status_bar = this._target.find("[data-live-editor-status-bar]");
		this._breadcrumb = this._target.find("[data-status-breadcrumb]");
		this._zoom_value_el = this._target.find("[data-zoom-value]");

		// 상태
		this._selected_element_path = null; // 현재 선택된 요소 경로
		this._current_device = 'desktop';   // 현재 디바이스 모드
		this._current_zoom = 100;           // 현재 줌 레벨
		this._is_dirty = false;             // 미저장 변경사항 존재 여부
		this._is_saving = false;            // 저장 중 여부
		this._side_panel_open = false;      // 사이드패널 열림 상태
		this._active_panel = null;          // 현재 활성 패널 ('elements', 'layers', 등)

		// 히스토리 (Undo/Redo)
		this._history = [];
		this._history_index = -1;
		this._history_max = 50;

		this._initialize();
	};

	// =============================================================================
	// 초기화
	// =============================================================================

	pb_page_live_builder.prototype._initialize = function(){
		var self_ = this;

		// iframe 캔버스 준비 신호 수신 (postMessage 방식)
		this._canvas_ready_seq = 0;
		$(window).on('message.liveeditor', function(e_){
			var data_ = e_.originalEvent ? e_.originalEvent.data : e_.data;
			if(data_ && data_.type === 'le-canvas-ready' && data_.seq === self_._canvas_ready_seq){
				self_._canvas_ready_seq = -1; // one-shot: 동일 seq 중복 호출 차단
				self_._on_canvas_loaded();
			}
		});

		// 초기 히스토리 저장 (Undo 시 원래 상태로 복원 가능)
		this._push_history(__('초기 상태'));
		this._is_dirty = false;

		// 캔버스 렌더링
		this._render_canvas();

		// 이벤트 바인딩
		this._bind_navbar_events();
		this._bind_icon_panel_events();
		this._bind_side_panel_events();
		this._bind_status_bar_events();
		this._bind_keyboard_shortcuts();

		// TODO: 배포 시 아래 주석 해제 필요
		// 미저장 경고 — 개발 중 비활성화
		// $(window).on('beforeunload', function(){
		// 	if(self_._is_dirty){
		// 		return __("저장하지 않은 변경사항이 있습니다. 이 페이지를 떠나시겠습니까?");
		// 	}
		// });
	};

	// =============================================================================
	// 캔버스 렌더링
	// =============================================================================

	pb_page_live_builder.prototype._render_canvas = function(){
		var self_ = this;
		this._render_seq = (this._render_seq || 0) + 1;
		var seq_ = this._render_seq;

		PB.post('live-editor-render-page', {
			builder_json: JSON.stringify(self_._builder_data),
			page_id: self_._options.page_id || ''
		}, function(r_, j_){
			// 이전 요청의 응답이 늦게 도착한 경우 무시
			if(seq_ !== self_._render_seq) return;
			if(!r_ || !j_.success){
				console.error('라이브에디터: 캔버스 렌더링 실패', j_);
				return;
			}
			self_._write_to_iframe(j_.page_html);
		});
	};

	pb_page_live_builder.prototype._write_to_iframe = function(html_){
		var self_ = this;
		var iframe_ = this._canvas_iframe[0];

		// 시퀀스 증가 — 이전 렌더의 ready 신호 무시
		this._canvas_ready_seq = (this._canvas_ready_seq || 0) + 1;
		var seq_ = this._canvas_ready_seq;

		// SortableJS를 iframe 내에 주입 (드래그앤드롭용)
		var sortable_src_ = '';
		if(typeof Sortable !== 'undefined' && Sortable.toString){
			// parent의 Sortable 소스를 직접 주입하는 대신 CDN/로컬 경로 사용
			// srcdoc iframe은 same-origin이므로 parent.Sortable 참조 가능
			sortable_src_ = '<script>window.Sortable = window.parent.Sortable;<\/script>';
		}

		// HTML 끝에 ready 신호 스크립트 삽입
		var ready_script_ = '<script>window.parent.postMessage({type:"le-canvas-ready",seq:' + seq_ + '},"*");<\/script>';
		var ready_html_ = html_.replace('</body>', sortable_src_ + ready_script_ + '</body>');

		// 세션 히스토리 충돌 방지: 기존 iframe 제거 후 새 iframe 생성
		var parent_ = iframe_.parentNode;
		var new_iframe_ = document.createElement('iframe');
		new_iframe_.id = iframe_.id;
		new_iframe_.setAttribute('frameborder', '0');
		if(iframe_.hasAttribute('data-live-editor-canvas-iframe')){
			new_iframe_.setAttribute('data-live-editor-canvas-iframe', '');
		}
		new_iframe_.srcdoc = ready_html_;
		parent_.replaceChild(new_iframe_, iframe_);
		this._canvas_iframe = $(new_iframe_);
	};

	pb_page_live_builder.prototype._on_canvas_loaded = function(){
		this._build_element_index();
		this._setup_canvas_interaction();
		this._init_canvas_sortable();
	};

	// =============================================================================
	// 네비바 이벤트
	// =============================================================================

	pb_page_live_builder.prototype._bind_navbar_events = function(){
		var self_ = this;

		// 디바이스 토글
		this._target.find("[data-device-toggle] [data-device]").click(function(){
			var device_ = $(this).attr('data-device');
			self_._switch_device(device_);
		});

		// 저장
		this._target.find("[data-save-btn]").click(function(){
			self_._save();
		});

		// 미리보기
		this._target.find("[data-preview-btn]").click(function(){
			self_._preview();
		});

		// Undo/Redo
		this._target.find("[data-undo-btn]").click(function(){
			self_._undo();
		});
		this._target.find("[data-redo-btn]").click(function(){
			self_._redo();
		});
	};

	// =============================================================================
	// 디바이스 전환
	// =============================================================================

	pb_page_live_builder.prototype._switch_device = function(device_){
		this._current_device = device_;

		// 버튼 활성 상태
		this._target.find("[data-device-toggle] [data-device]").removeClass('active');
		this._target.find("[data-device-toggle] [data-device='"+device_+"']").addClass('active');

		// 캔버스 프레임 너비 변경
		this._canvas_device_frame.removeClass('device-desktop device-tablet device-mobile');
		this._canvas_device_frame.addClass('device-' + device_);

		// 선택 오버레이 재계산
		this._refresh_overlays();
	};

	// =============================================================================
	// 좌측 아이콘 패널 이벤트
	// =============================================================================

	pb_page_live_builder.prototype._bind_icon_panel_events = function(){
		var self_ = this;

		this._icon_panel.find("[data-panel]").click(function(){
			var panel_name_ = $(this).attr('data-panel');

			// 같은 패널 다시 클릭하면 토글 (닫기)
			if(self_._active_panel === panel_name_ && self_._side_panel_open){
				self_._close_side_panel();
				return;
			}

			self_._open_panel(panel_name_);
		});
	};

	// =============================================================================
	// 패널 열기/닫기
	// =============================================================================

	pb_page_live_builder.prototype._open_panel = function(panel_name_){
		// 아이콘 패널 활성 상태
		this._icon_panel.find("[data-panel]").removeClass('active');
		this._icon_panel.find("[data-panel='"+panel_name_+"']").addClass('active');

		this._active_panel = panel_name_;

		// 패널 제목 설정
		var panel_titles_ = {
			'elements': __('요소 추가'),
			'layers': __('레이어'),
			'saved-layouts': __('저장서식'),
			'design': __('디자인'),
			'settings': __('페이지 설정'),
			'history': __('히스토리')
		};
		this._side_panel_title.text(panel_titles_[panel_name_] || '');

		// 사이드패널 열기
		this._side_panel.addClass('open');
		this._side_panel_open = true;
		this._target.addClass('side-panel-open');

		// 선택 오버레이 재계산
		this._refresh_overlays();

		// 패널별 콘텐츠 로드
		switch(panel_name_){
			case 'elements':
				this._load_elements_panel();
				break;
			case 'layers':
				this._load_layers_panel();
				break;
			case 'saved-layouts':
				this._load_saved_layouts_panel();
				break;
			case 'design':
				this._load_design_panel();
				break;
			case 'settings':
				this._load_settings_panel();
				break;
			case 'history':
				this._load_history_panel();
				break;
		}
	};

	pb_page_live_builder.prototype._close_side_panel = function(){
		this._icon_panel.find("[data-panel]").removeClass('active');
		this._side_panel.removeClass('open');
		this._side_panel_open = false;
		this._active_panel = null;
		this._target.removeClass('side-panel-open');
		this._side_panel_body.empty();
		this._side_panel_footer.hide().empty();

		// 선택 오버레이 재계산
		this._refresh_overlays();
	};

	pb_page_live_builder.prototype._bind_side_panel_events = function(){
		var self_ = this;
		this._target.find("[data-side-panel-close]").click(function(){
			self_._close_side_panel();
		});
	};

	// =============================================================================
	// 패널 콘텐츠 로더 (스텁 — 후속 마일스톤에서 구현)
	// =============================================================================

	pb_page_live_builder.prototype._load_elements_panel = function(parent_key_, insert_path_, insert_pos_){
		var self_ = this;

		// 삽입 위치 정보 저장
		this._element_insert_path = insert_path_ || null;
		this._element_insert_pos = insert_pos_ || 'append'; // 'before', 'after', 'append'

		// 로딩 표시
		this._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">hourglass_empty</i><p>'+__('요소 목록 로딩중...')+'</p></div>');
		this._side_panel_footer.hide().empty();

		// AJAX: 사용 가능한 요소 목록 로드
		PB.post('page-builder-load-element', {
			options: {
				parent: parent_key_ || null
			}
		}, function(r_, j_){
			if(!r_ || !j_.success){
				self_._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">error</i><p>'+__('요소 목록 로드 실패')+'</p></div>');
				return;
			}

			var elements_ = j_.elements || {};
			var html_ = '<div class="le-element-picker">';

			// 검색바
			html_ += '<div class="le-element-search"><input type="text" class="form-control" placeholder="'+__('요소 검색...')+'" data-element-search></div>';

			// 요소 그리드
			html_ += '<div class="le-element-grid" data-element-grid>';
			for(var key_ in elements_){
				if(!elements_.hasOwnProperty(key_)) continue;
				var el_ = elements_[key_];
				var icon_html_ = el_.icon
					? '<img src="'+el_.icon+'" alt="" class="le-element-icon-img">'
					: '<i class="material-icons">widgets</i>';

				html_ += '<div class="le-element-card" data-element-key="'+key_+'" title="'+(el_.desc || el_.name)+'">'
					+ '<div class="le-element-card-icon">'+icon_html_+'</div>'
					+ '<div class="le-element-card-name">'+el_.name+'</div>'
					+ '</div>';
			}
			html_ += '</div></div>';

			self_._side_panel_body.html(html_);

			// ── 검색 필터 ──
			self_._side_panel_body.find('[data-element-search]').on('input', function(){
				var keyword_ = $(this).val().toLowerCase();
				self_._side_panel_body.find('.le-element-card').each(function(){
					var name_ = $(this).find('.le-element-card-name').text().toLowerCase();
					$(this).toggle(name_.indexOf(keyword_) >= 0);
				});
			});

			// ── 요소 카드 클릭 → 추가 ──
			self_._side_panel_body.find('.le-element-card').on('click', function(){
				var key_ = $(this).attr('data-element-key');
				self_._add_element(key_);
			});
		});
	};

	pb_page_live_builder.prototype._load_layers_panel = function(){
		var self_ = this;
		var data_ = this._builder_data;
		var html_ = '<div class="le-layers-panel">';

		if(!data_.elementcontent || !data_.elementcontent.length){
			html_ += '<div class="panel-placeholder"><i class="material-icons">layers</i><p>'+__('요소가 없습니다')+'</p><p class="sub">'+__('요소를 추가하면 여기에 표시됩니다')+'</p></div>';
		}else{
			html_ += this._render_layer_tree(data_.elementcontent, '');
		}

		html_ += '</div>';
		this._side_panel_body.html(html_);
		this._side_panel_footer.hide().empty();

		// 선택된 요소 하이라이트
		if(this._selected_element_path){
			this._side_panel_body.find('.le-layer-item[data-layer-path="' + this._selected_element_path + '"]').addClass('selected');
		}

		// 이벤트: 레이어 클릭 → 캔버스 요소 선택
		this._side_panel_body.on('click', '.le-layer-label', function(e_){
			e_.stopPropagation();
			var path_ = $(this).closest('.le-layer-item').attr('data-layer-path');
			if(!path_) return;

			// 레이어 하이라이트 업데이트
			self_._side_panel_body.find('.le-layer-item').removeClass('selected');
			$(this).closest('.le-layer-item').addClass('selected');

			// 캔버스 요소 선택 + 스크롤
			self_._select_element(path_);
			var el_ = self_._element_index[path_];
			if(el_){
				el_.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		});

		// 이벤트: 레이어 액션 아이콘 (수정/복제/삭제) — 캔버스 툴바 동작 재사용
		this._side_panel_body.off('click.le_layer_action').on('click.le_layer_action', '.le-layer-action', function(e_){
			e_.stopPropagation();
			var path_ = $(this).closest('.le-layer-item').attr('data-layer-path');
			if(!path_) return;
			var action_ = $(this).attr('data-layer-action');
			switch(action_){
				case 'edit':
					self_._select_element(path_);
					self_._open_edit_panel(path_);
					break;
				case 'duplicate':
					self_._duplicate_element(path_);
					break;
				case 'delete':
					self_._delete_element(path_);
					break;
			}
		});

		// 이벤트: 접기/펼치기 토글
		this._side_panel_body.on('click', '.le-layer-toggle', function(e_){
			e_.stopPropagation();
			var item_ = $(this).closest('.le-layer-item');
			item_.toggleClass('collapsed');
			$(this).find('i').text(item_.hasClass('collapsed') ? 'chevron_right' : 'expand_more');
		});

		// Sortable.js: 레이어 패널에서 드래그 정렬
		if(typeof Sortable !== 'undefined'){
			this._side_panel_body.find('.le-layer-list').each(function(){
				Sortable.create(this, {
					group: {
						name: 'le-layers',
						put: function(to_, from_, dragEl_){
							var parent_li_ = $(to_.el).closest('.le-layer-item');
							var parent_name_ = parent_li_.length ? parent_li_.attr('data-layer-name') : null;
							var child_name_ = $(dragEl_).attr('data-layer-name');
							return self_._can_accept_child(parent_name_, child_name_);
						}
					},
					animation: 150,
					handle: '.le-layer-label',
					ghostClass: 'le-layer-ghost',
					fallbackOnBody: true,
					swapThreshold: 0.65,
					onEnd: function(evt_){
						self_._on_layer_sort_end(evt_);
					}
				});
			});
		}
	};

	/**
	 * 레이어 드래그 정렬 완료 → JSON 트리 동기화
	 */
	pb_page_live_builder.prototype._on_layer_sort_end = function(evt_){
		var item_ = $(evt_.item);
		var old_path_ = item_.attr('data-layer-path');
		if(!old_path_) return;

		// 이전 위치 정보
		var old_parts_ = String(old_path_).split('.');
		var old_idx_ = parseInt(old_parts_[old_parts_.length - 1]);
		var old_parent_path_ = old_parts_.length > 1 ? old_parts_.slice(0, -1).join('.') : null;

		var old_parent_content_ = old_parent_path_
			? this._get_element_by_path(old_parent_path_).elementcontent
			: this._builder_data.elementcontent;

		// 새 위치의 부모 리스트 찾기
		var new_parent_li_ = item_.parent().closest('.le-layer-item');
		var new_parent_path_ = new_parent_li_.length ? new_parent_li_.attr('data-layer-path') : null;

		var new_parent_content_ = new_parent_path_
			? this._get_element_by_path(new_parent_path_).elementcontent
			: this._builder_data.elementcontent;

		var new_idx_ = evt_.newIndex;

		// 같은 부모 내 이동인지 확인
		var same_parent_ = (old_parent_path_ === new_parent_path_)
			|| (!old_parent_path_ && !new_parent_path_);

		// JSON 트리에서 이동 수행
		var element_data_ = old_parent_content_.splice(old_idx_, 1)[0];

		if(same_parent_){
			// 같은 부모 내에서 인덱스만 변경
			new_parent_content_.splice(new_idx_, 0, element_data_);
		}else{
			// 다른 부모로 이동 — 유효성 검증
			var new_parent_el_ = new_parent_path_ ? this._get_element_by_path(new_parent_path_) : null;
			var new_parent_name_ = new_parent_el_ ? new_parent_el_.name : null;

			if(!Array.isArray(new_parent_content_) || !this._can_accept_child(new_parent_name_, element_data_.name)){
				// 이동 불가 → 원복
				old_parent_content_.splice(old_idx_, 0, element_data_);
				this._load_layers_panel();
				return;
			}
			new_parent_content_.splice(new_idx_, 0, element_data_);
		}

		this._push_history(__('요소 이동'));
		this._deselect_element();

		if(same_parent_){
			// 같은 부모 내 이동 → DOM 직접 조작 (iframe 재로드 없음)
			this._move_element_dom(old_parent_path_, old_idx_, new_idx_);
		} else {
			// 다른 부모로 이동 → 전체 리렌더 (구조 변경이 복잡)
			this._render_canvas();
		}

		// 레이어 패널 새로고침 (경로 업데이트)
		var self_ = this;
		setTimeout(function(){ self_._load_layers_panel(); }, 300);
	};

	/**
	 * 레이어 트리 HTML 재귀 생성
	 */
	pb_page_live_builder.prototype._render_layer_tree = function(elements_, parent_path_){
		var html_ = '<ul class="le-layer-list">';
		var el_map_ = window.pbpage_builder_element_map || {};

		for(var i_ = 0; i_ < elements_.length; i_++){
			var el_ = elements_[i_];
			var path_ = parent_path_ ? (parent_path_ + '.' + i_) : String(i_);
			var name_ = el_['name'] || 'unknown';
			var display_name_ = this._get_element_display_name(name_);
			var has_children_ = Array.isArray(el_['elementcontent']) && el_['elementcontent'].length > 0;
			var is_loadable_ = el_map_[name_] && el_map_[name_]['loadable'];
			var icon_ = this._get_layer_icon(name_);

			// leaf: loadable이 아닌 경우만 (loadable인데 자식이 비어도 leaf가 아님)
			html_ += '<li class="le-layer-item' + (!is_loadable_ && !has_children_ ? ' leaf' : '') + '" data-layer-path="' + path_ + '" data-layer-name="' + name_ + '">';

			// 토글 버튼
			if(has_children_){
				html_ += '<span class="le-layer-toggle"><i class="material-icons">expand_more</i></span>';
			}else if(is_loadable_){
				// loadable이지만 빈 컨테이너 — 접힌 토글 (드롭 타겟 역할)
				html_ += '<span class="le-layer-toggle le-layer-toggle-empty"><i class="material-icons">expand_more</i></span>';
			}else{
				html_ += '<span class="le-layer-toggle-spacer"></span>';
			}

			// 라벨 (아이콘 + 이름 + 액션: 수정/복제/삭제) — hover 시 액션 노출
			html_ += '<span class="le-layer-label">'
				+ '<i class="material-icons le-layer-icon">' + icon_ + '</i>'
				+ '<span class="le-layer-name">' + display_name_ + '</span>'
				+ '<span class="le-layer-actions">'
					+ '<span class="le-layer-action le-layer-action-edit" data-layer-action="edit" title="'+__('편집')+'"><i class="material-icons">edit</i></span>'
					+ '<span class="le-layer-action le-layer-action-duplicate" data-layer-action="duplicate" title="'+__('복제')+'"><i class="material-icons">content_copy</i></span>'
					+ '<span class="le-layer-action le-layer-action-delete" data-layer-action="delete" title="'+__('삭제')+'"><i class="material-icons">delete</i></span>'
				+ '</span>'
				+ '</span>';

			// 자식 재귀: loadable이면 항상 <ul> 생성 (빈 컨테이너에도 드롭 타겟 확보)
			if(has_children_){
				html_ += this._render_layer_tree(el_['elementcontent'], path_);
			}else if(is_loadable_){
				html_ += '<ul class="le-layer-list le-layer-list-empty"></ul>';
			}

			html_ += '</li>';
		}
		html_ += '</ul>';
		return html_;
	};

	/**
	 * 요소 타입별 아이콘 매핑
	 */
	pb_page_live_builder.prototype._get_layer_icon = function(element_name_){
		var map_ = {
			'container': 'crop_square',
			'row': 'view_stream',
			'column': 'view_week',
			'text': 'text_fields',
			'html': 'code',
			'image': 'image',
			'image-slider': 'collections',
			'video': 'videocam',
			'button': 'smart_button',
			'divider': 'horizontal_rule',
			'spacer': 'height',
			'icon': 'insert_emoticon',
			'form': 'description',
			'list': 'format_list_bulleted',
			'table': 'table_chart',
			'tabs': 'tab',
			'accordion': 'expand_circle_down',
			'map': 'map'
		};
		return map_[element_name_] || 'widgets';
	};

	pb_page_live_builder.prototype._load_saved_layouts_panel = function(){
		var self_ = this;

		// 로딩 표시
		this._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">hourglass_empty</i><p>'+__('저장서식 로딩중...')+'</p></div>');
		this._side_panel_footer.hide().empty();

		// AJAX: 저장서식 목록 로드
		PB.post('admin-page-builder-load-saved-layouts', {}, function(r_, j_){
			if(!r_ || !j_.success){
				self_._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">bookmark</i><p>'+__('저장서식 로드 실패')+'</p></div>');
				return;
			}

			var layouts_ = j_.layouts || [];
			if(!layouts_.length){
				self_._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">bookmark_border</i><p>'+__('저장된 서식이 없습니다')+'</p><p class="sub">'+__('요소 오버레이 툴바에서 저장서식으로 등록할 수 있습니다')+'</p></div>');
				return;
			}

			var html_ = '<div class="le-saved-layouts">';
			for(var i_ = 0; i_ < layouts_.length; i_++){
				var l_ = layouts_[i_];
				html_ += '<div class="le-saved-layout-item" data-layout-id="' + (l_.id || i_) + '">';
				html_ += '<div class="le-saved-layout-thumb">';
				if(l_.thumbnail){
					html_ += '<img src="' + l_.thumbnail + '" alt="">';
				}else{
					html_ += '<i class="material-icons">description</i>';
				}
				html_ += '</div>';
				html_ += '<div class="le-saved-layout-info">';
				html_ += '<span class="le-saved-layout-name">' + (l_.name || __('이름없음')) + '</span>';
				html_ += '</div>';
				html_ += '</div>';
			}
			html_ += '</div>';
			self_._side_panel_body.html(html_);

			// 저장서식 클릭 → JSON 로드 후 삽입
			self_._side_panel_body.on('click', '.le-saved-layout-item', function(){
				var layout_id_ = $(this).attr('data-layout-id');
				PB.post('admin-page-builder-get-layout-json', { id: layout_id_ }, function(r2_, j2_){
					if(!r2_ || !j2_.success || !j2_.layout_json) return;
					var layout_data_ = JSON.parse(j2_.layout_json);
					if(!layout_data_ || !layout_data_.elementcontent) return;

					// 루트에 모든 요소 삽입
					var contents_ = layout_data_.elementcontent;
					for(var k_ = 0; k_ < contents_.length; k_++){
						self_._regenerate_ids(contents_[k_]);
						self_._builder_data.elementcontent.push(contents_[k_]);
					}

					self_._push_history(__('저장서식 삽입'));
					self_._render_canvas();
					self_._close_side_panel();
				});
			});
		});
	};

	pb_page_live_builder.prototype._load_design_panel = function(){
		// 디자인 패널 → 설정 패널로 통합됨, 설정 패널로 전환
		this._open_panel('settings');
	};

	pb_page_live_builder.prototype._load_settings_panel = function(){
		var self_ = this;
		var settings_ = this._builder_data.settings || {};

		var html_ = '<div class="le-settings-panel">';
		html_ += '<div class="form-group"><label>'+__('페이지 제목')+'</label>';
		html_ += '<input type="text" class="form-control" data-setting="page_title" value="'+( this._options.page_title ? this._options.page_title.replace(/"/g,'&quot;') : '')+'">';
		html_ += '</div>';
		html_ += '<div class="form-group"><label>'+__('슬러그 (URL)')+'</label>';
		html_ += '<input type="text" class="form-control" data-setting="page_slug" value="'+( this._options.page_slug ? this._options.page_slug.replace(/"/g,'&quot;') : '')+'">';
		html_ += '</div>';
		html_ += '<div class="form-group"><label>'+__('글로벌 CSS')+'</label>';
		html_ += '<textarea class="form-control" data-setting="custom_css">'+(settings_.style || '')+'</textarea>';
		html_ += '</div>';
		html_ += '<div class="form-group"><label>'+__('글로벌 JavaScript')+'</label>';
		html_ += '<textarea class="form-control" data-setting="custom_js">'+(settings_.script || '')+'</textarea>';
		html_ += '</div>';
		html_ += '</div>';

		this._side_panel_body.html(html_);

		// CodeMirror 적용
		var css_textarea_ = this._side_panel_body.find('[data-setting="custom_css"]')[0];
		var js_textarea_ = this._side_panel_body.find('[data-setting="custom_js"]')[0];

		if(typeof CodeMirror !== 'undefined'){
			this._settings_css_cm = CodeMirror.fromTextArea(css_textarea_, {
				lineNumbers: true,
				styleActiveLine: true,
				matchBrackets: true,
				continueComments: true,
				mode: 'css'
			});
			this._settings_js_cm = CodeMirror.fromTextArea(js_textarea_, {
				lineNumbers: true,
				styleActiveLine: true,
				matchBrackets: true,
				continueComments: true,
				mode: 'javascript'
			});
			// 사이드패널 렌더 후 CodeMirror 리프레시
			setTimeout(function(){
				self_._settings_css_cm.refresh();
				self_._settings_js_cm.refresh();
			}, 100);
		}

		// 푸터: 적용 버튼
		this._side_panel_footer.html(
			'<button type="button" class="btn btn-sm btn-primary le-settings-apply-btn"><i class="material-icons" style="font-size:14px;vertical-align:middle;margin-right:3px;">check</i>'+__('적용')+'</button>'
		).show();

		this._side_panel_footer.find('.le-settings-apply-btn').on('click', function(){
			// CodeMirror → textarea 동기화
			if(self_._settings_css_cm) self_._settings_css_cm.save();
			if(self_._settings_js_cm) self_._settings_js_cm.save();

			// 페이지 설정 적용
			var title_ = self_._side_panel_body.find('[data-setting="page_title"]').val();
			var slug_ = self_._side_panel_body.find('[data-setting="page_slug"]').val();
			var css_ = self_._side_panel_body.find('[data-setting="custom_css"]').val();
			var js_ = self_._side_panel_body.find('[data-setting="custom_js"]').val();

			self_._options.page_title = title_;
			self_._options.page_slug = slug_;
			self_._builder_data.settings.style = css_;
			self_._builder_data.settings.script = js_;

			// 네비바 제목 업데이트
			self_._target.find('[data-page-title]').text(title_ || __('새 페이지'));

			self_._push_history(__('페이지 설정 변경'));

			// CSS 변경 시 캔버스 리렌더
			self_._render_canvas();

			PB.alert(__('페이지 설정이 적용되었습니다.'));
		});
	};

	pb_page_live_builder.prototype._load_history_panel = function(){
		var self_ = this;
		var html_ = '<div class="le-history-panel">';

		if(this._history.length === 0){
			html_ += '<div class="panel-placeholder"><i class="material-icons">history</i><p>'+__('히스토리가 없습니다')+'</p><p class="sub">'+__('편집을 시작하면 히스토리가 기록됩니다')+'</p></div>';
		}else{
			html_ += '<div class="le-history-list">';
			for(var i_ = this._history.length - 1; i_ >= 0; i_--){
				var entry_ = this._history[i_];
				var is_current_ = (i_ === this._history_index);
				html_ += '<div class="le-history-item'+(is_current_ ? ' active' : '')+'" data-history-idx="'+i_+'">';
				html_ += '<i class="material-icons">'+(is_current_ ? 'radio_button_checked' : 'radio_button_unchecked')+'</i>';
				html_ += '<span>'+(entry_.label || __('변경'))+'</span>';
				html_ += '</div>';
			}
			html_ += '</div>';
		}
		html_ += '</div>';

		this._side_panel_body.html(html_);
		this._side_panel_footer.hide().empty();

		// 히스토리 항목 클릭 → 해당 시점으로 복원
		this._side_panel_body.find('.le-history-item').on('click', function(){
			var idx_ = parseInt($(this).attr('data-history-idx'));
			if(idx_ === self_._history_index) return;

			self_._history_index = idx_;
			self_._builder_data = JSON.parse(JSON.stringify(self_._history[idx_].data));
			self_._update_history_buttons();
			self_._render_canvas();
			self_._deselect_element();

			// 히스토리 패널 새로고침
			self_._load_history_panel();
		});
	};

	// =============================================================================
	// 상태바 이벤트
	// =============================================================================

	pb_page_live_builder.prototype._bind_status_bar_events = function(){
		var self_ = this;

		this._target.find("[data-zoom-in]").click(function(){
			self_._set_zoom(self_._current_zoom + 10);
		});
		this._target.find("[data-zoom-out]").click(function(){
			self_._set_zoom(self_._current_zoom - 10);
		});
		this._target.find("[data-zoom-fit]").click(function(){
			self_._set_zoom(100);
		});
	};

	pb_page_live_builder.prototype._set_zoom = function(zoom_){
		zoom_ = Math.max(25, Math.min(200, zoom_));
		this._current_zoom = zoom_;
		this._zoom_value_el.text(zoom_ + '%');

		// iframe에 CSS transform 적용
		var scale_ = zoom_ / 100;
		this._canvas_iframe.css({
			'transform': 'scale(' + scale_ + ')',
			'transform-origin': 'top left',
			'width': (100 / scale_) + '%',
			'height': (100 / scale_) + '%'
		});
	};

	// =============================================================================
	// 키보드 단축키
	// =============================================================================

	pb_page_live_builder.prototype._bind_keyboard_shortcuts = function(){
		var self_ = this;

		$(document).on('keydown', function(e_){
			// Ctrl+S / Cmd+S → 저장
			if((e_.ctrlKey || e_.metaKey) && e_.which === 83){
				e_.preventDefault();
				self_._save();
				return;
			}
			// Ctrl+Z / Cmd+Z → 실행취소
			if((e_.ctrlKey || e_.metaKey) && !e_.shiftKey && e_.which === 90){
				e_.preventDefault();
				self_._undo();
				return;
			}
			// Ctrl+Shift+Z / Cmd+Shift+Z → 다시실행
			if((e_.ctrlKey || e_.metaKey) && e_.shiftKey && e_.which === 90){
				e_.preventDefault();
				self_._redo();
				return;
			}
		});
	};

	// =============================================================================
	// 저장 (스텁 — M5에서 완성)
	// =============================================================================

	pb_page_live_builder.prototype._save = function(){
		var self_ = this;
		if(self_._is_saving) return;

		self_._is_saving = true;
		self_._target.find("[data-save-btn]").addClass('saving');

		var json_string_ = JSON.stringify(self_._builder_data);
		var selected_status_ = self_._target.find("[data-page-status]").val() || self_._options.page_status;

		var base_params_ = {
			page_data: {
				id: self_._options.page_id,
				page_title: self_._options.page_title,
				status: selected_status_
			}
		};

		function on_save_complete_(r_, j_){
			self_._is_saving = false;
			self_._target.find("[data-save-btn]").removeClass('saving').removeAttr('data-save-progress');

			if(!r_ || !j_.success){
				PB.alert(__("저장에 실패하였습니다."));
				return;
			}

			if(self_._options.is_new && j_.page_id){
				self_._options.page_id = j_.page_id;
				self_._options.is_new = false;
			}

			self_._options.page_status = selected_status_;

			self_._is_dirty = false;
			self_._target.find("[data-save-btn]").addClass('saved');
			setTimeout(function(){
				self_._target.find("[data-save-btn]").removeClass('saved');
			}, 2000);
		}

		var threshold_ = (typeof PBVAR !== 'undefined' && PBVAR['text_chunk_threshold']) ? PBVAR['text_chunk_threshold'] : (512 * 1024);
		var byte_length_ = new TextEncoder().encode(json_string_).length;

		if(byte_length_ > threshold_){
			PB.chunk_text(json_string_, {
				action: 'edit-page-chunked',
				params: base_params_,
				start: function(){
					self_._target.find("[data-save-btn]").attr('data-save-progress', '0%');
				},
				progress: function(percent_){
					self_._target.find("[data-save-btn]").attr('data-save-progress', Math.round(percent_) + '%');
				},
				fail: function(json_, raw_){
					self_._is_saving = false;
					self_._target.find("[data-save-btn]").removeClass('saving').removeAttr('data-save-progress');
					PB.alert(__("저장에 실패하였습니다."));
				}
			}, function(success_, json_){
				if(success_ === null){
					base_params_.page_data.page_html = json_string_;
					PB.post('edit-page', base_params_, on_save_complete_);
					return;
				}
				on_save_complete_(success_, json_);
			});
		}else{
			base_params_.page_data.page_html = json_string_;
			PB.post('edit-page', base_params_, on_save_complete_);
		}
	};

	// =============================================================================
	// 미리보기
	// =============================================================================

	pb_page_live_builder.prototype._preview = function(){
		var self_ = this;
		if(self_._is_previewing) return;

		self_._is_previewing = true;
		self_._target.find("[data-preview-btn]").addClass('saving');

		var json_string_ = JSON.stringify(self_._builder_data);

		function on_preview_complete_(r_, j_){
			self_._is_previewing = false;
			self_._target.find("[data-preview-btn]").removeClass('saving');

			if(!r_ || !j_.success){
				PB.alert(__("미리보기를 불러올 수 없습니다."));
				return;
			}

			window.open(j_.preview_url, '_blank');
		}

		var threshold_ = (typeof PBVAR !== 'undefined' && PBVAR['text_chunk_threshold']) ? PBVAR['text_chunk_threshold'] : (512 * 1024);
		var byte_length_ = new TextEncoder().encode(json_string_).length;

		if(byte_length_ > threshold_){
			PB.chunk_text(json_string_, {
				action: 'preview-page-chunked',
				params: {},
				fail: function(){
					self_._is_previewing = false;
					self_._target.find("[data-preview-btn]").removeClass('saving');
					PB.alert(__("미리보기를 불러올 수 없습니다."));
				}
			}, function(success_, json_){
				if(success_ === null){
					PB.post('preview-page', { page_html: json_string_ }, on_preview_complete_);
					return;
				}
				on_preview_complete_(success_, json_);
			});
		}else{
			PB.post('preview-page', { page_html: json_string_ }, on_preview_complete_);
		}
	};

	// =============================================================================
	// Undo / Redo (스텁 — M5에서 완성)
	// =============================================================================

	pb_page_live_builder.prototype._push_history = function(label_){
		// 현재 위치 이후의 히스토리 삭제
		this._history.splice(this._history_index + 1);

		// 딥 클론 push
		this._history.push({
			label: label_,
			data: JSON.parse(JSON.stringify(this._builder_data))
		});

		// 최대 개수 제한
		if(this._history.length > this._history_max){
			this._history.shift();
		}

		this._history_index = this._history.length - 1;
		this._is_dirty = true;
		this._update_history_buttons();
	};

	pb_page_live_builder.prototype._undo = function(){
		if(this._history_index <= 0) return;
		this._history_index--;
		this._builder_data = JSON.parse(JSON.stringify(this._history[this._history_index].data));
		this._render_canvas();
		this._update_history_buttons();
	};

	pb_page_live_builder.prototype._redo = function(){
		if(this._history_index >= this._history.length - 1) return;
		this._history_index++;
		this._builder_data = JSON.parse(JSON.stringify(this._history[this._history_index].data));
		this._render_canvas();
		this._update_history_buttons();
	};

	pb_page_live_builder.prototype._update_history_buttons = function(){
		this._target.find("[data-undo-btn]").prop('disabled', this._history_index <= 0);
		this._target.find("[data-redo-btn]").prop('disabled', this._history_index >= this._history.length - 1);
	};

	// =============================================================================
	// M2: 요소 인덱스 구축
	// =============================================================================

	/**
	 * iframe DOM에서 data-live-edit-id를 가진 요소들의 인덱스 구축
	 * { "0": DOMElement, "0.0": DOMElement, ... }
	 */
	pb_page_live_builder.prototype._build_element_index = function(){
		this._element_index = {};
		var iframe_ = this._canvas_iframe[0];
		var doc_ = iframe_.contentDocument;
		if(!doc_) return;

		var els_ = doc_.querySelectorAll('[data-live-edit-id]');
		for(var i_ = 0; i_ < els_.length; i_++){
			var path_ = els_[i_].getAttribute('data-live-edit-id');
			this._element_index[path_] = els_[i_];
		}
	};

	// =============================================================================
	// DOM 직접 조작 유틸리티 (iframe 재로드 없이 요소 조작)
	// =============================================================================

	/**
	 * 부모 요소의 직계 편집 자식(data-live-edit-id) 찾기
	 * 중간에 다른 data-live-edit-id 조상이 없는 자식들만 반환
	 */
	pb_page_live_builder.prototype._get_direct_edit_children = function(parent_el_){
		var all_ = parent_el_.querySelectorAll('[data-live-edit-id]');
		var result_ = [];
		for(var i_ = 0; i_ < all_.length; i_++){
			var el_ = all_[i_];
			var ancestor_ = el_.parentNode;
			var is_direct_ = true;
			while(ancestor_ && ancestor_ !== parent_el_){
				if(ancestor_.hasAttribute && ancestor_.hasAttribute('data-live-edit-id')){
					is_direct_ = false;
					break;
				}
				ancestor_ = ancestor_.parentNode;
			}
			if(is_direct_) result_.push(el_);
		}
		return result_;
	};

	/**
	 * 서브트리의 모든 data-live-edit-id를 DOM 순서 기반으로 재인덱싱
	 */
	pb_page_live_builder.prototype._reindex_subtree = function(parent_el_, parent_path_){
		var children_ = this._get_direct_edit_children(parent_el_);
		for(var i_ = 0; i_ < children_.length; i_++){
			var new_path_ = parent_path_ ? (parent_path_ + '.' + i_) : String(i_);
			children_[i_].setAttribute('data-live-edit-id', new_path_);
			this._reindex_subtree(children_[i_], new_path_);
		}
	};

	/**
	 * 전체 iframe DOM의 data-live-edit-id 재인덱싱 + 인덱스 재구축
	 */
	pb_page_live_builder.prototype._reindex_all = function(){
		var doc_ = this._canvas_iframe[0].contentDocument;
		if(!doc_) return;
		this._reindex_subtree(doc_.body, null);
		this._build_element_index();
	};

	/**
	 * 새 요소를 서버 렌더링 후 iframe DOM에 삽입 (iframe 재로드 없음)
	 * @param {string|null} parent_path_ - 부모 경로 (null=루트)
	 * @param {number} insert_idx_ - 삽입 위치 인덱스
	 * @param {object} element_data_ - 요소 JSON 데이터
	 * @param {function} [callback_] - 완료 콜백
	 */
	pb_page_live_builder.prototype._render_and_insert_element = function(parent_path_, insert_idx_, element_data_, callback_){
		var self_ = this;
		var element_name_ = element_data_['name'];

		PB.post('live-editor-render-element', {
			element_name: element_name_,
			element_json: JSON.stringify(element_data_),
			element_path: parent_path_ ? (parent_path_ + '.' + insert_idx_) : String(insert_idx_)
		}, function(r_, j_){
			if(!r_ || !j_.success){
				console.error('라이브에디터: 요소 렌더 실패', j_);
				self_._render_canvas();
				if(callback_) callback_();
				return;
			}

			var iframe_doc_ = self_._canvas_iframe[0].contentDocument;
			if(!iframe_doc_){
				self_._render_canvas();
				if(callback_) callback_();
				return;
			}

			// 서버 응답 HTML → iframe DOM 요소로 변환
			// (서버가 이미 data-live-edit-id 래퍼를 포함하여 반환)
			var temp_ = iframe_doc_.createElement('div');
			temp_.innerHTML = j_.rendered_html;
			var new_el_ = temp_.firstElementChild;

			if(!new_el_){
				self_._render_canvas();
				if(callback_) callback_();
				return;
			}

			// 부모 DOM 찾기
			var parent_dom_ = parent_path_
				? self_._element_index[parent_path_]
				: iframe_doc_.body;

			if(!parent_dom_){
				self_._render_canvas();
				if(callback_) callback_();
				return;
			}

			// 기존 직계 편집 자식 목록
			var siblings_ = self_._get_direct_edit_children(parent_dom_);

			// 빈 부모(루트 아님)에 첫 자식 삽입 → 부모 리렌더로 대체
			if(siblings_.length === 0 && parent_path_){
				var parent_data_ = self_._get_element_by_path(parent_path_);
				if(parent_data_){
					self_._rerender_element(parent_path_, parent_data_);
				} else {
					self_._render_canvas();
				}
				if(callback_) callback_();
				return;
			}

			// DOM에 삽입
			if(insert_idx_ < siblings_.length){
				siblings_[insert_idx_].parentNode.insertBefore(new_el_, siblings_[insert_idx_]);
			} else if(siblings_.length > 0){
				var last_ = siblings_[siblings_.length - 1];
				if(last_.nextSibling){
					last_.parentNode.insertBefore(new_el_, last_.nextSibling);
				} else {
					last_.parentNode.appendChild(new_el_);
				}
			} else {
				// 루트에 첫 요소 추가 — 콘텐츠 영역 끝 마커 앞에 삽입
				var boundary_ = iframe_doc_.getElementById('pb-live-edit-content-end');
				if(boundary_){
					boundary_.parentNode.insertBefore(new_el_, boundary_);
				} else {
					parent_dom_.appendChild(new_el_);
				}
			}

			// 전체 경로 재인덱싱
			self_._reindex_all();

			// 동적 삽입된 요소에 드래그 핸들 + Sortable 보장
			self_._ensure_drag_handles(new_el_);
			self_._init_sortable_for_element(new_el_);

			if(self_._selected_element_path){
				self_._show_select_overlay(self_._selected_element_path);
			}

			if(callback_) callback_();
		});
	};

	/**
	 * iframe DOM에서 요소 제거 + 경로 재인덱싱
	 */
	pb_page_live_builder.prototype._remove_element_dom = function(path_){
		var el_ = this._element_index[path_];
		if(!el_ || !el_.parentNode){
			this._render_canvas();
			return;
		}
		el_.parentNode.removeChild(el_);
		this._reindex_all();
	};

	/**
	 * 두 요소의 DOM 위치 교환 + 경로 재인덱싱
	 */
	pb_page_live_builder.prototype._swap_element_dom = function(path_a_, path_b_){
		var el_a_ = this._element_index[path_a_];
		var el_b_ = this._element_index[path_b_];

		if(!el_a_ || !el_b_){
			this._render_canvas();
			return;
		}

		var doc_ = el_a_.ownerDocument;
		var placeholder_ = doc_.createComment('swap');
		el_a_.parentNode.insertBefore(placeholder_, el_a_);
		el_b_.parentNode.insertBefore(el_a_, el_b_);
		placeholder_.parentNode.insertBefore(el_b_, placeholder_);
		placeholder_.parentNode.removeChild(placeholder_);

		this._reindex_all();
	};

	/**
	 * 부모 내에서 요소를 다른 위치로 이동 (다중 위치 이동 지원)
	 */
	pb_page_live_builder.prototype._move_element_dom = function(parent_path_, old_idx_, new_idx_){
		var iframe_doc_ = this._canvas_iframe[0].contentDocument;
		var parent_dom_ = parent_path_
			? this._element_index[parent_path_]
			: iframe_doc_.body;

		if(!parent_dom_){
			this._render_canvas();
			return;
		}

		var siblings_ = this._get_direct_edit_children(parent_dom_);
		if(old_idx_ >= siblings_.length){
			this._render_canvas();
			return;
		}

		var moved_el_ = siblings_[old_idx_];
		moved_el_.parentNode.removeChild(moved_el_);

		// 제거 후 형제 목록 다시 조회
		siblings_ = this._get_direct_edit_children(parent_dom_);

		if(new_idx_ < siblings_.length){
			siblings_[new_idx_].parentNode.insertBefore(moved_el_, siblings_[new_idx_]);
		} else if(siblings_.length > 0){
			var last_ = siblings_[siblings_.length - 1];
			if(last_.nextSibling){
				last_.parentNode.insertBefore(moved_el_, last_.nextSibling);
			} else {
				last_.parentNode.appendChild(moved_el_);
			}
		} else {
			parent_dom_.appendChild(moved_el_);
		}

		this._reindex_all();
	};

	// =============================================================================
	// M2: 캔버스 인터랙션 설정
	// =============================================================================

	pb_page_live_builder.prototype._setup_canvas_interaction = function(){
		var self_ = this;
		var iframe_ = this._canvas_iframe[0];
		var doc_ = iframe_.contentDocument;
		if(!doc_) return;

		this._hovered_path = null;

		// ── mousemove: 호버 오버레이 ──
		$(doc_).on('mousemove.liveeditor', function(e_){
			var target_ = e_.target;
			var edit_el_ = self_._find_closest_edit_element(target_);

			if(edit_el_){
				var path_ = edit_el_.getAttribute('data-live-edit-id');
				if(path_ !== self_._hovered_path && path_ !== self_._selected_element_path){
					self_._hovered_path = path_;
					self_._show_hover_overlay(path_);
				}
			}else{
				if(self_._hovered_path){
					self_._hovered_path = null;
					self_._clear_hover_overlay();
				}
			}
		});

		// ── mouseleave: 호버 제거 ──
		$(doc_).on('mouseleave.liveeditor', function(){
			self_._hovered_path = null;
			self_._clear_hover_overlay();
		});

		// ── click: 요소 선택 ──
		$(doc_).on('click.liveeditor', function(e_){
			e_.preventDefault();
			e_.stopPropagation();

			var target_ = e_.target;
			var edit_el_ = self_._find_closest_edit_element(target_);

			if(edit_el_){
				var path_ = edit_el_.getAttribute('data-live-edit-id');
				self_._select_element(path_);
			}else{
				self_._deselect_element();
			}
		});

		// ── dblclick: 요소 편집 ──
		$(doc_).on('dblclick.liveeditor', function(e_){
			e_.preventDefault();
			e_.stopPropagation();

			var target_ = e_.target;
			var edit_el_ = self_._find_closest_edit_element(target_);
			if(edit_el_){
				var path_ = edit_el_.getAttribute('data-live-edit-id');
				self_._select_element(path_);
				self_._open_edit_panel(path_);
			}
		});

		// ── iframe 스크롤 시 오버레이 위치 재계산 ──
		$(iframe_.contentWindow).on('scroll.liveeditor', function(){
			if(self_._selected_element_path){
				self_._show_select_overlay(self_._selected_element_path);
			}
			if(self_._hovered_path && self_._hovered_path !== self_._selected_element_path){
				self_._show_hover_overlay(self_._hovered_path);
			}
		});

		// ── 캔버스 영역 스크롤 시 오버레이 위치 재계산 ──
		this._canvas_area.on('scroll.liveeditor', function(){
			if(self_._selected_element_path){
				self_._show_select_overlay(self_._selected_element_path);
			}
			if(self_._hovered_path && self_._hovered_path !== self_._selected_element_path){
				self_._show_hover_overlay(self_._hovered_path);
			}
		});

		// ── iframe 내 링크 클릭 방지 ──
		$(doc_).on('click.liveeditor', 'a', function(e_){
			e_.preventDefault();
		});
	};

	/**
	 * 가장 가까운 data-live-edit-id 요소를 찾음 (버블링)
	 */
	pb_page_live_builder.prototype._find_closest_edit_element = function(el_){
		while(el_ && el_.nodeType === 1){
			if(el_.hasAttribute && el_.hasAttribute('data-live-edit-id')){
				return el_;
			}
			el_ = el_.parentNode;
		}
		return null;
	};

	// =============================================================================
	// M2: 요소 선택/해제
	// =============================================================================

	pb_page_live_builder.prototype._select_element = function(path_){
		// 같은 요소면 무시
		if(this._selected_element_path === path_) return;

		this._selected_element_path = path_;
		this._hovered_path = null;

		this._clear_hover_overlay();
		this._show_select_overlay(path_);
		this._update_breadcrumb(path_);
	};

	pb_page_live_builder.prototype._deselect_element = function(){
		this._selected_element_path = null;
		this._clear_select_overlay();
		this._update_breadcrumb(null);
	};

	/**
	 * 선택 오버레이 위치 재계산 (사이드패널 열기/닫기, 디바이스 변경 후 호출)
	 */
	pb_page_live_builder.prototype._refresh_overlays = function(){
		var self_ = this;
		if(this._selected_element_path && this._element_index){
			// CSS transition(0.25s) 완료 후 재계산
			setTimeout(function(){
				self_._show_select_overlay(self_._selected_element_path);
			}, 300);
		}
	};

	// =============================================================================
	// M2: 오버레이 렌더링
	// =============================================================================

	/**
	 * 요소의 bbox를 오버레이 레이어 좌표계로 계산
	 */
	pb_page_live_builder.prototype._get_element_rect = function(path_){
		var el_ = this._element_index[path_];
		if(!el_) return null;

		var iframe_ = this._canvas_iframe[0];
		var iframe_rect_ = iframe_.getBoundingClientRect();
		var el_rect_ = el_.getBoundingClientRect();

		// el_rect_는 iframe 내부 뷰포트 기준 → 부모 문서 좌표로 변환:
		// (iframe의 부모문서 좌표) + (요소의 iframe내 좌표)
		var abs_top_ = iframe_rect_.top + el_rect_.top;
		var abs_left_ = iframe_rect_.left + el_rect_.left;

		// 오버레이 레이어의 기준점 (부모 문서 뷰포트 좌상단 기준)
		var overlay_ = this._overlay_layer[0];
		var overlay_rect_ = overlay_.getBoundingClientRect();

		// canvas-area 스크롤 오프셋 보정
		var canvas_scroll_top_ = this._canvas_area[0].scrollTop;
		var canvas_scroll_left_ = this._canvas_area[0].scrollLeft;

		return {
			top: abs_top_ - overlay_rect_.top + canvas_scroll_top_,
			left: abs_left_ - overlay_rect_.left + canvas_scroll_left_,
			width: el_rect_.width,
			height: el_rect_.height
		};
	};

	/**
	 * 호버 오버레이 표시 (파란 점선 + 라벨)
	 */
	pb_page_live_builder.prototype._show_hover_overlay = function(path_){
		var rect_ = this._get_element_rect(path_);
		if(!rect_) return;

		var el_ = this._element_index[path_];
		var name_ = el_.getAttribute('data-live-edit-name') || '';
		var display_name_ = this._get_element_display_name(name_);

		// 기존 호버 오버레이 제거
		this._overlay_layer.find('.le-hover-overlay').remove();

		var html_ = '<div class="le-hover-overlay" style="'
			+ 'top:' + rect_.top + 'px;'
			+ 'left:' + rect_.left + 'px;'
			+ 'width:' + rect_.width + 'px;'
			+ 'height:' + rect_.height + 'px;'
			+ '">'
			+ '<span class="le-overlay-label le-hover-label">' + display_name_ + '</span>'
			+ '</div>';

		this._overlay_layer.append(html_);
	};

	pb_page_live_builder.prototype._clear_hover_overlay = function(){
		this._overlay_layer.find('.le-hover-overlay').remove();
	};

	/**
	 * 선택 오버레이 표시 (파란 실선 + 라벨 + 툴바)
	 */
	pb_page_live_builder.prototype._show_select_overlay = function(path_){
		var rect_ = this._get_element_rect(path_);
		if(!rect_) return;

		var el_ = this._element_index[path_];
		var name_ = el_.getAttribute('data-live-edit-name') || '';
		var display_name_ = this._get_element_display_name(name_);

		// 기존 선택 오버레이 제거
		this._overlay_layer.find('.le-select-overlay').remove();

		// loadable 요소인 경우 "하위 요소 추가" 버튼 표시
		var map_ = window.pbpage_builder_element_map || {};
		var el_meta_ = map_[name_];
		var add_child_btn_ = '';
		if(el_meta_ && el_meta_.loadable){
			add_child_btn_ = '<button type="button" class="le-toolbar-btn le-toolbar-btn-add-child" data-action="add-child" title="'+__('하위 요소 추가')+'"><i class="material-icons">add_circle</i></button>';
		}

		// 자식 목록 계산 (직속 자식만)
		var el_data_ = this._get_element_by_path(path_);
		var children_ = (el_data_ && Array.isArray(el_data_.elementcontent)) ? el_data_.elementcontent : [];

		// 자식이 1개 이상일 때만 "자식 목록" 버튼 표시
		var child_list_btn_ = '';
		if(children_.length > 0){
			child_list_btn_ = '<button type="button" class="le-toolbar-btn le-toolbar-btn-child-list" data-action="child-list" title="'+__('자식 목록')+'"><i class="material-icons">list</i></button>';
		}

		// 자식 목록 드롭다운 HTML (기본 숨김)
		var child_dropdown_ = '';
		if(children_.length > 0){
			child_dropdown_ = this._build_child_dropdown_html(path_, children_);
		}

		var toolbar_html_ = '<div class="le-overlay-toolbar">'
			+ child_list_btn_
			+ add_child_btn_
			+ '<button type="button" class="le-toolbar-btn" data-action="move-up" title="'+__('위로 이동')+'"><i class="material-icons">arrow_upward</i></button>'
			+ '<button type="button" class="le-toolbar-btn" data-action="move-down" title="'+__('아래로 이동')+'"><i class="material-icons">arrow_downward</i></button>'
			+ '<button type="button" class="le-toolbar-btn" data-action="edit" title="'+__('편집')+'"><i class="material-icons">edit</i></button>'
			+ '<button type="button" class="le-toolbar-btn" data-action="duplicate" title="'+__('복제')+'"><i class="material-icons">content_copy</i></button>'
			+ '<button type="button" class="le-toolbar-btn" data-action="delete" title="'+__('삭제')+'"><i class="material-icons">delete</i></button>'
			+ '</div>';

		var html_ = '<div class="le-select-overlay" data-selected-path="' + path_ + '" style="'
			+ 'top:' + rect_.top + 'px;'
			+ 'left:' + rect_.left + 'px;'
			+ 'width:' + rect_.width + 'px;'
			+ 'height:' + rect_.height + 'px;'
			+ '">'
			+ '<span class="le-overlay-label le-select-label">' + display_name_ + '</span>'
			+ toolbar_html_
			+ child_dropdown_
			+ '</div>';

		this._overlay_layer.append(html_);

		// ── 툴바 버튼 이벤트 ──
		var self_ = this;
		this._overlay_layer.find('.le-select-overlay .le-toolbar-btn').on('click', function(e_){
			e_.stopPropagation();
			var action_ = $(this).attr('data-action');
			var target_path_ = self_._selected_element_path;
			switch(action_){
				case 'add-child':
					var el_data_ = self_._get_element_by_path(target_path_);
					var el_name_ = el_data_ ? el_data_.name : null;
					// 사이드패널 직접 열기 (커스텀 제목 포함)
					self_._icon_panel.find("[data-panel]").removeClass('active');
					self_._icon_panel.find("[data-panel='elements']").addClass('active');
					self_._active_panel = 'elements';
					self_._side_panel_title.text(__('하위 요소 추가'));
					self_._side_panel.addClass('open');
					self_._side_panel_open = true;
					self_._target.addClass('side-panel-open');
					self_._refresh_overlays();
					// 부모 필터링된 요소 목록 로드
					self_._load_elements_panel(el_name_, target_path_, 'child');
					break;
				case 'edit':
					self_._open_edit_panel(target_path_);
					break;
				case 'move-up':
					self_._move_element(target_path_, -1);
					break;
				case 'move-down':
					self_._move_element(target_path_, 1);
					break;
				case 'duplicate':
					self_._duplicate_element(target_path_);
					break;
				case 'delete':
					self_._delete_element(target_path_);
					break;
				case 'child-list':
					var dd_ = self_._overlay_layer.find('.le-select-overlay .le-child-dropdown');
					dd_.toggle();
					break;
			}
		});

		// ── 자식 목록 항목 이벤트 (선택/편집/삭제/드래그 정렬) ──
		this._setup_child_dropdown_events(path_);
	};

	/**
	 * 자식 목록 드롭다운 HTML 생성 (드래그핸들 + 아이디 + 아이콘 + 이름 + 수정/삭제 액션)
	 */
	pb_page_live_builder.prototype._build_child_dropdown_html = function(path_, children_){
		var html_ = '<div class="le-child-dropdown" style="display:none;">'
			+ '<div class="le-child-dropdown-head">'+__('자식 개체')+' '+children_.length+'</div>'
			+ '<div class="le-child-list">';
		for(var ci_ = 0; ci_ < children_.length; ci_++){
			var child_ = children_[ci_];
			var cname_ = child_ && child_.name ? child_.name : 'unknown';
			var cdisp_ = this._get_element_display_name(cname_);
			var cicon_ = (typeof this._get_layer_icon === 'function') ? this._get_layer_icon(cname_) : 'widgets';
			var cpath_ = path_ + '.' + ci_;
			html_ += '<div class="le-child-item" data-child-path="'+cpath_+'" data-child-idx="'+ci_+'">'
				+ '<span class="le-child-drag" title="'+__('드래그하여 순서 변경')+'"><i class="material-icons">drag_handle</i></span>'
				+ '<span class="le-child-idx">'+(ci_+1)+'</span>'
				+ '<i class="material-icons le-child-icon">'+cicon_+'</i>'
				+ '<span class="le-child-name">'+cdisp_+'</span>'
				+ '<span class="le-child-actions">'
					+ '<span class="le-child-action" data-child-action="edit" title="'+__('편집')+'"><i class="material-icons">edit</i></span>'
					+ '<span class="le-child-action" data-child-action="delete" title="'+__('삭제')+'"><i class="material-icons">delete</i></span>'
				+ '</span>'
				+ '</div>';
		}
		html_ += '</div></div>';
		return html_;
	};

	/**
	 * 자식 목록 드롭다운 이벤트 바인딩
	 * - 항목 클릭(핸들/액션 제외) → 해당 자식 선택
	 * - 편집 → 해당 자식 편집 패널 열기
	 * - 삭제 → 해당 자식 삭제(목록 열어둔 채 갱신)
	 * - 드래그 핸들 → SortableJS 순서 변경(목록 열어둔 채 갱신)
	 */
	pb_page_live_builder.prototype._setup_child_dropdown_events = function(parent_path_){
		var self_ = this;
		var dd_ = this._overlay_layer.find('.le-select-overlay .le-child-dropdown');
		if(!dd_.length) return;

		// 항목 클릭 → 해당 자식 선택 (드래그 핸들/액션 영역 제외)
		dd_.find('.le-child-item').on('click', function(e_){
			if($(e_.target).closest('.le-child-action, .le-child-drag').length) return;
			e_.stopPropagation();
			var cpath_ = $(this).attr('data-child-path');
			dd_.hide();
			self_._select_element(cpath_);
		});

		// 드래그 핸들 클릭이 선택으로 전파되지 않도록
		dd_.find('.le-child-drag').on('click', function(e_){ e_.stopPropagation(); });

		// 편집/삭제 액션
		dd_.find('.le-child-action').on('click', function(e_){
			e_.stopPropagation();
			var item_ = $(this).closest('.le-child-item');
			var cpath_ = item_.attr('data-child-path');
			var cidx_ = parseInt(item_.attr('data-child-idx'));
			var action_ = $(this).attr('data-child-action');
			if(action_ === 'edit'){
				dd_.hide();
				self_._select_element(cpath_);
				self_._open_edit_panel(cpath_);
			}else if(action_ === 'delete'){
				self_._delete_child_in_list(parent_path_, cidx_);
			}
		});

		// 드래그 정렬 (SortableJS — 부모 문서에서 사용 가능)
		if(typeof Sortable !== 'undefined'){
			var list_el_ = dd_.find('.le-child-list')[0];
			if(list_el_){
				Sortable.create(list_el_, {
					animation: 150,
					handle: '.le-child-drag',
					ghostClass: 'le-child-ghost',
					onEnd: function(evt_){
						self_._reorder_child_in_list(parent_path_, evt_.oldIndex, evt_.newIndex);
					}
				});
			}
		}
	};

	/**
	 * 자식 목록에서 드래그로 순서 변경 → JSON 트리 + 캔버스 DOM 동기화 (목록 유지)
	 */
	pb_page_live_builder.prototype._reorder_child_in_list = function(parent_path_, old_idx_, new_idx_){
		if(old_idx_ == null || new_idx_ == null || old_idx_ === new_idx_) return;
		var parent_data_ = this._get_element_by_path(parent_path_);
		if(!parent_data_ || !Array.isArray(parent_data_.elementcontent)) return;
		var content_ = parent_data_.elementcontent;
		if(old_idx_ < 0 || old_idx_ >= content_.length || new_idx_ < 0 || new_idx_ >= content_.length) return;

		// JSON 트리에서 이동
		var item_ = content_.splice(old_idx_, 1)[0];
		content_.splice(new_idx_, 0, item_);

		this._push_history(__('자식 순서 변경'));

		// 캔버스 iframe DOM 이동 (재로드 없음)
		this._move_element_dom(parent_path_, old_idx_, new_idx_);

		// 레이어 패널 열려 있으면 갱신
		if(this._active_panel === 'layers'){
			this._load_layers_panel();
		}

		// 오버레이+드롭다운 재구성 후 목록 열어둔 채 유지
		this._show_select_overlay(parent_path_);
		this._overlay_layer.find('.le-select-overlay .le-child-dropdown').show();
	};

	/**
	 * 자식 목록에서 삭제 → 부모 선택 유지 + 남은 자식 있으면 목록 유지
	 */
	pb_page_live_builder.prototype._delete_child_in_list = function(parent_path_, child_idx_){
		var self_ = this;
		var child_path_ = parent_path_ + '.' + child_idx_;
		var el_data_ = this._get_element_by_path(child_path_);
		if(!el_data_) return;
		var display_name_ = this._get_element_display_name(el_data_['name'] || '');

		PB.confirm({
			title: __('요소 삭제'),
			content: __('"{name}" 요소를 삭제하시겠습니까?').replace('{name}', display_name_),
			button1: __('삭제하기')
		}, function(c_){
			if(!c_) return;
			var parent_data_ = self_._get_element_by_path(parent_path_);
			if(!parent_data_ || !Array.isArray(parent_data_.elementcontent)) return;

			// JSON 트리에서 자식 제거
			parent_data_.elementcontent.splice(child_idx_, 1);
			self_._push_history(__('요소 삭제') + ': ' + display_name_);

			// 캔버스 DOM에서 자식 제거 (재로드 없음)
			self_._remove_element_dom(child_path_);

			if(self_._active_panel === 'layers'){
				self_._load_layers_panel();
			}

			// 부모가 여전히 선택 상태 → 오버레이 재구성 (자식수/툴바 갱신)
			self_._show_select_overlay(parent_path_);

			// 남은 자식이 있으면 목록 다시 열기
			var remaining_ = self_._get_element_by_path(parent_path_);
			if(remaining_ && Array.isArray(remaining_.elementcontent) && remaining_.elementcontent.length > 0){
				self_._overlay_layer.find('.le-select-overlay .le-child-dropdown').show();
			}
		});
	};

	pb_page_live_builder.prototype._clear_select_overlay = function(){
		this._overlay_layer.find('.le-select-overlay').remove();
	};

	// =============================================================================
	// M2: Breadcrumb 업데이트
	// =============================================================================

	pb_page_live_builder.prototype._update_breadcrumb = function(path_){
		var self_ = this;
		var html_ = '';

		// Body 항목 (항상 표시)
		html_ += '<span class="breadcrumb-item" data-breadcrumb-path="">Body</span>';

		if(path_){
			// 경로 분해 → 조상 요소 순회
			var parts_ = String(path_).split('.');
			for(var i_ = 0; i_ < parts_.length; i_++){
				var ancestor_path_ = parts_.slice(0, i_ + 1).join('.');
				var el_data_ = this._get_element_by_path(ancestor_path_);
				if(!el_data_) continue;

				var display_name_ = this._get_element_display_name(el_data_['name'] || '');
				html_ += '<span class="breadcrumb-separator">›</span>';
				html_ += '<span class="breadcrumb-item" data-breadcrumb-path="' + ancestor_path_ + '">' + display_name_ + '</span>';
			}
		}

		this._breadcrumb.html(html_);

		// Breadcrumb 클릭 → 해당 요소 선택
		this._breadcrumb.find('.breadcrumb-item[data-breadcrumb-path]').on('click', function(){
			var click_path_ = $(this).attr('data-breadcrumb-path');
			if(click_path_ === ''){
				self_._deselect_element();
			}else{
				self_._select_element(click_path_);
			}
		});
	};

	// =============================================================================
	// M3: 사이드패널 속성 편집
	// =============================================================================

	/**
	 * 선택된 요소의 편집 폼을 사이드패널에 로드
	 */
	pb_page_live_builder.prototype._open_edit_panel = function(path_){
		var self_ = this;
		var el_data_ = this._get_element_by_path(path_);
		if(!el_data_) return;

		var element_id_ = el_data_['name'];
		var display_name_ = this._get_element_display_name(element_id_);

		// 편집 중인 경로 기억
		this._editing_path = path_;
		this._editing_original_data = JSON.parse(JSON.stringify(el_data_));

		// 아이콘 패널 활성 상태 초기화 (편집 모드는 별도 패널)
		this._icon_panel.find("[data-panel]").removeClass('active');
		this._active_panel = '_edit';

		// 사이드패널 헤더
		this._side_panel_title.text(display_name_ + ' ' + __('편집'));

		// 사이드패널 바디: 로딩 표시
		this._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">hourglass_empty</i><p>'+__('편집 폼 로딩중...')+'</p></div>');

		// 사이드패널 푸터: 적용/취소 버튼
		this._side_panel_footer.html(
			'<button type="button" class="btn btn-sm btn-primary le-apply-btn"><i class="material-icons" style="font-size:14px;vertical-align:middle;margin-right:3px;">check</i>'+__('적용')+'</button>'
			+ '<button type="button" class="btn btn-sm btn-default le-cancel-btn">'+__('취소')+'</button>'
		).show();

		// 사이드패널 열기
		this._side_panel.addClass('open');
		this._side_panel_open = true;
		this._target.addClass('side-panel-open');

		// ── AJAX: 편집 폼 로드 ──
		var content_ = el_data_['elementcontent'] || '';
		// elementcontent가 배열(자식 요소)이면 문자열로 변환하지 않음 — 빈 문자열 전달
		if(Array.isArray(content_)) content_ = '';

		PB.post('page-builder-load-edit-form', {
			element_id: element_id_,
			element_data: el_data_.properties || {},
			content: content_
		}, function(r_, j_){
			if(!r_ || !j_.success){
				self_._side_panel_body.html('<div class="panel-placeholder"><i class="material-icons">error</i><p>'+__('폼 로드 실패')+'</p></div>');
				return;
			}

			// 폼 HTML 주입
			self_._side_panel_body.html('<div class="le-edit-form-wrap">' + j_.form_html + '</div>');

			// Bootstrap 탭 초기화
			self_._side_panel_body.find('.pb-page-builder-element-edit-nav-tabs > .nav-tabs a:first').tab('show');

			// 편집 폼 참조 저장
			self_._edit_form = self_._side_panel_body.find('.le-edit-form-wrap');
		});

		// ── 적용 버튼 ──
		this._side_panel_footer.find('.le-apply-btn').off('click').on('click', function(){
			self_._apply_edit_changes();
		});

		// ── 취소 버튼 ──
		this._side_panel_footer.find('.le-cancel-btn').off('click').on('click', function(){
			self_._cancel_edit();
		});
	};

	/**
	 * 편집 폼에서 데이터를 수집하여 JSON 트리 업데이트 + 캔버스 리렌더
	 */
	pb_page_live_builder.prototype._apply_edit_changes = function(){
		var self_ = this;
		var path_ = this._editing_path;
		if(!path_ && path_ !== '0') return;

		// ── 폼 데이터 수집 ──
		var form_data_ = {};
		var content_ = '';

		this._edit_form.find('input, select, textarea').each(function(){
			var $input_ = $(this);
			var name_ = $input_.attr('name');
			if(!name_) return;

			// content 필드 별도 처리
			if(name_ === 'content'){
				content_ = $input_.val();
				return;
			}

			// checkbox
			if($input_.attr('type') === 'checkbox'){
				form_data_[name_] = $input_.is(':checked') ? $input_.val() : '';
				return;
			}

			// radio
			if($input_.attr('type') === 'radio'){
				if($input_.is(':checked')){
					form_data_[name_] = $input_.val();
				}
				return;
			}

			form_data_[name_] = $input_.val();
		});

		// ── JSON 트리 업데이트 ──
		var el_data_ = this._get_element_by_path(path_);
		if(!el_data_) return;

		// 폼 데이터를 요소의 properties에 병합
		if(!el_data_.properties) el_data_.properties = {};
		for(var key_ in form_data_){
			if(form_data_.hasOwnProperty(key_)){
				el_data_.properties[key_] = form_data_[key_];
			}
		}

		// ── child_sync 메타데이터 기반 자식 동기화 ──
		var el_meta_ = (window.pbpage_builder_element_map || {})[el_data_.name];
		if(el_meta_ && el_meta_.child_sync){
			var sync_ = el_meta_.child_sync;
			if(form_data_.hasOwnProperty(sync_.source_property)){
				this._sync_children(el_data_, form_data_[sync_.source_property], sync_);
			}
		}

		// content 업데이트 (비-배열 elementcontent인 경우만)
		if(content_ && !Array.isArray(el_data_['elementcontent'])){
			el_data_['elementcontent'] = content_;
		}

		// 히스토리 push
		this._push_history(__('요소 편집'));
		this._is_dirty = true;

		// ── 캔버스에 단일 요소 리렌더 ──
		this._rerender_element(path_, el_data_);

		// 적용 후 원본 데이터 갱신 (취소 시 적용된 변경이 되돌려지지 않도록)
		this._editing_original_data = JSON.parse(JSON.stringify(el_data_));

		// 사이드패널은 열린 상태 유지 (계속 편집 가능)
	};

	/**
	 * 편집 취소 — 원본 데이터 복원 후 사이드패널 닫기
	 */
	pb_page_live_builder.prototype._cancel_edit = function(){
		this._editing_path = null;
		this._editing_original_data = null;
		this._close_side_panel();
	};

	/**
	 * 단일 요소를 서버 렌더링하여 iframe DOM에 교체
	 */
	pb_page_live_builder.prototype._rerender_element = function(path_, el_data_){
		var self_ = this;
		var element_name_ = el_data_['name'];

		PB.post('live-editor-render-element', {
			element_name: element_name_,
			element_json: JSON.stringify(el_data_),
			element_path: path_
		}, function(r_, j_){
			if(!r_ || !j_.success){
				console.error('라이브에디터: 요소 리렌더 실패', j_);
				return;
			}

			var target_el_ = self_._element_index[path_];
			if(!target_el_) return;

			// iframe 문서 컨텍스트에서 새 요소 생성
			// (서버가 이미 data-live-edit-id 래퍼를 포함하여 반환)
			var iframe_doc_ = target_el_.ownerDocument;
			var temp_ = iframe_doc_.createElement('div');
			temp_.innerHTML = j_.rendered_html;
			var new_el_ = temp_.firstElementChild;

			if(new_el_){
				// 서버 응답의 래퍼로 기존 래퍼 전체 교체
				target_el_.parentNode.replaceChild(new_el_, target_el_);
			} else {
				target_el_.innerHTML = j_.rendered_html;
			}

			// 요소 스타일 CSS 업데이트 (margin, padding, background 등)
			if(j_.element_css){
				var unique_class_ = el_data_.properties ? el_data_.properties.unique_class_name : null;
				if(unique_class_){
					var style_id_ = 'pb-live-style-' + unique_class_;
					var existing_style_ = iframe_doc_.getElementById(style_id_);
					if(!existing_style_){
						existing_style_ = iframe_doc_.createElement('style');
						existing_style_.id = style_id_;
						iframe_doc_.head.appendChild(existing_style_);
					}
					existing_style_.textContent = j_.element_css;
				}
			}

			// 전체 경로 재인덱싱
			self_._reindex_all();

			// 교체된 요소에 드래그 핸들 + Sortable 보장
			self_._ensure_drag_handles(new_el_);
			self_._init_sortable_for_element(new_el_);

			// 선택 오버레이 재위치
			if(self_._selected_element_path){
				self_._show_select_overlay(self_._selected_element_path);
			}
		});
	};

	// =============================================================================
	// M4: 요소 추가 / 삭제 / 복제
	// =============================================================================

	/**
	 * child_sync용 자식 요소 JSON 생성 헬퍼
	 * element_map의 child_sync 메타데이터에 따라 범용 자식 요소를 생성
	 *
	 * @param {string} child_element_key_ 자식 요소 키 (예: 'column', 'tab_panel')
	 * @param {string} child_property_    세그먼트 값을 받을 프로퍼티명 (예: 'column_width')
	 * @param {string} seg_value_         해당 세그먼트의 값 (예: '6')
	 * @returns {object} 자식 요소 JSON
	 */
	pb_page_live_builder.prototype._create_child_element = function(child_element_key_, child_property_, seg_value_){
		var props_ = {
			unique_class_name: 'pb-element-class-' + PB.random_string(10, 'abcdefghijklmnopqrstuvwxyz0123456789')
		};
		props_[child_property_] = String(seg_value_);

		var child_meta_ = (window.pbpage_builder_element_map || {})[child_element_key_];
		return {
			name: child_element_key_,
			properties: props_,
			elementcontent: (child_meta_ && child_meta_.loadable) ? [] : ''
		};
	};

	/**
	 * child_sync 메타데이터 기반 자식 요소 동기화
	 * 부모 요소의 source_property 값을 delimiter로 분리하여 자식 생성/삭제/병합
	 *
	 * @param {object} parent_data_  부모 요소 JSON (직접 수정됨)
	 * @param {string} new_value_    source_property의 새 값 (예: "3:3:3:3")
	 * @param {object} sync_config_  element_map[key].child_sync 메타데이터
	 */
	pb_page_live_builder.prototype._sync_children = function(parent_data_, new_value_, sync_config_){
		var delimiter_ = sync_config_.delimiter || ':';
		var child_element_ = sync_config_.child_element;
		var child_property_ = sync_config_.child_property;
		var max_children_ = sync_config_.max_children || 0;
		var merge_on_reduce_ = sync_config_.merge_on_reduce !== false;

		var value_ = (new_value_ && new_value_ !== '') ? new_value_ : (sync_config_.default_value || '');
		var segments_ = value_.split(delimiter_);

		// max_children 제한
		if(max_children_ > 0 && segments_.length > max_children_){
			segments_ = segments_.slice(0, max_children_);
		}

		if(!Array.isArray(parent_data_.elementcontent)){
			parent_data_.elementcontent = [];
		}

		var existing_ = parent_data_.elementcontent;
		var new_count_ = segments_.length;
		var old_count_ = existing_.length;

		// ── 1) 기존 자식 프로퍼티 업데이트 + 부족분 새 자식 생성 ──
		for(var i_ = 0; i_ < new_count_; i_++){
			var seg_value_ = segments_[i_];

			if(i_ < old_count_){
				if(!existing_[i_].properties) existing_[i_].properties = {};
				existing_[i_].properties[child_property_] = String(seg_value_);
			}else{
				existing_.push(this._create_child_element(child_element_, child_property_, seg_value_));
			}
		}

		// ── 2) 초과 자식 제거 (merge_on_reduce 시 마지막 자식에 내용 병합) ──
		if(old_count_ > new_count_){
			if(merge_on_reduce_ && new_count_ > 0){
				var last_child_ = existing_[new_count_ - 1];
				if(!Array.isArray(last_child_.elementcontent)){
					last_child_.elementcontent = [];
				}
				for(var j_ = new_count_; j_ < old_count_; j_++){
					var removed_ = existing_[j_];
					if(Array.isArray(removed_.elementcontent)){
						for(var k_ = 0; k_ < removed_.elementcontent.length; k_++){
							last_child_.elementcontent.push(removed_.elementcontent[k_]);
						}
					}
				}
			}
			existing_.splice(new_count_);
		}

		// source_property 정규화
		parent_data_.properties[sync_config_.source_property] = segments_.join(delimiter_);
	};

	/**
	 * 새 요소를 JSON 트리에 추가하고 캔버스 리렌더
	 */
	pb_page_live_builder.prototype._add_element = function(element_key_){
		var self_ = this;
		var element_map_ = window.pbpage_builder_element_map || {};
		var el_meta_ = element_map_[element_key_];
		if(!el_meta_) return;

		// 새 요소 JSON 생성
		var new_element_ = {
			name: element_key_,
			properties: {
				unique_class_name: 'pb-element-class-' + PB.random_string(10, 'abcdefghijklmnopqrstuvwxyz0123456789')
			},
			elementcontent: el_meta_.loadable ? [] : ''
		};

		// ── child_sync 메타데이터가 있으면 자식 자동 생성 ──
		if(el_meta_.child_sync){
			var sync_ = el_meta_.child_sync;
			new_element_.properties[sync_.source_property] = sync_.default_value;
			this._sync_children(new_element_, sync_.default_value, sync_);
		}

		// JSON 트리에 삽입 + 삽입 위치 추적
		var insert_path_ = this._element_insert_path;
		var insert_pos_ = this._element_insert_pos;
		var parent_path_ = null;
		var insert_idx_ = 0;

		if(insert_pos_ === 'child' && insert_path_){
			// loadable 컨테이너에 자식으로 추가
			parent_path_ = insert_path_;
			var parent_data_ = this._get_element_by_path(parent_path_);
			if(!parent_data_) return;
			if(!Array.isArray(parent_data_.elementcontent)){
				parent_data_.elementcontent = [];
			}
			insert_idx_ = parent_data_.elementcontent.length;
			parent_data_.elementcontent.push(new_element_);

		}else if(insert_path_ && insert_pos_ !== 'append'){
			parent_path_ = this._get_parent_path(insert_path_);
			var parts_ = String(insert_path_).split('.');
			var idx_ = parseInt(parts_[parts_.length - 1]);
			var parent_content_ = parent_path_
				? this._get_element_by_path(parent_path_).elementcontent
				: this._builder_data.elementcontent;

			if(Array.isArray(parent_content_)){
				insert_idx_ = insert_pos_ === 'after' ? idx_ + 1 : idx_;
				parent_content_.splice(insert_idx_, 0, new_element_);
			}
		}else{
			if(!this._builder_data.elementcontent){
				this._builder_data.elementcontent = [];
			}
			insert_idx_ = this._builder_data.elementcontent.length;
			this._builder_data.elementcontent.push(new_element_);
		}

		// 히스토리
		this._push_history(__('요소 추가') + ': ' + (el_meta_.name || element_key_));

		// DOM 직접 삽입 (iframe 재로드 없음)
		this._render_and_insert_element(parent_path_, insert_idx_, new_element_);

		// 사이드패널 닫기
		this._close_side_panel();
	};

	/**
	 * 요소 삭제 — 확인 후 JSON 트리에서 제거
	 */
	pb_page_live_builder.prototype._delete_element = function(path_){
		var self_ = this;
		var el_data_ = this._get_element_by_path(path_);
		if(!el_data_) return;

		var display_name_ = this._get_element_display_name(el_data_['name'] || '');

		PB.confirm({
			title: __('요소 삭제'),
			content: __('"{name}" 요소를 삭제하시겠습니까?').replace('{name}', display_name_),
			button1: __('삭제하기')
		}, function(c_){
			if(!c_) return;
			var parent_path_ = self_._get_parent_path(path_);
			var parts_ = String(path_).split('.');
			var idx_ = parseInt(parts_[parts_.length - 1]);

			var parent_content_ = parent_path_
				? self_._get_element_by_path(parent_path_).elementcontent
				: self_._builder_data.elementcontent;

			if(Array.isArray(parent_content_)){
				parent_content_.splice(idx_, 1);
			}

			// 히스토리
			self_._push_history(__('요소 삭제') + ': ' + display_name_);

			// 선택 해제 + 오버레이 제거
			self_._deselect_element();

			// DOM에서 직접 제거 (iframe 재로드 없음)
			self_._remove_element_dom(path_);

			// 레이어 패널에서 삭제한 경우 트리 새로고침 (경로 재정렬)
			if(self_._active_panel === 'layers'){
				self_._load_layers_panel();
			}
		});
	};

	/**
	 * 요소 복제 — JSON 딥 클론 + ID 재생성 후 바로 뒤에 삽입
	 */
	pb_page_live_builder.prototype._duplicate_element = function(path_){
		var el_data_ = this._get_element_by_path(path_);
		if(!el_data_) return;

		// 딥 클론
		var clone_ = JSON.parse(JSON.stringify(el_data_));

		// ID 재생성 (재귀)
		this._regenerate_ids(clone_);

		// 부모 elementcontent에서 바로 뒤에 삽입
		var parent_path_ = this._get_parent_path(path_);
		var parts_ = String(path_).split('.');
		var idx_ = parseInt(parts_[parts_.length - 1]);

		var parent_content_ = parent_path_
			? this._get_element_by_path(parent_path_).elementcontent
			: this._builder_data.elementcontent;

		if(Array.isArray(parent_content_)){
			parent_content_.splice(idx_ + 1, 0, clone_);
		}

		// 히스토리
		var display_name_ = this._get_element_display_name(el_data_['name'] || '');
		this._push_history(__('요소 복제') + ': ' + display_name_);

		// DOM 직접 삽입 (iframe 재로드 없음)
		this._render_and_insert_element(parent_path_, idx_ + 1, clone_);

		// 레이어 패널에서 복제한 경우 트리 새로고침 (복제본 표시)
		if(this._active_panel === 'layers'){
			this._load_layers_panel();
		}
	};

	/**
	 * 요소를 같은 부모 내에서 위/아래로 이동
	 * @param {string} path_ - 이동할 요소 경로
	 * @param {number} direction_ - -1(위로), +1(아래로)
	 */
	pb_page_live_builder.prototype._move_element = function(path_, direction_){
		var parent_path_ = this._get_parent_path(path_);
		var parts_ = String(path_).split('.');
		var idx_ = parseInt(parts_[parts_.length - 1]);

		var parent_content_ = parent_path_
			? this._get_element_by_path(parent_path_).elementcontent
			: this._builder_data.elementcontent;

		if(!Array.isArray(parent_content_)) return;

		var new_idx_ = idx_ + direction_;
		if(new_idx_ < 0 || new_idx_ >= parent_content_.length) return;

		// 위치 교환
		var item_ = parent_content_.splice(idx_, 1)[0];
		parent_content_.splice(new_idx_, 0, item_);

		// 새 경로 계산
		parts_[parts_.length - 1] = String(new_idx_);
		var new_path_ = parts_.join('.');

		this._push_history(__('요소 이동'));

		// DOM 직접 교환 (iframe 재로드 없음)
		this._swap_element_dom(path_, new_path_);

		// 새 경로로 즉시 선택 복원
		this._selected_element_path = null;
		if(this._element_index && this._element_index[new_path_]){
			this._select_element(new_path_);
		}
	};

	/**
	 * 요소 JSON의 id와 unique_class_name을 재귀적으로 재생성
	 */
	pb_page_live_builder.prototype._regenerate_ids = function(el_data_){
		if(!el_data_) return;

		// properties 안의 id, unique_class_name 재생성
		if(!el_data_['properties']) el_data_['properties'] = {};
		el_data_['properties']['id'] = '';
		el_data_['properties']['unique_class_name'] = 'pb-element-class-' + PB.random_string(10, 'abcdefghijklmnopqrstuvwxyz0123456789');

		if(Array.isArray(el_data_['elementcontent'])){
			for(var i_ = 0; i_ < el_data_['elementcontent'].length; i_++){
				this._regenerate_ids(el_data_['elementcontent'][i_]);
			}
		}
	};

	// =============================================================================
	// 유틸리티
	// =============================================================================

	/**
	 * JSON 트리에서 경로(path)로 요소 찾기
	 * @param {string} path_ - "0", "0.2", "0.2.1" 형태
	 * @return {object|null} 해당 요소 데이터
	 */
	pb_page_live_builder.prototype._get_element_by_path = function(path_){
		if(!path_ && path_ !== 0) return null;

		var parts_ = String(path_).split('.');
		var current_ = this._builder_data.elementcontent;

		for(var i_ = 0; i_ < parts_.length; i_++){
			var idx_ = parseInt(parts_[i_]);
			if(!current_ || !Array.isArray(current_) || idx_ >= current_.length) return null;

			if(i_ === parts_.length - 1){
				return current_[idx_];
			}
			current_ = current_[idx_].elementcontent;
		}

		return null;
	};

	/**
	 * 요소의 부모 경로 반환
	 * @param {string} path_ - "0.2.1" → "0.2"
	 * @return {string|null}
	 */
	pb_page_live_builder.prototype._get_parent_path = function(path_){
		var parts_ = String(path_).split('.');
		if(parts_.length <= 1) return null;
		parts_.pop();
		return parts_.join('.');
	};

	/**
	 * 요소 이름을 한국어 표시명으로 변환
	 */
	pb_page_live_builder.prototype._get_element_display_name = function(element_name_){
		var map_ = window.pbpage_builder_element_map;
		if(map_ && map_[element_name_] && map_[element_name_]['name']){
			return map_[element_name_]['name'];
		}
		return element_name_;
	};

	// =============================================================================
	// 캔버스 iframe 드래그앤드롭 (SortableJS)
	// =============================================================================

	/**
	 * 캔버스 iframe 내에 SortableJS 초기화
	 * loadable 컨테이너 요소에 Sortable 인스턴스 생성 + 드래그 핸들/CSS 주입
	 */
	pb_page_live_builder.prototype._init_canvas_sortable = function(){
		var self_ = this;
		var iframe_ = this._canvas_iframe[0];
		var doc_ = iframe_.contentDocument;
		var win_ = iframe_.contentWindow;
		if(!doc_ || !win_ || !win_.Sortable) return;

		var SortableJS_ = win_.Sortable;
		var map_ = window.pbpage_builder_element_map || {};
		var primary_color_ = '#088edb';

		// ── 1. iframe에 드래그 관련 CSS 주입 ──
		var style_ = doc_.createElement('style');
		style_.textContent =
			'[data-drag-handle]{' +
				'display:none;position:absolute;top:0;left:0;z-index:9999;' +
				'width:20px;height:20px;cursor:grab;' +
				'background:rgba(8,142,219,0.85);' +
				'border-radius:0 0 4px 0;' +
				'user-select:none;-webkit-user-select:none;' +
				"background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='%23fff'%3E%3Ccircle cx='7' cy='5' r='1.5'/%3E%3Ccircle cx='13' cy='5' r='1.5'/%3E%3Ccircle cx='7' cy='10' r='1.5'/%3E%3Ccircle cx='13' cy='10' r='1.5'/%3E%3Ccircle cx='7' cy='15' r='1.5'/%3E%3Ccircle cx='13' cy='15' r='1.5'/%3E%3C/svg%3E\");" +
				'background-repeat:no-repeat;background-position:center;' +
			'}' +
			'[data-live-edit-id]:hover>[data-drag-handle]{display:block;}' +
			'.le-canvas-sort-ghost{opacity:0.3;}' +
			'.le-canvas-sort-chosen{outline:2px solid ' + primary_color_ + ';outline-offset:-2px;}' +
			'[data-live-edit-content-empty]{' +
				'min-height:40px;' +
				'border:2px dashed #ddd;border-radius:4px;' +
			'}';
		doc_.head.appendChild(style_);

		// ── 2. 각 편집 가능 요소에 드래그 핸들 삽입 ──
		var all_edits_ = doc_.querySelectorAll('[data-live-edit-id]');
		for(var i_ = 0; i_ < all_edits_.length; i_++){
			var el_ = all_edits_[i_];
			// position 필요 (핸들이 absolute)
			var pos_ = win_.getComputedStyle(el_).position;
			if(pos_ === 'static') el_.style.position = 'relative';

			var handle_ = doc_.createElement('span');
			handle_.setAttribute('data-drag-handle', '');
			el_.insertBefore(handle_, el_.firstChild);
		}

		// ── 공통 Sortable 옵션 ──
		var sort_on_start_ = function(){
			self_._deselect_element();
			self_._clear_hover_overlay();
			self_._overlay_layer.hide();
		};
		var sort_on_end_ = function(evt_){
			self_._overlay_layer.show();
			self_._on_canvas_sort_end(evt_);
		};
		var base_opts_ = {
			draggable: '>[data-live-edit-id]',
			handle: '[data-drag-handle]',
			animation: 150,
			ghostClass: 'le-canvas-sort-ghost',
			chosenClass: 'le-canvas-sort-chosen',
			swapThreshold: 0.65,
			onStart: sort_on_start_,
			onEnd: sort_on_end_
		};

		// ── 3. loadable 컨테이너에 Sortable 인스턴스 생성 ──
		// 주의: loadable 요소의 자식 [data-live-edit-id]가 직접 자식이 아닐 수 있음
		// (예: container → .pb-container-wrapper → .pb-container.box → [data-live-edit-id])
		// 따라서 실제 자식을 담고 있는 "내부 콘텐츠 컨테이너"를 찾아서 Sortable 생성
		var loadable_els_ = doc_.querySelectorAll('[data-live-edit-loadable]');
		for(var j_ = 0; j_ < loadable_els_.length; j_++){
			var loadable_el_ = loadable_els_[j_];

			// 내부 콘텐츠 컨테이너 탐색: 첫 번째 자식 [data-live-edit-id]의 부모
			// (예: container → .pb-container-wrapper → .pb-container.box → children)
			var first_child_edit_ = loadable_el_.querySelector('[data-live-edit-id]');
			var content_container_ = first_child_edit_ ? first_child_edit_.parentNode : loadable_el_;

			// 빈 컨테이너에 드롭존 마커 추가
			if(!first_child_edit_){
				content_container_.setAttribute('data-live-edit-content-empty', '');
			}

			SortableJS_.create(content_container_, $.extend({}, base_opts_, {
				group: {
					name: 'le-canvas-sort',
					put: function(to_, from_, dragEl_){
						// to_.el이 내부 컨테이너일 수 있으므로 [data-live-edit-name]까지 탐색
						var p_ = to_.el;
						while(p_ && (!p_.hasAttribute || !p_.hasAttribute('data-live-edit-name'))){
							p_ = p_.parentNode;
						}
						var parent_name_ = p_ ? p_.getAttribute('data-live-edit-name') : null;
						var child_name_ = dragEl_.getAttribute('data-live-edit-name');
						return self_._can_accept_child(parent_name_, child_name_);
					}
				},
				fallbackOnBody: false
			}));

			// 네스팅 버그 방지: 내부 자식의 드래그 핸들 이벤트가 부모 Sortable로 전파 차단
			// handle의 소유자가 이 loadable 요소 자체가 아니고 내부에 포함된 경우만 차단
			(function(el_){
				var stop_nested_ = function(e_){
					var t_ = e_.target;
					if(!t_ || !t_.closest) return;
					var h_ = t_.closest('[data-drag-handle]');
					if(!h_) return;
					var owner_ = h_.closest('[data-live-edit-id]');
					if(owner_ && owner_ !== el_ && el_.contains(owner_)){
						e_.stopPropagation();
					}
				};
				el_.addEventListener('pointerdown', stop_nested_, false);
				el_.addEventListener('mousedown', stop_nested_, false);
			})(loadable_el_);
		}

		// ── 4. 루트 컨테이너(body 내 콘텐츠 영역)에도 Sortable ──
		var first_root_ = doc_.querySelector('[data-live-edit-id]');
		if(first_root_){
			var root_container_ = first_root_.parentNode;
			if(root_container_ && !root_container_.hasAttribute('data-live-edit-loadable')){
				SortableJS_.create(root_container_, $.extend({}, base_opts_, {
					group: {
						name: 'le-canvas-sort',
						put: true
					}
				}));
			}
		}
	};

	/**
	 * 동적으로 삽입/교체된 요소에 드래그 핸들 보장
	 * _render_and_insert_element, _rerender_element 후 호출
	 * @param {Element} root_el_ - 핸들을 확인/생성할 루트 DOM 요소
	 */
	pb_page_live_builder.prototype._ensure_drag_handles = function(root_el_){
		var iframe_ = this._canvas_iframe[0];
		var win_ = iframe_.contentWindow;
		if(!win_ || !root_el_) return;

		// root_el_ 자체 + 하위의 모든 [data-live-edit-id] 요소 대상
		var targets_ = [];
		if(root_el_.hasAttribute && root_el_.hasAttribute('data-live-edit-id')){
			targets_.push(root_el_);
		}
		var children_ = root_el_.querySelectorAll('[data-live-edit-id]');
		for(var i_ = 0; i_ < children_.length; i_++) targets_.push(children_[i_]);

		for(var j_ = 0; j_ < targets_.length; j_++){
			var el_ = targets_[j_];
			// 이미 핸들 있으면 스킵
			if(el_.querySelector(':scope > [data-drag-handle]')) continue;

			// position 보정 (핸들이 absolute)
			var pos_ = win_.getComputedStyle(el_).position;
			if(pos_ === 'static') el_.style.position = 'relative';

			var handle_ = el_.ownerDocument.createElement('span');
			handle_.setAttribute('data-drag-handle', '');
			el_.insertBefore(handle_, el_.firstChild);
		}
	};

	/**
	 * 동적으로 삽입/교체된 loadable 요소에 Sortable 인스턴스 재생성
	 * _rerender_element 후 호출
	 * @param {Element} el_ - Sortable을 확인/생성할 DOM 요소
	 */
	pb_page_live_builder.prototype._init_sortable_for_element = function(el_){
		var iframe_ = this._canvas_iframe[0];
		var win_ = iframe_.contentWindow;
		if(!win_ || !win_.Sortable || !el_) return;
		var self_ = this;
		var SortableJS_ = win_.Sortable;

		// el_ 자체 또는 하위의 [data-live-edit-loadable] 요소 탐색
		var loadables_ = [];
		if(el_.hasAttribute && el_.hasAttribute('data-live-edit-loadable')) loadables_.push(el_);
		var nested_ = el_.querySelectorAll('[data-live-edit-loadable]');
		for(var i_ = 0; i_ < nested_.length; i_++) loadables_.push(nested_[i_]);
		if(!loadables_.length) return;

		// Sortable 공통 옵션
		var sort_on_start_ = function(){
			self_._deselect_element();
			self_._clear_hover_overlay();
			self_._overlay_layer.hide();
		};
		var sort_on_end_ = function(evt_){
			self_._overlay_layer.show();
			self_._on_canvas_sort_end(evt_);
		};
		var base_opts_ = {
			draggable: '>[data-live-edit-id]',
			handle: '[data-drag-handle]',
			animation: 150,
			ghostClass: 'le-canvas-sort-ghost',
			chosenClass: 'le-canvas-sort-chosen',
			swapThreshold: 0.65,
			onStart: sort_on_start_,
			onEnd: sort_on_end_
		};

		for(var j_ = 0; j_ < loadables_.length; j_++){
			var loadable_el_ = loadables_[j_];

			// 내부 콘텐츠 컨테이너 탐색
			var first_child_edit_ = loadable_el_.querySelector('[data-live-edit-id]');
			var content_container_ = first_child_edit_ ? first_child_edit_.parentNode : loadable_el_;

			// 빈 컨테이너에 드롭존 마커
			if(!first_child_edit_){
				content_container_.setAttribute('data-live-edit-content-empty', '');
			}

			SortableJS_.create(content_container_, $.extend({}, base_opts_, {
				group: {
					name: 'le-canvas-sort',
					put: function(to_, from_, dragEl_){
						var p_ = to_.el;
						while(p_ && (!p_.hasAttribute || !p_.hasAttribute('data-live-edit-name'))){
							p_ = p_.parentNode;
						}
						var parent_name_ = p_ ? p_.getAttribute('data-live-edit-name') : null;
						var child_name_ = dragEl_.getAttribute('data-live-edit-name');
						return self_._can_accept_child(parent_name_, child_name_);
					}
				},
				fallbackOnBody: false
			}));

			// 네스팅 버그 방지
			(function(el_ref_){
				var stop_nested_ = function(e_){
					var t_ = e_.target;
					if(!t_ || !t_.closest) return;
					var h_ = t_.closest('[data-drag-handle]');
					if(!h_) return;
					var owner_ = h_.closest('[data-live-edit-id]');
					if(owner_ && owner_ !== el_ref_ && el_ref_.contains(owner_)){
						e_.stopPropagation();
					}
				};
				el_ref_.addEventListener('pointerdown', stop_nested_, false);
				el_ref_.addEventListener('mousedown', stop_nested_, false);
			})(loadable_el_);
		}
	};

	/**
	 * 캔버스 드래그 완료 핸들러
	 * iframe 내 DOM 이동을 JSON 트리에 반영
	 */
	pb_page_live_builder.prototype._on_canvas_sort_end = function(evt_){
		var dragged_ = evt_.item;
		var old_path_ = dragged_.getAttribute('data-live-edit-id');
		if(!old_path_) return;

		// 새 부모 정보: evt_.to가 내부 콘텐츠 컨테이너일 수 있으므로
		// [data-live-edit-id]를 가진 가장 가까운 조상까지 탐색
		var new_parent_path_ = null;
		var to_ = evt_.to;
		while(to_){
			if(to_.hasAttribute && to_.hasAttribute('data-live-edit-id')){
				new_parent_path_ = to_.getAttribute('data-live-edit-id');
				break;
			}
			to_ = to_.parentElement;
		}
		var new_idx_ = evt_.newIndex;

		// 이전 위치 파싱
		var old_parts_ = String(old_path_).split('.');
		var old_idx_ = parseInt(old_parts_[old_parts_.length - 1]);
		var old_parent_path_ = old_parts_.length > 1 ? old_parts_.slice(0, -1).join('.') : null;

		var old_parent_content_ = old_parent_path_
			? this._get_element_by_path(old_parent_path_).elementcontent
			: this._builder_data.elementcontent;

		var new_parent_content_ = new_parent_path_
			? this._get_element_by_path(new_parent_path_).elementcontent
			: this._builder_data.elementcontent;

		var same_parent_ = (old_parent_path_ === new_parent_path_)
			|| (!old_parent_path_ && !new_parent_path_);

		// 유효성 검증 (다른 부모로 이동 시)
		var element_data_ = old_parent_content_[old_idx_];
		if(!same_parent_){
			var target_parent_el_ = new_parent_path_ ? this._get_element_by_path(new_parent_path_) : null;
			var target_parent_name_ = target_parent_el_ ? target_parent_el_.name : null;
			if(!this._can_accept_child(target_parent_name_, element_data_.name)){
				// 이동 불가 → 캔버스 리렌더로 원복
				this._render_canvas();
				return;
			}
		}

		// JSON 트리 업데이트
		element_data_ = old_parent_content_.splice(old_idx_, 1)[0];

		if(same_parent_ && old_idx_ < new_idx_) new_idx_--;

		new_parent_content_.splice(new_idx_, 0, element_data_);

		this._push_history(__('요소 이동'));

		// 캔버스 전체 리렌더 (path 재인덱싱)
		this._render_canvas();

		// 레이어 패널 갱신
		if(this._current_panel === 'layers'){
			var self_ = this;
			setTimeout(function(){ self_._load_layers_panel(); }, 300);
		}
	};

	/**
	 * 부모-자식 관계 유효성 검증
	 * element_map의 parent/children 규칙을 체크
	 * @param {string} parent_name_ - 부모 요소 타입 (null이면 루트)
	 * @param {string} child_name_ - 자식 요소 타입
	 * @return {boolean}
	 */
	pb_page_live_builder.prototype._can_accept_child = function(parent_name_, child_name_){
		// 루트 레벨은 항상 허용
		if(!parent_name_) return true;

		var map_ = window.pbpage_builder_element_map || {};
		var parent_meta_ = map_[parent_name_];
		var child_meta_ = map_[child_name_];

		// 부모가 loadable이 아니면 자식 불가
		if(!parent_meta_ || !parent_meta_.loadable) return false;

		// parent.children 검증: ["*","!container"] 형식
		var children_exp_ = parent_meta_.children || ['*'];
		var child_ok_ = children_exp_.indexOf('*') >= 0 || children_exp_.indexOf(child_name_) >= 0;
		if(children_exp_.indexOf('!' + child_name_) >= 0) child_ok_ = false;

		// child.parent 검증: ["*","!row"] 형식
		var parent_exp_ = child_meta_ ? (child_meta_.parent || ['*']) : ['*'];
		var parent_ok_ = parent_exp_.indexOf('*') >= 0 || parent_exp_.indexOf(parent_name_) >= 0;
		if(parent_exp_.indexOf('!' + parent_name_) >= 0) parent_ok_ = false;

		return child_ok_ && parent_ok_;
	};

});
