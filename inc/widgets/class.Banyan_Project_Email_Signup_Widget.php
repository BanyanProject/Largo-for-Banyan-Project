<?php

class Banyan_Project_Email_Signup_Widget extends WP_Widget {
	
	protected $default_title = 'Join Our Email Newsletter';
	protected $default_description;
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Email_Signup_Widget', // Base ID
			__('Banyan Project Email Signup Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'Ajax signup form for email signups.', 'text_domain' )
			)
		);
		
		$this->default_description = "Sign-up to receive weekly email updates from " . AFFILIATE_NAME . ".";
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

		<?php
				
		if ( ! empty( $instance['description'] ) ) {
			echo "<p>" .  $instance['description'] . "</p>";
		}
								
		?>
		
		<form class="form-inline">
			  <div class="form-group">
			    <label class="sr-only" for="exampleInputEmail3">Enter your email address</label>
			    <input type="email" class="form-control" id="exampleInputEmail3" placeholder="Your email address">
			  </div>
			  <button type="submit" class="btn btn-default">I'm in!</button>
		</form>
			
		<?php
		
		if (isset($args['after_widget'])) echo $args['after_widget'];
	}
	
	public function form( $instance ) {

		if ( isset( $instance[ 'title' ] ) ) 
			$title = $instance[ 'title' ];
		else 
			$title = __( $this->default_title, 'text_domain' );

		if ( isset( $instance[ 'description' ] ) ) 
			$description = $instance[ 'description' ];
		else 
			$description = __( $this->default_description, 'text_domain' );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Description:' ); ?></label>
			<textarea class="widefat"  id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" ><?php echo esc_attr( $description ); ?></textarea>		
		</p>
		<?php 
	}	

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['description'] = ( ! empty( $new_instance['description'] ) ) ? strip_tags( $new_instance['description'] ) : '';
		return $instance;
	}

}

?>