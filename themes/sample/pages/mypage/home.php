<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 마이페이지 홈(대시보드)
 * 일반 페이지 방식(이 테마엔 header-mypage.php/footer-mypage.php가 없음):
 * pb_theme_header() 직접 호출 + 자체 container/grid 셸(part001 base.css .grid/.col-*). */

$pb_mypage_user_ = pb_current_user();

// 최근 활동 — 코어에 활동로그 모듈이 없어 데모용 더미 데이터로 시연한다(확장지점).
$pb_mypage_recent_activity_ = array(
	array('title' => __('마이페이지에 로그인했습니다.', PB_THEME_DOMAIN), 'date' => date('Y.m.d H:i')),
	array('title' => __('회원정보 수정 화면을 확인했습니다.', PB_THEME_DOMAIN), 'date' => date('Y.m.d', strtotime('-1 day'))),
	array('title' => __('찜 목록 기능을 살펴봤습니다.', PB_THEME_DOMAIN), 'date' => date('Y.m.d', strtotime('-3 day'))),
);

pb_theme_header();
?>
<div class="container mypage-shell">
	<div class="grid">
		<div class="col-12 col-md-3">
			<?php include PB_THEME_PATH.'pages/mypage/aside.php'; ?>
		</div>
		<div class="col-12 col-md-9">
			<div class="card mb-6">
				<div class="card-body">
					<div class="user-card">
						<span class="avatar avatar-lg" aria-hidden="true"><?=htmlspecialchars(pb_mypage_avatar_letter(@$pb_mypage_user_['user_name']))?></span>
						<div>
							<div class="user-card-name text-lg"><?=sprintf(__('%s님, 안녕하세요.', PB_THEME_DOMAIN), htmlspecialchars((string)@$pb_mypage_user_['user_name']))?></div>
							<div class="user-card-meta"><?=htmlspecialchars((string)@$pb_mypage_user_['user_email'])?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header"><?=__('최근 활동', PB_THEME_DOMAIN)?></div>
				<div class="card-body">
					<?php if(count($pb_mypage_recent_activity_) <= 0): ?>
					<p class="text-muted text-center mypage-empty"><?=__('최근 활동이 없습니다.', PB_THEME_DOMAIN)?></p>
					<?php else: ?>
					<ul class="mypage-activity-list">
						<?php foreach($pb_mypage_recent_activity_ as $activity_): ?>
						<li class="mypage-activity-item">
							<span class="mypage-activity-item__title"><?=htmlspecialchars($activity_['title'])?></span>
							<span class="mypage-activity-item__date text-muted text-sm"><?=htmlspecialchars($activity_['date'])?></span>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php pb_theme_footer(); ?>
