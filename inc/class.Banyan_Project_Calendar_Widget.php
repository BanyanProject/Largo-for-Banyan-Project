<?php

class Banyan_Project_Calendar_Widget extends WP_Widget {
	
	protected $default_title = 'Events Calendar';
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Calendar_Widget', // Base ID
			__('Banyan Project Calendar Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'A sidebar widget displaying the Community Events Calendar for Banyan Project affiliates, optionally organized by category.', 'text_domain' )
			)
		);
	}
	
	public function widget( $args, $instance ) {
	
     	if (isset($args['before_widget'])) echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		else {
			apply_filters( 'widget_title', $this->default_title );
		}
		
		?>
		
        <div id="wrap-calendar-widget" class="clearfix">
          <script type="text/template" id="clndr-widget-template">
          	
            <div class="clndr-controls">
			  <div class="clndr-control-button">
			    <span class="clndr-previous-button">&lsaquo;</span>
			  </div>
              <div class="month"><%= month %></div>
			  <div class="clndr-control-button">
			    <span class="clndr-next-button">&rsaquo;</span>
		      </div>
            </div>
            
            <div class="clndr-grid">
            	
              <div class="days-of-the-week">
                <% _.each(daysOfTheWeek, function(day) { %>
                  <div class="header-day"><%= day %></div>
                <% }); %>
              </div>
              
			  <% for(var i = 0; i < numberOfRows; i++){ %>
			    <div class="week">											
				  <% for(var j = 0; j < 7; j++){ var d = j + i * 7; %>						
                    <div class="<%= days[d].classes %>" >
                      <span class="day-number"><%= days[d].day %></span>
                    </div>
                  <% } %>
                </div>
              <% } %>
              
            </div>

            <div class="event-listing">
              <h4 class="event-listing-title">Events This Month</h4>
              
              <% if (eventsThisMonth.length == 0) { %>
                <p><small>There are no event listings this month.</small></p>
              <% } %>
              
              <% _.each(eventsThisMonth, function(event) { %>
                <div class="event-item">
                  <div class="event-title"><a href="<%= event.url %>"><%= event.title %></a></div>
                  <div class="event-date-location"><small><%= formatMMd(event.date) %>, <%= event.locationTitle %></small></div>
                </div>
              <% }); %>
              
              
            </div>


          
          </script>
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
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}	

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

}

?>