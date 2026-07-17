<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan003 part002 — 홈 카드/기능/통계 파셜
 * 코어에 없는 가짜 컴포넌트 등록/드로우 메커니즘을 제거하고
 * 평범한 테마 PHP 파셜 함수로 재작성했다. 마크업은 기존 컴포넌트 정의와
 * 100% 동일(회귀 방지), 렌더 메커니즘만 일반 함수 호출로 교체.
 *
 * 함수 목록:
 *  - sample_card($data_, $parameter_=array())        : 아이콘+제목+설명(+선택적 링크) 카드
 *  - sample_card_col($data_, $parameter_=array())     : 위 카드를 그리드 컬럼(.col-*)으로 감싸는 변형
 *  - sample_feature($data_, $parameter_=array())      : 기능 소개용 카드(아이콘 배지 + 제목 + 설명)
 *  - sample_feature_col($data_, $parameter_=array())  : 기능 카드 그리드 컬럼 래퍼
 *  - sample_stat($data_, $parameter_=array())         : 통계 숫자 + 라벨
 *  - sample_stat_col($data_, $parameter_=array())     : 통계 그리드 컬럼 래퍼
 * ============================================================ */

/* ---------- sample_card : 아이콘+제목+설명(+링크) 카드 ---------- */
if(!function_exists('sample_card')){
	function sample_card($data_, $parameter_ = array()){
		$icon_ = isset($data_['icon']) ? $data_['icon'] : '';
		$title_ = isset($data_['title']) ? $data_['title'] : '';
		$desc_ = isset($data_['desc']) ? $data_['desc'] : '';
		$href_ = isset($data_['href']) && strlen($data_['href']) ? $data_['href'] : '';
		$class_ = isset($parameter_['class']) && strlen($parameter_['class']) ? ' '.$parameter_['class'] : '';
		$id_ = isset($parameter_['id']) && strlen($parameter_['id']) ? $parameter_['id'] : "sample-card-".pb_random_string(5);
		$tag_ = strlen($href_) ? 'a' : 'div';
		?>
<<?=$tag_?> class="card sample-card<?=$class_?>" id="<?=$id_?>"<?php if(strlen($href_)):?> href="<?=$href_?>"<?php endif;?> data-reveal>
	<div class="card-body">
		<?php if(strlen($icon_)):?><span class="sample-card__icon" aria-hidden="true"><?=$icon_?></span><?php endif;?>
		<?php if(strlen($title_)):?><h3 class="sample-card__title"><?=$title_?></h3><?php endif;?>
		<?php if(strlen($desc_)):?><p class="sample-card__desc text-muted text-sm"><?=$desc_?></p><?php endif;?>
	</div>
</<?=$tag_?>>
		<?php
	}
}

/* ---------- sample_card_col : 그리드 컬럼 래퍼 ---------- */
if(!function_exists('sample_card_col')){
	function sample_card_col($data_, $parameter_ = array()){
		$col_class_ = isset($parameter_['col_class']) && strlen($parameter_['col_class']) ? $parameter_['col_class'] : 'col-12 col-sm-6 col-lg-4';
		?>
<div class="<?=$col_class_?>">
	<?php sample_card($data_, $parameter_); ?>
</div>
		<?php
	}
}

/* ---------- sample_feature : 기능 소개 카드(아이콘 배지) ---------- */
if(!function_exists('sample_feature')){
	function sample_feature($data_, $parameter_ = array()){
		$icon_ = isset($data_['icon']) ? $data_['icon'] : '';
		$title_ = isset($data_['title']) ? $data_['title'] : '';
		$desc_ = isset($data_['desc']) ? $data_['desc'] : '';
		$class_ = isset($parameter_['class']) && strlen($parameter_['class']) ? ' '.$parameter_['class'] : '';
		$id_ = isset($parameter_['id']) && strlen($parameter_['id']) ? $parameter_['id'] : "sample-feature-".pb_random_string(5);
		?>
<div class="card sample-feature<?=$class_?>" id="<?=$id_?>" data-reveal>
	<div class="card-body">
		<span class="sample-feature__icon" aria-hidden="true"><?=$icon_?></span>
		<h3 class="sample-feature__title"><?=$title_?></h3>
		<p class="sample-feature__desc text-muted text-sm"><?=$desc_?></p>
	</div>
</div>
		<?php
	}
}

/* ---------- sample_feature_col : 기능 카드 그리드 컬럼 래퍼 ---------- */
if(!function_exists('sample_feature_col')){
	function sample_feature_col($data_, $parameter_ = array()){
		$col_class_ = isset($parameter_['col_class']) && strlen($parameter_['col_class']) ? $parameter_['col_class'] : 'col-12 col-sm-6 col-lg-3';
		?>
<div class="<?=$col_class_?>">
	<?php sample_feature($data_, $parameter_); ?>
</div>
		<?php
	}
}

/* ---------- sample_stat : 통계 숫자 + 라벨 ---------- */
if(!function_exists('sample_stat')){
	function sample_stat($data_, $parameter_ = array()){
		$value_ = isset($data_['value']) ? $data_['value'] : '';
		$label_ = isset($data_['label']) ? $data_['label'] : '';
		$class_ = isset($parameter_['class']) && strlen($parameter_['class']) ? ' '.$parameter_['class'] : '';
		?>
<div class="sample-stat<?=$class_?>" data-reveal>
	<div class="sample-stat__value"><?=$value_?></div>
	<div class="sample-stat__label text-muted text-sm"><?=$label_?></div>
</div>
		<?php
	}
}

/* ---------- sample_stat_col : 통계 그리드 컬럼 래퍼 ---------- */
if(!function_exists('sample_stat_col')){
	function sample_stat_col($data_, $parameter_ = array()){
		$col_class_ = isset($parameter_['col_class']) && strlen($parameter_['col_class']) ? $parameter_['col_class'] : 'col-6 col-md-3';
		?>
<div class="<?=$col_class_?>">
	<?php sample_stat($data_, $parameter_); ?>
</div>
		<?php
	}
}

?>
