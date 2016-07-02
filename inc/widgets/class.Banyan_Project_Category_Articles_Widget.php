<?php

class Banyan_Project_Category_Articles_Widget extends WP_Widget {
	
	protected $default_title = 'Category Articles';
	protected $default_listings = 10;
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Category_Articles_Widget', // Base ID
			__('Banyan Project Category Articles Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'A widget displaying a list of articles for a given category.', 'text_domain' )
			)
		);
	}
	
	public function widget( $args, $instance ) {

		global $shown_ids;

		$category_id = get_queried_object_id();
		$limit = $instance['listings'];
				
		$rel_posts = new WP_Query( array(
			'cat' => $category_id
			,'post__not_in' => $shown_ids
 			,'posts_per_page' => $instance['listings']
 			,'tax_query' => array(
				array(
					'taxonomy' => 'post-type'
					,'field' => 'slug'
					,'terms' => array('news','blog')
				)
			)
 		) );

	
     	if (isset($args['before_widget'])) echo $args['before_widget'];
		
		if ( is_category()) {
			echo $args['before_title'] . apply_filters( 'widget_title', get_cat_name($category_id) . ' Articles' ). $args['after_title'];
		}
		else {
			echo apply_filters( 'widget_title', $this->default_title );
		}
		
		?>
		
        <div id="wrap-related-articles" class="clearfix">
        
 		<?php if ( $rel_posts->have_posts()) : ?>
                                    
	 		<?php while ( $rel_posts->have_posts() ) { 
	 			$rel_posts->the_post(); ?>
	 			
	        	<article class="post-type-news">
		        	<div class="entry-content clearfix">
		        		
	        			
		        		<?php if (bp_get_post_type(get_the_ID())->slug == 'blog') : ?>
		        		
	        			<?php echo(get_avatar(get_the_author_meta('ID'),'thumbnail',array('class' => 'alignleft'))); ?>
	        			
		        		<h5 class="top-tag"><?php largo_top_term(); ?></h5>

						<?php else : ?>

	        			<?php echo(get_the_post_thumbnail(get_the_ID(),'thumbnail',array('class' => 'alignleft'))); ?>
	        			
	        			<?php endif; ?>

		        		<h4 class="entry-title"><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h4>
	        		
	        			<?php largo_excerpt(get_the_ID(), 1, false, '', true); ?>

						<?php get_template_part( 'partials/social', 'horizontal-small' ); ?>
	        			
		        	</div>
	        	</article>	
	        <?php } ?>
	     
	     <?php else: ?>
	     
	     <p>No additional articles have been published related to this topic.</p>	
	      
	     <?php endif; ?>
	         
        	<p><a href="/contact"><span class="glyphicon glyphicon-hand-up"></span> Suggest an article topic</a> to our editors.</p>      
        </div>		
				
		<?php
		
		if (isset($args['after_widget'])) echo $args['after_widget'];
	}
	
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( $this->default_title, 'text_domain' );
		}
		
		if ( isset( $instance[ 'listings' ]) && is_numeric($instance[ 'listings' ]) ) {
			$listings = $instance[ 'listings' ];
		}
		else {
			$listings = __( $this->default_listings, 'text_domain' );
		}
		
		
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'listings' ); ?>"><?php _e( 'Number of News and Blog posts to Display:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'listings' ); ?>" name="<?php echo $this->get_field_name( 'listings' ); ?>" type="text" value="<?php echo esc_attr( $listings ); ?>">
		</p>
		<?php 
	}	

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['listings'] = ( ! empty( $new_instance['listings'] ) ) ? strip_tags( $new_instance['listings'] ) : $this->default_listings;
		return $instance;
	}

}

?>