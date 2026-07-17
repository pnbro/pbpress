<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan002 part009 — /showcase/gallery
 * devplan004 part004 — showcase 재구성: 이 페이지는 pbpress 코어 API 데모가
 * 아니라 "UI 목업" 부속 화면으로 relabel(탭 라벨·h1만 변경, 내부 로직은
 * 그대로 유지). 코어 카탈로그는 /showcase(ajax-demo.php) 참조.
 *
 * 이미지 그리드 + 라이트박스 모달(part001 재사용) + 업로드 미리보기/데모.
 *
 * 이미지 소스 우선순위:
 *   1) pb_file_resource_list() 로 등록된 file_resources 조회
 *      (코어 file_resources 테이블: slug/file_title/file_desc/file_type/display_option
 *       — 실제 업로드 경로 컬럼은 없음. 파일 존재 여부와 무관한 메타데이터 리소스이므로
 *       조회에 성공해도 실제 이미지 바이너리 경로는 확정할 수 없다. 확장지점: DO에
 *       경로 컬럼 추가 후 pb_filebase_url()로 실사용 이미지 연결.)
 *   2) 위에서 표시 가능한 리소스가 없으면 인라인 SVG 플레이스홀더로 대체.
 * ============================================================ */

$showcase_file_resources_ = array();
if(function_exists('pb_file_resource_list')){
	$found_ = @pb_file_resource_list(array(
		'display_option' => class_exists('PB_FILE_RESOURCE_OPTION') ? PB_FILE_RESOURCE_OPTION::PUBLISHED : 'published',
		'limit' => 12,
	));
	if(is_array($found_)){
		$showcase_file_resources_ = $found_;
	}
}

$showcase_palette_ = array('primary', 'success', 'warning', 'info', 'danger');
$showcase_placeholder_count_ = 8;

pb_theme_header();
?>

<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/showcase.css">

