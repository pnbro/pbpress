<?php 	
	global $pbpost;
	pb_theme_header();
?>

<h1 class="post-title"><?=pb_post_title()?></h1>
<div class="post-html"><?=pb_post_html()?></div>

<?php if(pb_exists_prev_post()){ ?>
<a href="<?=pb_prev_post_url()?>" class="prev-post-link"><?=pb_prev_post_title()?></a>
<?php } ?>

<?php if(pb_exists_next_post()){ ?>
<a href="<?=pb_next_post_url()?>" class="next-post-link"><?=pb_next_post_title()?></a>
<?php } ?>

<?php
	pb_theme_footer();
?>