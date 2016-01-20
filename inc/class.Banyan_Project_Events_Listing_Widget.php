<?php

class Banyan_Project_Events_Listing_Widget extends WP_Widget {
	
	protected $default_title = 'Upcoming Events';
	protected $default_listings = 5;
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Events_Listing_Widget', // Base ID
			__('Banyan Project Events Listing Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'A widget displaying a list of upcoming events.', 'text_domain' )
			)
		);
	}
	
	public function widget( $args, $instance ) {

		global $wpdb;
		$limit = $instance['listings'];
		
		// Query for upcoming events
		
		$querystr = "
		select distinct 
			p.*
			, sd.meta_value as `start_date`
			, st.meta_value as `start_time`
			, ed.meta_value as `end_date`
			, et.meta_value as `end_time`
			, lt.meta_value as `location_title`
		from wp_posts p 
			join wp_postmeta sd on p.ID = sd.post_id
			join wp_postmeta st on p.ID = st.post_id
			join wp_postmeta ed on p.ID = ed.post_id
			join wp_postmeta et on p.ID = et.post_id
			join wp_postmeta lt on p.ID = lt.post_id
		where p.post_status = 'publish'
			and sd.meta_key = 'start_date'
			and st.meta_key = 'start_time'
			and ed.meta_key = 'end_date' 
			and ed.meta_value between now() and date_add(now(), interval 60 day)
			and et.meta_key = 'end_time'
			and lt.meta_key = 'location_title'
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
        		
        			<div class="teaser-date-location">
        				<span class="upcoming-start-date"><?php echo(date("F j",strtotime($event->start_date))); ?></span>								
						<?php if ($event->start_date != $event->end_date) : ?>
						- <span class="upcoming-end-date"><?php echo(date("F j",strtotime($event->end_date))); ?></span>,								
						<?php else: ?>
						<span class="upcoming-start-time"><?php echo(date("g:ia",strtotime($event->start_time))); ?></span>,
						<?php endif; ?>
        				
        				<span class="event-location"><?php echo($event->location_title); ?></span>       				
        			</div>
        		
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
			<label for="<?php echo $this->get_field_id( 'listings' ); ?>"><?php _e( 'Number of Event Listings to Display:' ); ?></label> 
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