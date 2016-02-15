<?php

class Banyan_Project_Homepage_Advertising_Widget extends WP_Widget {
	
	protected $default_title = 'Our Sponsors';
		
	function __construct() {
		parent::__construct(
			'Banyan_Project_Homepage_Advertising_Widget', // Base ID
			__('Banyan Project Homepage Advertising Widget', 'text_domain'), // Name
			array( 
				'description' => 
				__( 'Displays medium rectangle ads on the sidebar of the homepage.', 'text_domain' )
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
		
        <div class="ad-outerwrap ad-outerwrap-sidebar">
        	<div class="ad-innerwrap ad-innerwrap-sidebar">
 				<div>Advertisement</div>
       			<?php echo adrotate_group(2); ?>
        	</div>
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