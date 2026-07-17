<?php

if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/* ============================================================
 * devplan004 part002 — 백엔드 데모 기반 구축
 * ------------------------------------------------------------
 * pbpress 코어의 "선언형 DO 테이블 + 쿼리빌더 + AJAX" 조합으로
 * 실제 DB를 왕복하는 방명록(sample_guestbook) 데모를 제공한다.
 *
 * 테이블 생성 트리거: 아래 pbdb_data_object() 호출은 "선언"만 한다.
 * 실제 CREATE TABLE은 코어 PBDB_DO 생성자가 'pb_install_tables'
 * 필터에 스스로 등록해 두고, 관리자 화면에서 테이블 재설치를
 * 실행하면(admin/_ajax_reinstall_tables.php → $pbdb->install_tables()
 * → pb_hook_apply_filters('pb_install_tables', ...) → CREATE TABLE IF
 * NOT EXISTS) 그 시점에 물리 테이블이 생성된다. 이 파일에서 별도의
 * 설치 코드를 작성할 필요가 없다.
 * ============================================================ */

global $sample_guestbook_do;
$sample_guestbook_do = pbdb_data_object("sample_guestbook", array(
	"id"       => array("type" => PBDB_DO::TYPE_INT,      "nn" => true, "pk" => true, "ai" => true, "comment" => "PK"),
	"writer"   => array("type" => PBDB_DO::TYPE_VARCHAR,   "length" => 60,  "nn" => true, "comment" => "작성자 표시명"),
	"user_id"  => array("type" => PBDB_DO::TYPE_INT,       "comment" => "로그인 회원 연동(비회원 NULL)"),
	"content"  => array("type" => PBDB_DO::TYPE_VARCHAR,   "length" => 500, "nn" => true, "comment" => "내용"),
	"reg_date" => array("type" => PBDB_DO::TYPE_DATETIME,  "comment" => "작성일시"),
), "샘플테마 방명록 데모");

function sample_guestbook_do(){
	global $sample_guestbook_do;
	return $sample_guestbook_do;
}

/* ------------------------------------------------------------
 * 지연 시딩 — 목록 조회 시 테이블은 존재하지만 0건이면 데모용 3건을
 * 자동 삽입한다. 요청당 1회만 시도(static 가드). 테이블이 아직
 * 만들어지지 않았거나 조회 중 예외가 발생해도 조용히 무시한다.
 * ------------------------------------------------------------ */
function sample_guestbook_ensure_seed(){
	static $checked_ = false;
	if($checked_) return;
	$checked_ = true;

	$do_ = sample_guestbook_do();

	try{
		if(!$do_->is_exists()) return;

		$count_ = $do_->statement()->count();
		if($count_ > 0) return;

		$seed_items_ = array(
			array(
				'writer' => __('방문자A', PB_THEME_DOMAIN),
				'content' => __('pbpress 방명록 데모입니다. 이 글은 실제 sample_guestbook 테이블에 저장되어 있습니다.', PB_THEME_DOMAIN),
			),
			array(
				'writer' => __('방문자B', PB_THEME_DOMAIN),
				'content' => __('DO 선언 한 번으로 테이블이 만들어지고, pbdb_ss 쿼리빌더로 목록을 조회합니다.', PB_THEME_DOMAIN),
			),
			array(
				'writer' => __('방문자C', PB_THEME_DOMAIN),
				'content' => __('등록·삭제는 CSRF 토큰 검증을 거쳐 실제 DB에 반영됩니다. 직접 남겨보세요.', PB_THEME_DOMAIN),
			),
		);

		foreach($seed_items_ as $item_){
			$do_->insert(array(
				'writer' => $item_['writer'],
				'content' => $item_['content'],
				'reg_date' => pb_current_time(),
			));
		}
	}catch(Exception $ex_){
		// 테이블 미설치 등 예외 상황은 데모 흐름을 막지 않도록 조용히 무시한다.
		return;
	}
}

/* ------------------------------------------------------------
 * AJAX: sample-guestbook-list — 검색/페이징 목록 조회
 * ------------------------------------------------------------ */
