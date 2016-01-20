<?php
/**
 * Template Name: Full Width Page
 * Single Post Template: Full-width (no sidebar)
 * Description: Shows the post but does not load any sidebars, allowing content to span full container width.
 *
 * @package Largo
 * @since 0.1
 */
get_header();
?>

<div id="content" class="col-md-12" role="main">
	<?php
		while ( have_posts() ) : the_post();
			
			$shown_ids[] = get_the_ID();
			
			$partial = ( is_page() ) ? 'page' : 'single-classic';
			
			get_template_part( 'partials/content', $partial );

			if ( $partial === 'single-classic' ) {
				if ( is_active_sidebar( 'article-bottom' ) ) {

					do_action( 'largo_before_post_bottom_widget_area' );

					echo '<div class="article-bottom nocontent">';
					dynamic_sidebar( 'article-bottom' );
					echo '</div>';

					do_action( 'largo_after_post_bottom_widget_area' );

				}

				do_action(' largo_before_comments' );

				//comments_template( '', true );

				do_action( 'largo_after_comments' );
			}

		endwhile;
	?>
</div><!--#content-->

<?php get_footer();
