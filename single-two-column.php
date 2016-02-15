<?php
/**
 * Single Post Template: Two Column (Classic Layout)
 * Template Name: Two Column (Classic Layout)
 * Description: Shows the post and sidebar if specified.
 */

global $shown_ids;

add_filter('body_class', function($classes) {
	$classes[] = 'classic';
	$classes[] = 'banyan-2col';
	return $classes;
});

get_header();
?>

<div id="content" class="col-main" role="main">

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

			do_action('largo_before_comments');
			
			//comments_template( '', true );

			do_action('largo_after_comments');

		endwhile;
	?>
</div>

<?php do_action('largo_after_content'); ?>

<?php 

if (is_page())
	get_sidebar('home');
else 
	get_sidebar(); 

?>

<div class="clearfix"></div>

<?php get_footer();