pb_add_ajax('sample-guestbook-list', '_sample_guestbook_ajax_list');
function _sample_guestbook_ajax_list(){
	sample_guestbook_ensure_seed();

	$keyword_ = trim((string)_REQUEST('keyword', ''));
	$offset_ = (int)_REQUEST('offset', 0, PB_PARAM_INT);
	$offset_ = max(0, $offset_);
	$limit_ = (int)_REQUEST('limit', 10, PB_PARAM_INT);
	$limit_ = max(1, min(50, $limit_));

	$statement_ = sample_guestbook_do()->statement();
	if(strlen($keyword_)){
		$statement_->add_like_condition(array('content', 'writer'), $keyword_);
	}

	$total_ = $statement_->count();

	$list_statement_ = sample_guestbook_do()->statement();
	if(strlen($keyword_)){
		$list_statement_->add_like_condition(array('content', 'writer'), $keyword_);
	}
	$rows_ = $list_statement_->select("id DESC", array($offset_, $limit_));

	$current_user_id_ = pb_current_user_id();
	$is_logged_in_ = ($current_user_id_ != -1);
	$is_admin_ = $is_logged_in_ && pb_user_has_authority_task($current_user_id_, 'manage_site');

	$items_ = array();
	foreach($rows_ as $row_){
		$can_delete_ = strlen($row_['user_id'])
			? ($is_logged_in_ && ((string)$current_user_id_ === (string)$row_['user_id']))
			: $is_admin_;

		$items_[] = array(
			'id' => (int)$row_['id'],
			'writer' => $row_['writer'],
			'content' => $row_['content'],
			'reg_date' => $row_['reg_date'],
			'can_delete' => $can_delete_,
		);
	}

	return pb_ajax_success(array(
		'items' => $items_,
		'total' => (int)$total_,
		'offset' => $offset_,
		'limit' => $limit_,
	));
}

/* ------------------------------------------------------------
 * AJAX: sample-guestbook-add — 방명록 등록(CSRF 검증 + insert)
 * ------------------------------------------------------------ */
pb_add_ajax('sample-guestbook-add', '_sample_guestbook_ajax_add');
function _sample_guestbook_ajax_add(){
	if(!pb_verify_request_token('sample-guestbook', _POST('_request_chip'))){
		return pb_ajax_error(__('잘못된 요청', PB_THEME_DOMAIN), __('요청이 만료되었거나 위조되었습니다. 새로고침 후 다시 시도해주세요.', PB_THEME_DOMAIN));
	}

	$writer_ = trim((string)_POST('writer'));
	$content_ = trim((string)_POST('content'));

	$current_user_id_ = pb_current_user_id();
	$is_logged_in_ = ($current_user_id_ != -1);

	if($is_logged_in_){
		$current_user_ = pb_current_user();
		if(isset($current_user_['user_name']) && strlen($current_user_['user_name'])){
			$writer_ = $current_user_['user_name'];
		}
	}

	if(!strlen($writer_)){
		return pb_ajax_error(__('입력값 오류', PB_THEME_DOMAIN), __('작성자명을 입력해주세요.', PB_THEME_DOMAIN));
	}
	if(!strlen($content_)){
		return pb_ajax_error(__('입력값 오류', PB_THEME_DOMAIN), __('내용을 입력해주세요.', PB_THEME_DOMAIN));
	}
	if(mb_strlen($content_) > 500){
		return pb_ajax_error(__('입력값 오류', PB_THEME_DOMAIN), __('내용은 500자를 넘을 수 없습니다.', PB_THEME_DOMAIN));
	}
	if(mb_strlen($writer_) > 60){
		$writer_ = mb_substr($writer_, 0, 60);
	}

	$insert_data_ = array(
		'writer' => $writer_,
		'content' => $content_,
		'reg_date' => pb_current_time(),
	);
	if($is_logged_in_){
		$insert_data_['user_id'] = $current_user_id_;
	}

	$inserted_id_ = sample_guestbook_do()->insert($insert_data_);

	return pb_ajax_success(array('id' => $inserted_id_));
}

/* ------------------------------------------------------------
 * AJAX: sample-guestbook-delete — 삭제(CSRF + 소유자/관리자 권한 검증)
 *  - 로그인 회원 글(user_id 있음) : 작성 본인만 삭제
 *  - 비회원 글(user_id 없음)     : manage_site 권한을 가진 관리자만 삭제
 * ------------------------------------------------------------ */
pb_add_ajax('sample-guestbook-delete', '_sample_guestbook_ajax_delete');
function _sample_guestbook_ajax_delete(){
	if(!pb_verify_request_token('sample-guestbook', _POST('_request_chip'))){
		return pb_ajax_error(__('잘못된 요청', PB_THEME_DOMAIN), __('요청이 만료되었거나 위조되었습니다. 새로고침 후 다시 시도해주세요.', PB_THEME_DOMAIN));
	}

	$id_ = (int)_POST('id', 0, PB_PARAM_INT);
	if($id_ <= 0){
		return pb_ajax_error(__('잘못된 요청', PB_THEME_DOMAIN), __('삭제할 방명록을 찾을 수 없습니다.', PB_THEME_DOMAIN));
	}

	$row_statement_ = sample_guestbook_do()->statement();
	$row_statement_->add_compare_condition('id', $id_, "=", PBDB::TYPE_NUMBER);
	$row_ = $row_statement_->get_first_row();

	if(!isset($row_)){
		return pb_ajax_error(__('잘못된 요청', PB_THEME_DOMAIN), __('삭제할 방명록을 찾을 수 없습니다.', PB_THEME_DOMAIN));
	}

	$current_user_id_ = pb_current_user_id();
	$is_logged_in_ = ($current_user_id_ != -1);

	$can_delete_ = strlen($row_['user_id'])
		? ($is_logged_in_ && ((string)$current_user_id_ === (string)$row_['user_id']))
		: ($is_logged_in_ && pb_user_has_authority_task($current_user_id_, 'manage_site'));

	if(!$can_delete_){
		return pb_ajax_error(__('권한 없음', PB_THEME_DOMAIN), __('본인이 작성한 글이거나 관리자만 삭제할 수 있습니다.', PB_THEME_DOMAIN));
	}

	sample_guestbook_do()->delete($id_);

	return pb_ajax_success();
}

