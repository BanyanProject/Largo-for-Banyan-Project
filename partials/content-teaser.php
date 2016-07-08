
<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>

	<?php

		$entry_classes = 'entry-content';
		echo '<div class="' . $entry_classes . '">';

		echo '<a href="' . get_permalink() . '">' . get_the_post_thumbnail() . '</a>';

	?>

	 	<h2 class="entry-title">
	 		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to', 'largo' ) . ' ' ) )?>" rel="bookmark"><?php the_title(); ?></a>
	 	</h2>

		<?php largo_excerpt( $post, 3); ?>

		<?php get_template_part( 'partials/social', 'horizontal-small' ); ?>

		</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<?php 
?>