<div class="container">

	<ul class="breadcrumb">
		<li><a href="<?=pb_home_url()?>"><?=__("홈")?></a></li>
		<li><a href="<?=pb_home_url("showcase")?>"><?=__("쇼케이스")?></a></li>
		<li><?=__("UI 목업", PB_THEME_DOMAIN)?></li>
	</ul>

	<h1><?=__("UI 목업 — 이미지 갤러리 & 업로드", PB_THEME_DOMAIN)?></h1>
	<p class="alert alert-info"><?=__("이 갤러리는 UI 목업입니다. pbpress 코어 API 데모는 상단 탭의 \"코어 API 카탈로그\"를 참고하세요.", PB_THEME_DOMAIN)?></p>
	<p class="text-muted"><?=__("file_resources 조회 결과 또는 인라인 SVG 플레이스홀더로 구성한 갤러리입니다. 타일 클릭 시 라이트박스가 열리고 키보드 좌우 방향키로 이동할 수 있습니다.")?></p>

	<nav class="tabs showcase-subnav" aria-label="<?=__("쇼케이스 내비게이션")?>">
		<a class="tab" href="<?=pb_home_url("showcase")?>"><?=__("코어 API 카탈로그", PB_THEME_DOMAIN)?></a>
		<a class="tab is-active" href="<?=pb_home_url("showcase/gallery")?>"><?=__("UI 목업(갤러리)", PB_THEME_DOMAIN)?></a>
	</nav>

	<!-- ================= 이미지 갤러리 ================= -->
	<section class="showcase-section" id="gallery">
		<div class="showcase-section__head">
			<h2><?=__("이미지 갤러리")?></h2>
			<p class="showcase-section__desc">
				<?php if(count($showcase_file_resources_) > 0): ?>
					<?=sprintf(__("file_resources 에 등록된 리소스 %d건을 표시합니다(메타데이터만, 실제 이미지는 플레이스홀더)."), count($showcase_file_resources_))?>
				<?php else: ?>
					<?=__("등록된 file_resources 가 없어 인라인 SVG 플레이스홀더로 대체 표시합니다.")?>
				<?php endif; ?>
			</p>
		</div>

		<div class="grid showcase-gallery-grid" id="showcase-gallery-grid">

			<?php foreach($showcase_file_resources_ as $resource_): ?>
				<button type="button" class="col-6 col-md-4 col-lg-3 showcase-gallery__item" data-gallery-caption="<?=htmlspecialchars(@$resource_['file_title'], ENT_QUOTES)?>">
					<span class="showcase-gallery__thumb">
						<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="<?=htmlspecialchars(@$resource_['file_title'], ENT_QUOTES)?>">
							<rect width="400" height="300" style="fill:var(--c-bg)"></rect>
							<rect x="30" y="30" width="340" height="240" rx="12" style="fill:var(--c-surface);stroke:var(--c-border);stroke-width:2"></rect>
							<circle cx="140" cy="130" r="34" style="fill:var(--c-primary-soft)"></circle>
							<path d="M60 240 L160 150 L220 210 L280 160 L340 240 Z" style="fill:var(--c-primary-soft)"></path>
							<text x="200" y="270" text-anchor="middle" style="fill:var(--c-text-muted);font-size:14px;font-family:sans-serif"><?=__("파일 리소스")?></text>
						</svg>
					</span>
					<span class="showcase-gallery__caption"><?=htmlspecialchars(@$resource_['file_title'], ENT_QUOTES)?></span>
				</button>
			<?php endforeach; ?>

			<?php if(count($showcase_file_resources_) <= 0): ?>
				<?php for($i_ = 0; $i_ < $showcase_placeholder_count_; $i_++):
					$color_ = $showcase_palette_[$i_ % count($showcase_palette_)];
					$caption_ = sprintf(__("샘플 이미지 #%d"), $i_ + 1);
				?>
					<button type="button" class="col-6 col-md-4 col-lg-3 showcase-gallery__item" data-gallery-caption="<?=htmlspecialchars($caption_, ENT_QUOTES)?>">
						<span class="showcase-gallery__thumb">
							<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="<?=htmlspecialchars($caption_, ENT_QUOTES)?>">
								<rect width="400" height="300" style="fill:var(--c-<?=$color_?>-soft)"></rect>
								<circle cx="<?=90 + ($i_ * 17) % 160?>" cy="<?=90 + ($i_ * 23) % 100?>" r="<?=40 + ($i_ * 7) % 30?>" style="fill:var(--c-<?=$color_?>)" opacity="0.55"></circle>
								<rect x="40" y="190" width="320" height="70" rx="10" style="fill:var(--c-surface)" opacity="0.85"></rect>
								<text x="200" y="232" text-anchor="middle" style="fill:var(--c-text);font-size:18px;font-weight:600;font-family:sans-serif">#<?=$i_ + 1?></text>
							</svg>
						</span>
						<span class="showcase-gallery__caption"><?=htmlspecialchars($caption_, ENT_QUOTES)?></span>
					</button>
				<?php endfor; ?>
			<?php endif; ?>

		</div>
	</section>

	<!-- ================= 업로드 데모 ================= -->
	<section class="showcase-section" id="upload-demo">
		<div class="showcase-section__head">
			<h2><?=__("업로드 데모")?></h2>
			<p class="showcase-section__desc"><?=__("이미지 선택 → 바닐라 FileReader 미리보기 → 코어 PB.file.upload(청크 아님)로 실제 업로드까지 시연합니다. 업로드 성공 시 위 갤러리에 즉시 반영됩니다(세션 내 표시일 뿐 file_resources 영구 등록은 아님).")?></p>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="showcase-upload__dropzone">
					<div class="field mb-0">
						<label class="label" for="showcase-upload-input"><?=__("이미지 파일 선택")?></label>
						<input type="file" id="showcase-upload-input" class="input" accept="image/*">
						<p class="field-hint"><?=__("이미지 파일만 허용됩니다. 업로드는 코어 fileupload 엔드포인트(확장자 필터·실행 차단 규약 적용)로 처리됩니다.")?></p>
					</div>

					<div class="showcase-upload__preview hidden" id="showcase-upload-preview">
						<img class="showcase-upload__preview-img" id="showcase-upload-preview-img" alt="<?=__("미리보기")?>">
						<div>
							<div class="fw-medium"><?=__("미리보기")?></div>
							<div class="text-sm text-muted"><?=__("업로드 전 클라이언트에서만 렌더링된 이미지입니다.")?></div>
						</div>
					</div>

					<div class="showcase-upload__progress">
						<div class="showcase-upload__progress-bar" id="showcase-upload-progress-bar"></div>
					</div>

					<div class="mt-4">
						<button type="button" class="btn btn-primary" id="showcase-upload-submit" disabled><?=__("업로드")?></button>
					</div>

					<div class="text-sm mt-3" id="showcase-upload-result"></div>
				</div>

				<!-- 확장지점: 실제 서비스에서는 업로드 성공 콜백에서 pb_file_resource_add() 형태의
				     전용 AJAX 핸들러를 추가로 호출해 파일 메타데이터를 영구 저장할 수 있다.
				     단, 현재 core file_resources DO에는 실제 파일 경로 컬럼이 없어
				     경로 영속화를 위해서는 스키마 확장이 선행되어야 한다(본 파트 범위 밖). -->
			</div>
		</div>
	</section>

</div>

<!-- 라이트박스 모달 -->
<div class="modal showcase-lightbox" id="showcase-lightbox" aria-hidden="true">
	<div class="modal-backdrop"></div>
	<div class="modal-dialog modal-dialog-lg">
		<div class="modal-header">
			<h3 class="modal-title" id="showcase-lightbox-title"><?=__("이미지 미리보기")?></h3>
			<button type="button" class="modal-close" data-modal-close aria-label="<?=__("닫기")?>">&times;</button>
		</div>
		<div class="modal-body">
			<div class="showcase-lightbox__stage">
				<button type="button" class="showcase-lightbox__nav showcase-lightbox__nav--prev" id="showcase-lightbox-prev" aria-label="<?=__("이전 이미지")?>">&#8592;</button>
				<div id="showcase-lightbox-media"></div>
				<button type="button" class="showcase-lightbox__nav showcase-lightbox__nav--next" id="showcase-lightbox-next" aria-label="<?=__("다음 이미지")?>">&#8594;</button>
			</div>
			<p class="showcase-lightbox__caption" id="showcase-lightbox-caption"></p>
		</div>
	</div>
</div>

<script src="<?=pb_current_theme_url()?>lib/js/features/showcase.js"></script>

<?php pb_theme_footer(); ?>
