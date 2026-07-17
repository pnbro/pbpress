
	</main>

	<?php
		/* devplan002 part008 — 푸터 카피라이트 + SNS 링크(값 있는 것만) */
		$sample_theme_footer_copyright_ = pb_option_value('sample_theme_footer_copyright');
		if(!strlen($sample_theme_footer_copyright_)){
			$sample_theme_footer_copyright_ = '&copy; '.date('Y').' PBPress Sample Theme. All rights reserved.';
		}

		$sample_theme_sns_links_ = array(
			'facebook' => array(
				'url' => pb_option_value('sample_theme_sns_facebook'),
				'label' => 'Facebook',
				'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.79 8.44-4.94 8.44-9.94Z"/></svg>',
			),
			'instagram' => array(
				'url' => pb_option_value('sample_theme_sns_instagram'),
				'label' => 'Instagram',
				'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>',
			),
			'youtube' => array(
				'url' => pb_option_value('sample_theme_sns_youtube'),
				'label' => 'YouTube',
				'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3Z" fill="currentColor" stroke="none"/></svg>',
			),
		);
	?>

	<footer class="pb-footer">
		<div class="container text-center">
			<p class="pb-footer__copyright"><?=$sample_theme_footer_copyright_?></p>
			<?php
				$sample_theme_has_sns_ = false;
				foreach($sample_theme_sns_links_ as $sample_theme_sns_item_){
					if(strlen($sample_theme_sns_item_['url'])){ $sample_theme_has_sns_ = true; break; }
				}
			?>
			<?php if($sample_theme_has_sns_){ ?>
				<div class="pb-footer__sns flex justify-center gap-3">
					<?php foreach($sample_theme_sns_links_ as $sample_theme_sns_item_){ ?>
						<?php if(strlen($sample_theme_sns_item_['url'])){ ?>
							<a href="<?=htmlspecialchars($sample_theme_sns_item_['url'])?>" target="_blank" rel="noopener noreferrer" aria-label="<?=htmlspecialchars($sample_theme_sns_item_['label'])?>" class="pb-footer__sns-link"><?=$sample_theme_sns_item_['icon']?></a>
						<?php } ?>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</footer>

	<!-- Sample Theme UI 유틸 (바닐라 JS, devplan002 part001) — bootstrap.bundle.min.js 참조 제거됨 -->
	<script src="<?=pb_current_theme_url()?>lib/js/ui.js"></script>
	<?php pb_foot() ?>
</body>

</html>
