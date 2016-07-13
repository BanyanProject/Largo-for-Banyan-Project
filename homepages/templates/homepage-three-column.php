<?php

global $tags, $shown_ids;

$topstory = largo_get_featured_posts(array(
	'tax_query' => array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'prominence',
			'field' => 'slug',
			'terms' => 'top-story'
		),
		array(
			'taxonomy' => 'post-type',
			'field' => 'slug',
			'terms' => array('news','blog')
		)
	),
	'showposts' => 1
));

$homefeatured = largo_get_featured_posts(array(
	'tax_query' => array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'prominence',
			'field' => 'slug',
			'terms' => 'homepage-featured'
		),
		array(
			'taxonomy' => 'post-type',
			'field' => 'slug',
			'terms' => array('news','blog')
		)
	),
	'showposts' => 6
));


?>

<div id="homepage-three-column">
	
	<div class="row">
		
		<div class="col-main">
			
			<div class="row">

				<div class="col-md-7">

					<?php if ($topstory->have_posts()) {
						while ($topstory->have_posts()) {
							
							$topstory->the_post();
							$shown_ids[] = get_the_ID();
							
							get_template_part('partials/content', 'top-story');					

						}
					} 
									
					while ($homefeatured->have_posts()) {
						$homefeatured->the_post();
						if (!in_array(get_the_ID(),$shown_ids)) {
							
							$shown_ids[] = get_the_ID();
						
							get_template_part( 'partials/content', 'teaser');
	 					}
 					}
					
					?>
					
				</div>
				
				<aside id="middlebar" class="col-md-5">
					<div class="widgetarea" role="complementary">
						
						<?php if (!dynamic_sidebar('homepage-middle-column')) { ?>
							<p><?php _e('Please add widgets to this content area in the WordPress admin area under appearance > widgets.', 'largo'); ?></p>
						<?php } ?>
						
					</div>		
				</aside>
				
			</div>
			
		</div>
		
			
		<?php get_sidebar(); ?>
					
	</div>
	
</div>
