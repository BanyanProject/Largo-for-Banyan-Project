<?php

$tags = of_get_option( 'tag_display' );
$hero_class = largo_hero_class( $post->ID, FALSE );
$values = get_post_custom( $post->ID );

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>

	<?php

		$entry_classes = 'entry-content';
		echo '<div class="' . $entry_classes . '">';

		echo '<a href="' . get_permalink() . '">' . get_the_post_thumbnail() . '</a>';

		if ( largo_has_categories_or_tags() && $tags === 'top' ) {
		 	echo '<h5 class="top-tag">' . largo_top_term( $args = array( 'echo' => FALSE ) ) . '</h5>';
		}

	?>

	 	<h2 class="entry-title">
	 		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to', 'largo' ) . ' ' ) )?>" rel="bookmark"><?php the_title(); ?></a>
	 	</h2>

		<?php largo_excerpt( $post, 5, true, __('Continue&nbsp;Reading', 'largo'), true, false ); ?>

		<?php if ( !is_home() && largo_has_categories_or_tags() && $tags === 'btm' ) { ?>
	    	<h5 class="tag-list"><strong><?php _e('More about:', 'largo'); ?></strong> <?php largo_categories_and_tags( 8 ); ?></h5>
	    <?php } ?>

		<?php get_template_part( 'partials/social', 'horizontal-small' ); ?>

		</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<?php 
?>
