<?php

class Banyan_Project_Recent_Blog_Posts_Widget extends WP_Widget {
	
	protected $default_title = 'Recent Blog Posts';
	protected $default_listings = 3;
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Recent_Blog_Posts_Widget', // Base ID
			__('Banyan Project Recent Blog Posts Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'A widget displaying recent blog posts.', 'text_domain' )
			)
		);
	}
	
	public function widget( $args, $instance ) {

		global $shown_ids;
		
		$limit = $instance['listings'];
				
		$posts = new WP_Query( array(
 			'posts_per_page' => $instance['listings']
 			,'ignore_sticky_posts' => true
 			,'tax_query' => array(
				array(
					'taxonomy' => 'post-type'
					,'field' => 'slug'
					,'terms' => 'blog'
				)
			)
 		) );

	
     	if (isset($args['before_widget'])) echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		else {
			apply_filters( 'widget_title', $this->default_title );
		}
		
 		if ( $posts->have_posts()) : ?>
		
        <div id="wrap-related-articles" class="clearfix">
                                    
	 		<?php while ( $posts->have_posts() ) {
	 			
	 			$posts->the_post(); 
	 			
	 			if (!in_array(get_the_ID(),$shown_ids)) {
				
	 				?>
	 			
		        	<article class="post-type-news">
			        	<div class="entry-content clearfix">
			        		     		
		        			<?php echo(get_avatar(get_the_author_meta('ID'),'thumbnail',array('class' => 'alignleft'))); ?>
		        			
			        		<h5 class="top-tag"><?php largo_top_term(); ?></h5>
	
			        		<h4 class="entry-title"><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h4>	        		
		        
		        			<?php largo_excerpt(get_the_ID(), 1, false, '', true); ?>
	
							<?php get_template_part( 'partials/social', 'horizontal-small' ); ?>
	        			
			        	</div>
		        	</article>		        	
		        	
	      		<?php }
			} ?>
	      
	      <?php else : ?>
	      
	      <p>There are no recent blog posts to display.</p>
	      
	      <?php endif; ?>
	         
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
			<label for="<?php echo $this->get_field_id( 'listings' ); ?>"><?php _e( 'Number of Blog posts to display:' ); ?></label> 
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