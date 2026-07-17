<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 찜/북마크 목록
 * 코어에 찜/북마크 데이터소스가 없어(search_api 확인 결과 0건) 빈 배열 → 빈상태 시연.
 * 확장 시: page-handlers/mypage/bookmarks.php에서 실제 조회 후 이 배열에 채워 넣으면 됨. */

$pb_mypage_bookmarks_ = array();

pb_theme_header();
?>
<div class="container mypage-shell">
	<div class="grid">
		<div class="col-12 col-md-3">
			<?php include PB_THEME_PATH.'pages/mypage/aside.php'; ?>
		</div>
		<div class="col-12 col-md-9">
			<div class="card">
				<div class="card-header">찜 목록</div>
				<div class="card-body">
					<?php if(count($pb_mypage_bookmarks_) <= 0): ?>
					<div class="mypage-empty text-center">
						<p class="text-muted">아직 찜한 글이 없습니다.</p>
						<a class="btn btn-ghost btn-sm" href="<?=pb_home_url()?>">둘러보러 가기</a>
					</div>
					<?php else: ?>
					<div class="grid mypage-bookmark-grid">
						<?php foreach($pb_mypage_bookmarks_ as $bookmark_): ?>
						<div class="col-12 col-sm-6 col-lg-4">
							<div class="card mypage-bookmark-card">
								<div class="card-body">
									<div class="fw-medium"><?=htmlspecialchars((string)@$bookmark_['title'])?></div>
									<div class="text-muted text-sm mt-2"><?=htmlspecialchars((string)@$bookmark_['date'])?></div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php pb_theme_footer(); ?>
