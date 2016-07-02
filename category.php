<?php
/**
 * Template for category archive pages
 */
 
wp_enqueue_script(
	'category',
	'/wp-content/themes/Largo-for-Banyan-Project/js/calendar.php?cid=' . get_queried_object_id(),
	array('jquery'),
	'0.1',
	true
);
   
   
function add_comment_reply_script() {
?>
<script type="text/javascript" src='http://wp.bp.trunk/wp-includes/js/comment-reply.min.js'></script>
<?php
}

add_action( 'wp_footer', 'add_comment_reply_script' );
   
   
   
get_header();

global $tags, $paged, $post, $shown_ids;

$title = single_cat_title('', false) . ' Forum';
$description = category_description();
$permalink = get_category_link(get_queried_object_id());
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

?>

<div class="col-md-12 clearfix">

	<div class="row row-fluid clearfix">
		<div class="col-main" role="main" id="content">
			
			<header class="archive-background clearfix">
				<h1 class="page-title"><?php echo($title); ?></h1>
				<?php if ($description) : ?><p class="archive-description"><?php echo $description; ?></p><?php endif; ?>
				<p>
					<?php bloginfo('name'); ?>' forums are a place for community members to engage in a sustained conversation about issues related to a specific topic.
					Join the conversation below by commenting on our featured story below, responding to others' comments, or raising a new issue related to <?php echo(strtolower($title)); ?> that needs attention.
				</p>
				
				<?php if (!is_user_logged_in()) : ?>
				<p>
					You must be logged in to comment. 
					You may <a href="/login/">login here</a>.
					You may <a href="/register/">create a user account here</a>.
				</p>
				<?php endif; ?> 
							
				<?php get_template_part('partials/social', 'horizontal'); ?>
				
			</header>
						
			<div class="stories" id="category-featured">
						
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
			
			?>
			
			</div>
			
			<div id="comments-forum">
				<h3>Join the Conversation</h3>
			</div>
			
			<?php 	
		
				do_action('largo_before_comments');
				
				bp_comments_template( get_queried_object_id());
	
				do_action('largo_after_comments');
			
			?>
		</div>

			
		
		
		<?php get_sidebar(); ?>
	</div>

</div>

<?php get_footer();
