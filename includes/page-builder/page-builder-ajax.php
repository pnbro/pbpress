<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_page_builder_check_children_exp($element_id_, $exp_){
	$result_ = (in_array($element_id_, $exp_) || in_array("*", $exp_)) && !in_array("!".$element_id_, $exp_);
	return $result_;
}

function _pb_page_builder_parse_children_exp($exp_){
	$results_ = array();	
	$temp_page_builder_elements_ = pb_page_builder_elements();

	foreach($temp_page_builder_elements_ as $element_id_ => $element_data_){
		if(_pb_page_builder_check_children_exp($element_id_, $exp_)){
			$results_[$element_id_] = $element_data_;
		}
	}

	return $results_;
}

function _pb_page_builder_ajax_load_elements(){
	$options_ = _POST('options');
	$parent_id_ = isset($options_['parent']) ? $options_['parent'] : null;
	$keyword_ = isset($options_['keyword']) ? $options_['keyword'] : null;
	
	$elements_ = isset($options_['elements']) ? $options_['elements'] : null;
	$included_elements_ = isset($options_['included_elements']) ? $options_['included_elements'] : null;

	if(gettype($elements_) !== 'array'){
		if(strlen($elements_)) $elements_ = explode(",", $elements_);
		else $elements_ = array();
	}

	if(gettype($included_elements_) !== 'array'){
		if(strlen($included_elements_)) $included_elements_ = explode(",", $included_elements_);
		else $included_elements_ = array();
	}

	$temp_page_builder_elements_ = pb_page_builder_elements();
	$page_builder_elements_ = array();

	$parent_element_data_ = strlen($parent_id_) && isset($temp_page_builder_elements_[$parent_id_]) ? $temp_page_builder_elements_[$parent_id_] : null;

	if(isset($parent_element_data_) && isset($parent_element_data_['children'])){
		$temp_page_builder_elements_ = _pb_page_builder_parse_children_exp($parent_element_data_['children']);
	}

	foreach($temp_page_builder_elements_ as $key_ => $page_builder_element_){
		if(strlen($keyword_)){
			if(strpos($page_builder_element_['name'], $keyword_) === false) continue;
		}
		if(count($elements_) > 0){
			if(in_array($key_, $elements_) === false) continue;
		}
		
		if(count($included_elements_) > 0){
			if(in_array($key_, $included_elements_) === false) continue;
		}

		if(isset($page_builder_element_['parent'])){
			if(!_pb_page_builder_check_children_exp($parent_id_, $page_builder_element_['parent'])) continue;
		}

		$page_builder_elements_[$key_] = $page_builder_element_;
	}

	echo json_encode(array(
		'success' => true,
		'elements' => $page_builder_elements_,
	));

	pb_end();
}
pb_add_ajax('page-builder-load-element', '_pb_page_builder_ajax_load_elements');

function _pb_page_builder_render_edit_form_group($func_){

}

function _pb_page_builder_ajax_load_edit_element_form(){
	$element_id_ = _POST('element_id');
	$defaults_ = _POST('element_data');
	$content_ = _POST('content');
	
	$temp_page_builder_elements_ = pb_page_builder_elements();
	$element_data_ = $temp_page_builder_elements_[$element_id_];
	$element_class_ = pb_page_builder_element_class($element_id_);

	global $pb_page_builder_element_edit_category_funcs;

	$edit_categories_ = pb_page_builder_element_edit_categories();
	$edit_category_functions_ = $pb_page_builder_element_edit_category_funcs;

	$element_edit_categories_ = isset($element_data_['edit_categories']) ? $element_data_['edit_categories'] : array("common");

	ob_start();
?>
<div class="pb-page-builder-element-edit-nav-tabs">

	<ul class="nav nav-tabs" role="tablist">
		<?php for($index_=0;$index_<count($edit_categories_); ++$index_){
			$category_data_ = $edit_categories_[$index_];

			if(!in_array($category_data_['key'], $element_edit_categories_)){
				continue;
			}
		?>
			<li role="presentation" class=""><a href="#manage-site-tab-<?=$category_data_['key']?>" role="tab" data-toggle="tab"><?=$category_data_['title']?></a></li>
		<?php } ?>
		
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<?php for($index_=0;$index_<count($edit_categories_); ++$index_){
			$category_data_ = $edit_categories_[$index_];
			if(!in_array($category_data_['key'], $element_edit_categories_)){
				continue;
			}

			$element_edit_forms_ = $element_class_->edit_forms($category_data_['key']);
		?>
			<div role="tabpanel" class="tab-pane" id="manage-site-tab-<?=$category_data_['key']?>">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><?=$category_data_['title']?></h3>
					</div>
					<div class="panel-body">
					<?php foreach($edit_category_functions_[$category_data_['key']] as $edit_form_){

						if(is_callable($edit_form_['render'])){
							call_user_func_array($edit_form_['render'], array($defaults_, $content_));	
						}else{
							pb_page_builder_element_edit_form_render($edit_form_['render'], $defaults_, $content_);
						}

						
					} ?>

					<?php foreach($element_edit_forms_ as $edit_form_){
						if(is_callable($edit_form_['render'])){
							call_user_func_array($edit_form_['render'], array($defaults_, $content_));	
						}else{
							pb_page_builder_element_edit_form_render($edit_form_['render'], $defaults_, $content_);
						}
					} ?>
					</div>
				</div>
			</div>
		<?php } ?>

		
	</div>
</div>


<?php
	$form_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'form_html' => $form_html_,
	));

	pb_end();
}
pb_add_ajax('page-builder-load-edit-form', '_pb_page_builder_ajax_load_edit_element_form');

