<?php

class Banyan_Project_Related_Events_Widget extends WP_Widget {
	
	protected $default_title = 'Related Events';
	protected $default_listings = 3;
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Related_Events_Widget', // Base ID
			__('Banyan Project Related Events  Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'A widget displaying a list of upcoming events that related to a given article.', 'text_domain' )
			)
		);
	}
	
	public function widget( $args, $instance ) {

		global $wpdb;
		$limit = $instance['listings'];
		$post_id = get_the_ID();	
			
		$related = new Largo_Related(100,$post_id);
		$ids = array_diff($related->ids(), array( 0 => $post_id));
		
		
		
		$querystr = "
		select distinct 
			p.*
			, sd.meta_value as `start_date`
			, ed.meta_value as `end_date`
			, lt.meta_value as `location_title`
			, a.meta_value as `address`
			, c.meta_value as `city`
		from wp_posts p 
			join wp_postmeta sd on p.ID = sd.post_id
				and sd.meta_key = 'start_date'
			join wp_postmeta ed on p.ID = ed.post_id
				and ed.meta_key = 'end_date' 
			join wp_postmeta lt on p.ID = lt.post_id
				and lt.meta_key = 'location_title'
			left join wp_postmeta a on p.ID = a.post_id
				and a.meta_key = 'address'
			left join wp_postmeta c on p.ID = c.post_id
				and c.meta_key = 'city'
		where p.post_status = 'publish'
			and ed.meta_value between now() and date_add(now(), interval 60 day)
			and p.ID in ( " . join(',',$ids) . ")
		order by `start_date`
		limit {$limit}
		";
		
		$events = $wpdb->get_results($querystr, OBJECT);
	
     	if (isset($args['before_widget'])) echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		else {
			apply_filters( 'widget_title', $this->default_title );
		}
		
		?>
		
        <div id="events-listing-widget" class="clearfix">
                                    
        <?php foreach ($events as $event) { ?>
        	<article class="post-type-event">
	        	<div class="entry-content clearfix">
	        		
        			<?php echo(get_the_post_thumbnail($event->ID,'post-thumbnail',array('class' => 'alignleft'))); ?>
        			
	        		<h4 class="entry-title"><a href="/event/<?php echo($event->ID); ?>" ><?php echo($event->post_title); ?></a></h4>
        		
        			<div class="teaser-date-location clearfix">
        				<span class="upcoming-start-date"><?php echo(date("F j",strtotime($event->start_date))); ?></span>								
						<?php if (date("Y-m-d",strtotime($event->start_date)) != date("Y-m-d",strtotime($event->end_date))) : ?>
						- <span class="upcoming-end-date"><?php echo(date("F j",strtotime($event->end_date))); ?></span>,								
						<?php else: ?>
						<span class="upcoming-start-time"><?php echo(date("g:i a",strtotime($event->start_date))); ?></span>,
						<?php endif; ?>
        				
        				<span class="event-location"><?php echo($event->location_title); ?></span>       				
        			</div>
     
     				<?php get_template_part( 'partials/social', 'horizontal-small' ); ?>

        		
	        	</div>
        	</article>	
        <?php } ?>
         
        	<p>For a complete event listing, see the <a href="/community-events-calendar">Community Events Calendar</a>.</p>      
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
			<label for="<?php echo $this->get_field_id( 'listings' ); ?>"><?php _e( 'Number of Events to Display:' ); ?></label> 
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