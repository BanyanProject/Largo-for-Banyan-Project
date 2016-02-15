<?php
/**
 * Template for category archive pages
 */
 
wp_enqueue_script(
	'category',
	'/wp-content/themes/Largo-BP/js/category.php',
	array('jquery'),
	'0.1',
	true
);
   
get_header();

global $tags, $paged, $post, $shown_ids;

$title = single_cat_title('', false);
$description = category_description();
$rss_link =  get_category_feed_link(get_queried_object_id());
$posts_term = of_get_option('posts_term_plural', 'Stories');

$featured_posts = largo_get_featured_posts( array(
	'showposts' => 1,
	'tax_query' => array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => $wp_query->query_vars['category_name'] ,
		),
		array(
			'taxonomy' => 'prominence',
			'field' => 'slug',
			'terms' => 'category-featured',
		),
		array(
			'taxonomy' => 'post-type',
			'field' => 'slug',
			'terms' => array('news','blog')
		)
	)
));

$all_posts = largo_get_featured_posts( array(
	'showposts' => 9,
	'tax_query' => array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => $wp_query->query_vars['category_name'] ,
		),
		array(
			'taxonomy' => 'post-type',
			'field' => 'slug',
			'terms' => array('news','blog')
		)
	)
));

?>

<div class="col-md-12 clearfix">
	<header class="archive-background clearfix">
		<a class="rss-link rss-subscribe-link" href="<?php echo $rss_link; ?>"><?php echo __( 'Subscribe', 'largo' ); ?> </a>
		<h1 class="page-title"><?php echo $title; ?></h1>
		<div class="archive-description"><?php echo $description; ?></div>
		<?php get_template_part('partials/archive', 'category-related'); ?>
	</header>


	<div class="row row-fluid clearfix">
		<div class="stories col-main" role="main" id="content">
						
			<?php
		
				if ($featured_posts->have_posts()) {
					
					while ($featured_posts->have_posts()) {
					
						$featured_posts->the_post();
						$shown_ids[] = get_the_ID(); ?>
						
						<div class="primary-featured-post">
						
							<?php get_template_part('partials/content', 'top-story'); ?>
						
						</div>	
												
					<?php }
				}

			if ( $all_posts->have_posts() ) {
				
				while ( $all_posts->have_posts() ) {
					
					$all_posts->the_post();
					
					if (!in_array(get_the_ID(),$shown_ids)) {
						$shown_ids[] = get_the_ID();
						get_template_part( 'partials/content', 'teaser');
					}
				}
				
				largo_content_nav( 'nav-below' );
			} 
			
			?>
			
		</div>
		<?php get_sidebar(); ?>
	</div>

</div>

<?php get_footer();