function _pb_page_builder_ajax_render_element_custom_preview(){
	$element_id_ = _POST('key');
	$field_name_ = _POST('field_name');
	$element_data_ = _POST('element_data');
	$content_ = _POST('content');

	$temp_page_builder_elements_ = pb_page_builder_elements();

	$target_element_ = $temp_page_builder_elements_[$element_id_];

	$preview_fields_ = $target_element_['preview_fields'];
	$target_preview_data_ = null;

	foreach($preview_fields_ as $preview_field_){
		if($preview_field_['name'] === $field_name_){
			$target_preview_data_ = $preview_field_;
			break;
		}
	}

	ob_start();
	call_user_func_array($target_preview_data_['func'], array($element_data_, $content_));
	$preview_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'preview_html' => $preview_html_,
	));

	pb_end();	
}

pb_add_ajax('page-builder-render-element-custom-preview', '_pb_page_builder_ajax_render_element_custom_preview');


function _pb_page_builder_ajax_render_element_render_preview(){
	$element_id_ = _POST('key');
	$element_data_ = _POST('element_data');
	$content_ = _POST('content');

	$temp_page_builder_elements_ = pb_page_builder_elements();

	$target_element_ = $temp_page_builder_elements_[$element_id_];
	$preview_render_ = $target_element_['preview'];
	
	ob_start();
	call_user_func_array($preview_render_, array($element_data_, $content_));
	$preview_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'preview_html' => $preview_html_,
	));

	pb_end();	
}
pb_add_ajax('page-builder-render-element-render-preview', '_pb_page_builder_ajax_render_element_render_preview');

