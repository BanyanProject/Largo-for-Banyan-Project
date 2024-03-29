<?php
/**
 * Single Post Template: One Column (Standard Layout)
 * Template Name: One Column (Standard Layout)
 * Description: Shows the post but does not load any sidebars.
 */

global $shown_ids;

add_filter( 'body_class', function( $classes ) {
	$classes[] = 'normal';
	$classes[] = 'banyan-1col';
	return $classes;
} );

get_header();
?>

<div id="content" class="col-md-10 col-md-offset-1"  role="main">
		
	<?php
		while ( have_posts() ) : the_post();
			
			$shown_ids[] = get_the_ID();
						
			get_template_part( 'partials/content', 'single' );

			if ( is_active_sidebar( 'article-bottom' ) ) {

				do_action( 'largo_before_post_bottom_widget_area' );
				echo '<div class="article-bottom nocontent">';
				dynamic_sidebar( 'article-bottom' );
				echo '</div>';
				do_action( 'largo_after_post_bottom_widget_area' );

			}

		endwhile;
	?>
</div>

<?php do_action( 'largo_after_content' ); ?>

<?php get_footer();
