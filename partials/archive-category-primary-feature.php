<article id="post-<?php echo $featured_post->ID ?>" <?php post_class('clearfix row-fluid'); ?>>

	<header>
		
		<?php if ( has_post_thumbnail($featured_post->ID) ) : ?>
		<a class="head-image" href="<?php echo post_permalink($featured_post->ID); ?>"><?php echo get_the_post_thumbnail($featured_post->ID, 'large'); ?></a>
		<?php endif; ?>
		
		<h2 class="entry-title">
			<a href="<?php echo post_permalink($featured_post->ID); ?>"
				title="<?php echo __( 'Permalink to', 'largo' ) . esc_attr(strip_tags($featured_post->post_title)); ?>"
				rel="bookmark"><?php echo $featured_post->post_title; ?></a>
		</h2>

	</header>

	<div class="entry-content">
		<?php largo_excerpt($featured_post, 5, true, '', true, false); ?>
	</div>
	
</article>