// =============================================================================
// 라이브에디터: 전체 페이지 렌더링
// =============================================================================
function _pb_live_editor_ajax_render_page(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$builder_json_str_ = _POST('builder_json');
	if(!strlen($builder_json_str_)){
		pb_ajax_error(__("잘못된 요청"), __("빌더 데이터가 누락되었습니다."));
	}

	$builder_data_ = pb_page_builder_parse($builder_json_str_);

	if(pb_is_error($builder_data_)){
		pb_ajax_error($builder_data_->error_title(), $builder_data_->error_message());
	}

	$settings_ = $builder_data_['settings'];
	$page_contents_ = $builder_data_['elementcontent'];

	$element_map_ = pb_page_builder_elements();
	global $pb_page_builder_element_classes;

	// ── 글로벌 $pbpage 설정 (테마 header/footer가 참조) ──
	global $pbpage, $pbpage_meta_map;
	$page_id_ = _POST('page_id');
	if(strlen($page_id_)){
		$pbpage = pb_page($page_id_);
		if(!isset($pbpage_meta_map)) $pbpage_meta_map = array();
	}
	if(!isset($pbpage)){
		$pbpage = array(
			'id' => $page_id_,
			'page_title' => '',
			'page_html' => $builder_json_str_,
			'status' => PB_PAGE_STATUS::PUBLISHED,
			'slug' => '',
		);
		if(!isset($pbpage_meta_map)) $pbpage_meta_map = array();
	}

	// ── 경로 추적을 위한 글로벌 카운터 초기화 ──
	$GLOBALS['_live_edit_counter'] = array(); // depth별 인덱스 카운터
	$GLOBALS['_live_edit_depth'] = 0;

	// ── 렌더링 전 필터 등록: 각 요소에 data-live-edit-id 마커 삽입 ──
	pb_hook_set_filter('pb_page_builder_element_render_function', '_live_editor_wrap_', '_pb_live_editor_wrap_render', 10);

	// ── pb_head 훅에 라이브에디터 캔버스 스타일 주입 ──
	$_le_settings_ = $settings_;
	$_le_builder_data_ = $builder_data_;
	pb_hook_set_action('pb_head', '_live_editor_head_style_', function() use ($_le_settings_, $_le_builder_data_){
		echo '<style type="text/css">' . pb_hook_apply_filters('pb_page_builder_global_style', $_le_settings_['style'], $_le_builder_data_) . '</style>';
		echo '<style type="text/css">
/* 라이브에디터 캔버스 기본 스타일 */
[data-live-edit-id]{ position: relative; min-height: 20px; }
[data-live-edit-id]:empty{ min-height: 40px; border: 1px dashed #ccc; }
</style>';
	}, 99);

	// 테마 header + 요소 렌더링 + 테마 footer
	// 렌더링 중 PHP 에러가 pb_ajax_error → exit로 이어지면
	// ob 버퍼가 flush되어 HTML+JSON이 섞이므로, 임시 에러 핸들러로 보호
	$_prev_error_handler_ = set_error_handler(function($severity_, $message_, $file_, $line_) {
		if(!(error_reporting() & $severity_)) return false;
		// 심각한 에러만 예외로 변환, 경고/알림은 억제하여 렌더링 계속 진행
		if($severity_ & (E_USER_ERROR | E_RECOVERABLE_ERROR)) {
			throw new ErrorException($message_, 0, $severity_, $file_, $line_);
		}
		return true; // 경고/알림 억제 (PBPress 기본 핸들러가 pb_ajax_error → exit 호출하는 것 방지)
	});

	$_ob_level_ = ob_get_level();
	ob_start();

	try {
		// 테마 page-setup.php 로드 (있을 경우만)
		$header_name_ = null;
		$footer_name_ = null;
		$_page_setup_path_ = pb_current_theme_path() . "page-setup.php";
		if(file_exists($_page_setup_path_)){
			include($_page_setup_path_);
		}

		pb_theme_header($header_name_);

		// 콘텐츠 영역 시작 마커 (요소 삽입 위치 기준점)
		echo '<span id="pb-live-edit-content-start" style="display:none"></span>';

		// 정상 렌더링 파이프라인 사용 (_render → render_content → 재귀)
		if(is_array($page_contents_)){
			foreach($page_contents_ as $element_data_){
				if(!isset($element_data_['name']) || !isset($pb_page_builder_element_classes[$element_data_['name']])) continue;
				$element_class_ = $pb_page_builder_element_classes[$element_data_['name']];
				$content_ = isset($element_data_['elementcontent']) ? $element_data_['elementcontent'] : '';
				call_user_func_array(array($element_class_, "_render"), array($element_data_, $content_));
			}
		}

		echo '<script type="text/javascript">' . pb_hook_apply_filters('pb_page_builder_global_script', $settings_['script'], $builder_data_) . '</script>';

		// 콘텐츠 영역 끝 마커 (footer 앞 삽입 위치 기준점)
		echo '<span id="pb-live-edit-content-end" style="display:none"></span>';

		pb_theme_footer($footer_name_);
	} catch (Exception $e_) {
		// 렌더링 중 생성된 중첩 ob 버퍼 모두 정리
		while(ob_get_level() > $_ob_level_) {
			ob_end_clean();
		}
		restore_error_handler();
		pb_ajax_error("RENDER ERROR", "[{$e_->getFile()}, line : {$e_->getLine()}]{$e_->getMessage()}");
	}

	restore_error_handler();
	$page_html_ = ob_get_clean();

	// pb_end()에서 exit하므로 글로벌/필터 자동 정리됨

	$response_ = json_encode(array(
		'success' => true,
		'page_html' => $page_html_,
	));

	if($response_ === false){
		$page_html_ = mb_convert_encoding($page_html_, 'UTF-8', 'UTF-8');
		$response_ = json_encode(array(
			'success' => true,
			'page_html' => $page_html_,
		), JSON_INVALID_UTF8_SUBSTITUTE);
	}

	echo $response_;

	pb_end();
}

/**
 * 라이브에디터용 렌더 래핑 필터
 *
 * pb_page_builder_element_render_function 필터에서 호출되어,
 * 각 요소의 렌더링 결과를 data-live-edit-id 래퍼로 감쌈.
 * 경로는 depth/카운터 기반으로 자동 추적.
 */
function _pb_live_editor_wrap_render($render_func_, $data_, $content_){
	$depth_ = &$GLOBALS['_live_edit_depth'];
	$counter_ = &$GLOBALS['_live_edit_counter'];

	// 현재 depth의 카운터 초기화
	if(!isset($counter_[$depth_])){
		$counter_[$depth_] = 0;
	}

	// 경로 생성 (예: "0", "0.0", "0.1", "1.0.2")
	$path_parts_ = array();
	for($i_ = 0; $i_ <= $depth_; $i_++){
		$path_parts_[] = isset($counter_[$i_]) ? $counter_[$i_] : 0;
	}
	$current_path_ = implode('.', $path_parts_);
	$element_name_ = isset($data_['name']) ? $data_['name'] : 'unknown';

	// 자식 렌더링을 위해 depth 증가 + 하위 카운터 리셋
	$depth_++;
	unset($counter_[$depth_]);

	// 원본 render 함수 실행 + 출력 캡처
	ob_start();
	call_user_func_array($render_func_, array($data_, $content_));
	$rendered_html_ = ob_get_clean();

	// depth 복원
	$depth_--;

	// 마커 속성 준비
	$rendered_html_ = trim($rendered_html_);
	static $element_map_ = null;
	if($element_map_ === null) $element_map_ = pb_page_builder_elements();
	$loadable_attr_ = (isset($element_map_[$element_name_]['loadable']) && $element_map_[$element_name_]['loadable']) ? ' data-live-edit-loadable="1"' : '';
	$marker_attrs_ = ' data-live-edit-id="'.htmlspecialchars($current_path_).'"'
		.' data-live-edit-name="'.htmlspecialchars($element_name_).'"'
		.$loadable_attr_;

	// 렌더링된 HTML 루트 태그에 직접 속성 주입 (래퍼 div 없이)
	// → CSS 레이아웃(flexbox, grid, Bootstrap row>col 등) 깨짐 방지
	if(preg_match('/^<[a-zA-Z]/', $rendered_html_)){
		$rendered_html_ = preg_replace('/^(<[a-zA-Z][a-zA-Z0-9]*)/', '$1'.$marker_attrs_, $rendered_html_, 1);
		echo $rendered_html_;
	} else {
		// 태그로 시작하지 않는 경우 래퍼 div 폴백
		echo '<div'.$marker_attrs_.'>'.$rendered_html_.'</div>';
	}

	// 현재 depth 카운터 증가
	$counter_[$depth_]++;

	// 원래 함수 대신 이미 출력했으므로 빈 함수 반환
	return function(){};
}

pb_add_ajax('live-editor-render-page', '_pb_live_editor_ajax_render_page');

// =============================================================================
// 라이브에디터: 단일 요소 렌더링 (M3에서 활용)
// =============================================================================
function _pb_live_editor_ajax_render_element(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한없음"), __("접근권한이 없습니다."));
	}

	$element_name_ = _POST('element_name');
	$element_json_ = _POST('element_json'); // 전체 요소 JSON
	$element_path_ = _POST('element_path');

	if(!strlen($element_name_)){
		pb_ajax_error(__("잘못된 요청"), __("요소 이름이 누락되었습니다."));
	}

	global $pb_page_builder_element_classes;
	$element_map_ = pb_page_builder_elements();

	if(!isset($pb_page_builder_element_classes[$element_name_])){
		pb_ajax_error(__("잘못된 요청"), __("존재하지 않는 요소입니다."));
	}

	$element_data_ = json_decode($element_json_, true);
	if(!is_array($element_data_)){
		pb_ajax_error(__("잘못된 요청"), __("요소 데이터가 올바르지 않습니다."));
	}

	$element_class_ = $pb_page_builder_element_classes[$element_name_];
	$content_ = isset($element_data_['elementcontent']) ? $element_data_['elementcontent'] : '';

	// 자식 요소 마커를 위해 필터 등록 (경로 기반)
	$base_path_ = strlen($element_path_) ? $element_path_ : '0';
	$GLOBALS['_live_edit_counter'] = array();
	$GLOBALS['_live_edit_depth'] = 0;

	// 자식 렌더링 시 마커 삽입 (depth 0은 건너뜀 — JS에서 래퍼 유지)
	pb_hook_set_filter('pb_page_builder_element_render_function', '_live_editor_wrap_', '_pb_live_editor_wrap_render', 10);

	ob_start();
	call_user_func_array(array($element_class_, "_render"), array($element_data_, $content_));
	$rendered_html_ = ob_get_clean();
	$rendered_html_ = trim($rendered_html_);

	// 요소 스타일 CSS 생성 (margin, padding, background 등)
	$element_css_ = '';
	if(isset($element_data_['properties']) && function_exists('pb_page_builder_element_make_styles')){
		$element_css_ = pb_page_builder_element_make_styles($element_data_['properties']);
	}

	echo json_encode(array(
		'success' => true,
		'rendered_html' => $rendered_html_,
		'element_css' => $element_css_,
	));

	pb_end();
}
pb_add_ajax('live-editor-render-element', '_pb_live_editor_ajax_render_element');

?>