/* ============================================================
 * 실지표 함수 — sample_theme_metrics()
 * ------------------------------------------------------------
 * 하드코딩 금지: pb_current_theme_path() 하위 *.php 를 재귀 스캔해
 * 실제 코드에 등장하는 코어 API 호출 횟수를 센다. 요청당 1회만
 * 스캔하고 static 캐시로 재사용한다(part003 홈 배너가 이 함수를 호출).
 *
 * 정규식은 "함수명 뒤 공백 없는 '(' 리터럴"이 아니라 \s*\( 형태를 써서
 * 이 파일 자신이 담고 있는 정규식 문자열(패턴 소스 자체)이 스캔 대상이
 * 되어도 스스로를 오카운트하지 않도록 했다(패턴 문자열에는 "\s*\("가
 * 들어있어 실제 호출 리터럴 "pb_add_ajax("과 문자열이 일치하지 않음).
 *
 * 카운트 근거(참고용 — 아래는 사람이 눈으로 검증할 때 쓰는 grep 예시이며
 * 실제 런타임 카운트는 위에서 설명한 preg_match_all 스캔으로 수행한다):
 *   grep -rE "\bpb_hook_add_action\s*\(|\bpb_hook_add_filter\s*\(" themes/sample --include=*.php | wc -l
 *   grep -rE "\bpb_rewrite_register\s*\(" themes/sample --include=*.php | wc -l
 *   grep -rE "\bpb_add_ajax\s*\(" themes/sample --include=*.php | wc -l
 *   grep -rE "\bpbdb_data_object\s*\(" themes/sample --include=*.php | wc -l
 * ============================================================ */
function sample_theme_metrics(){
	static $cached_ = null;
	if(isset($cached_)) return $cached_;

	$theme_path_ = pb_current_theme_path();

	$counts_ = array(
		'hooks' => 0,
		'routes' => 0,
		'ajax' => 0,
		'do_tables' => 0,
	);

	/* 함수명 → 지표 매핑. PHP 토큰 파서(token_get_all)로 "실제 함수호출 지점"만
	 * 센다. 주석·문자열 안의 동일 문구는 토큰 종류가 T_STRING이 아니므로
	 * 자동으로 제외되어(자기참조 grep 예시·설명 문구 등) 숫자가 부풀지 않는다.
	 * 호출로 인정하는 조건: T_STRING(함수명) 뒤 첫 비공백 토큰이 '(' 인 경우. */
	$fn_map_ = array(
		'pb_hook_add_action' => 'hooks',
		'pb_hook_add_filter' => 'hooks',
		'pb_rewrite_register' => 'routes',
		'pb_add_ajax' => 'ajax',
		'pbdb_data_object' => 'do_tables',
	);

	try{
		$files_ = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($theme_path_, FilesystemIterator::SKIP_DOTS)
		);

		foreach($files_ as $file_){
			if($file_->isDir()) continue;
			if(strtolower($file_->getExtension()) !== 'php') continue;

			$content_ = @file_get_contents($file_->getPathname());
			if($content_ === false) continue;

			$tokens_ = @token_get_all($content_);
			if(!is_array($tokens_)) continue;

			$token_count_ = count($tokens_);
			for($i_ = 0; $i_ < $token_count_; $i_++){
				$tok_ = $tokens_[$i_];
				if(!is_array($tok_) || $tok_[0] !== T_STRING) continue;
				if(!isset($fn_map_[$tok_[1]])) continue;

				// 함수명 뒤 첫 비공백 토큰이 '(' 인지 확인(실제 호출만 인정)
				$j_ = $i_ + 1;
				while($j_ < $token_count_ && is_array($tokens_[$j_]) && $tokens_[$j_][0] === T_WHITESPACE){
					$j_++;
				}
				if($j_ < $token_count_ && $tokens_[$j_] === '('){
					$counts_[$fn_map_[$tok_[1]]]++;
				}
			}
		}
	}catch(Exception $ex_){
		// 스캔 실패 시에도 0으로 채워진 안전한 배열을 반환한다.
	}

	$cached_ = $counts_;
	return $cached_;
}

?>
