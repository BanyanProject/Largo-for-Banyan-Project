<?php
/**
 * The homepage template
 *
 * @package Largo
 * @since 0.1
 */

/**
 * ======== DO NOT EDIT OR CLONE THIS FILE FOR A CHILD THEME =======
 *
 * Largo comes with a built-in homepage template system, documented in homepages/README.md
 * It's generally better to use that system than to have your child theme use its own home.php template
 */

get_header();

/*
 * Collect post IDs in each loop so we can avoid duplicating posts
 * and get the theme option to determine if this is a two column or three column layout
 */
$shown_ids = array();
$home_template = largo_get_active_homepage_layout();
$layout_class = of_get_option('home_template');
$tags = of_get_option ('tag_display');

global $largo;
if ($home_template == 'LegacyThreeColumn')
	$span_class = 'col-md-8';
else
	$span_class = ( $largo['home_rail'] ) ? 'col-md-8' : 'col-md-12' ;
?>

<div id="content" class="stories <?php echo $span_class; ?> <?php echo sanitize_html_class(basename($home_template)); ?>" role="main">
<?php
	largo_render_homepage_layout($home_template);
?>
</div><!-- #content-->
<?php get_footer();
