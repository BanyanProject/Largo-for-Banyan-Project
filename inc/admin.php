<?php

 /* Meta boxes */
 
function add_post_types_meta_box() {
	add_meta_box('bp-post-types-meta', 'Post Type', 'bp_post_types_meta_box', 'post', 'side', 'high');	
} 
 
function bp_post_types_meta_box() {
	global $post;
	echo '<input type="hidden" name="post_type_noncename" id="post_type_noncename" value="' .
		wp_create_nonce('post_type_noncename') . '" />';
     
    // Get the location data if its already been entered
	$pt = bp_get_post_type($post->ID);
	$terms = get_terms('post-type', 'hide_empty=0');

	echo '<p>Select and save the appropriate post type.  The post type will determine what custom fields are displayed in this editor.</p>';
	echo '<select name="_post_type" id="post_theme">'."\n";

    foreach ($terms as $term) {
    	if (!isset($pt->slug) && $term->slug == 'news')
        	echo '<option class="post-type-option" value="' . $term->slug . '" selected>'. $term->name . "</option>\n"; 
		elseif ($pt->slug == $term->slug)
        	echo '<option class="post-type-option" value="' . $term->slug . '" selected>'. $term->name . "</option>\n"; 
		else
        	echo '<option class="post-type-option" value="' . $term->slug . '">'. $term->name . "</option>\n"; 
    }
	echo "</select>\n";  
	
}

add_action( 'add_meta_boxes', 'add_post_types_meta_box');
 
function bp_save_post_type_meta($post_id, $post) {
	
	if ( !wp_verify_nonce( $_POST['post_type_noncename'], 'post_type_noncename' ))
		return $post->ID;

	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	if (!is_string($_POST['_post_type']))
		return $post->ID;
	
	$pt = $_POST['_post_type'];
	
	wp_set_object_terms($post->ID, $pt, 'post-type');
}

add_action('save_post', 'bp_save_post_type_meta', 1, 2); // save the custom fields

/* Custom Fields */
 
function bp_register_custom_fields() {
							
	slt_cf_setting( 'prefix', '' );	

	$location_fields = array(
		array(
			'name' => 'location_title'
			, 'label' => 'Location Title'
			, 'type' => 'text'
		)		
		, array(
			'name' => 'address'
			, 'label' => 'Address'
			, 'type' => 'text'
		)		
		, array(
			'name' => 'city'
			, 'label' => 'City'
			, 'type' => 'text'
		)		
		, array(
			'name' => 'state_province'
			, 'label' => 'State/Province'
			, 'type' => 'text'
		)		
		, array(
			'name' => 'google_map_embed'
			, 'label' => 'Google Map Embed'
			, 'type' => 'gmap'
			, 'gmap_type' => 'roadmap'
			, 'width' => 600
			, 'height' => 450
		)		
		, array(
			'name' => 'google_map_description'
			, 'label' => 'Description of Google Map'
			, 'type' => 'textarea'
		)		
	);
	
	$location_box_events = array(
		'type' => 'post'
		, 'id' => 'location-fields'
		, 'title' => 'Location Fields'
		, 'description' => "Location fields for event posts."
		, 'context' => 'above-content'
		, 'priority' => 'default'
		, 'fields' => array()
	);
	
	foreach ($location_fields as $loc) {
		$loc['scope'] = array( 'post-type' => array( 'Event' ) );
		$location_box_events['fields'][] = $loc;
	}

	slt_cf_register_box($location_box_events);

	slt_cf_register_box(array(
		'type' => 'post'
		, 'id' => 'date-time-fields'
		, 'title' => 'Date and Time '
		, 'description' => "Required fields for events. Events will not display unless both start and end dates are selected."
		, 'context' => 'above-content'
		, 'priority' => 'default'
		, 'fields' => array(
			0 => array(
				'scope' => array( 'post-type' => array( 'Event' ) )
				, 'name' => 'start_date'
				, 'label' => 'Start Date and Time'
				, 'type' => 'datetime'
				, 'datepicker_format' => 'yy-mm-dd'
				, 'timepicker_format' => 'hh:mm'
							)		
			, 1 => array(
				'scope' => array( 'post-type' => array( 'Event' ) )
				, 'name' => 'end_date'
				, 'label' => 'End Date and Time'
				, 'type' => 'datetime'
				, 'datepicker_format' => 'yy-mm-dd'
				, 'timepicker_format' => 'hh:mm'
			)		
		)
	));
		
	$location_box_pages = array(
		'type' => 'post'
		, 'id' => 'location-fields'
		, 'title' => 'Location Fields'
		, 'description' => "Location fields for business profiles."
		, 'context' => 'normal'
		, 'priority' => 'default'
		, 'fields' => array()	
	);

	foreach ($location_fields as $loc) {
		$loc['scope'] = array('page');
		$location_box_pages['fields'][] = $loc;
	}
	
	slt_cf_register_box($location_box_pages);
	
	slt_cf_register_box(array(
		'type' => 'post'
		, 'id' => 'optional-fields'
		, 'title' => 'Optional Fields'
		, 'description' => "Optional fields available to all posts and pages."
		, 'context' => 'normal'
		, 'priority' => 'default'
		, 'fields' => array(
			0 => array(
				'scope' => array('post','page')
				, 'name' => 'subtitle'
				, 'label' => 'Subtitle'
				, 'type' => 'text'
			)		
			, 1 => array(
				'scope' => array('post','page')
				, 'name' => 'shirttail'
				, 'label' => 'Shirttail'
				, 'type' => 'textarea'
			)		
		)
	));
	
	slt_cf_register_box(array(
		'type' => 'post'
		, 'id' => 'optional-event-fields'
		, 'title' => 'Optional Event Fields'
		, 'description' => "Optional fields available to events."
		, 'context' => 'normal'
		, 'priority' => 'default'
		, 'fields' => array(
			0 => array(
				'scope' => array( 'post-type' => array( 'Event' ) )
				, 'name' => 'event_email'
				, 'label' => 'Contact Email'
				, 'type' => 'text'
			)		
			, 1 => array(
				'scope' => array( 'post-type' => array( 'Event' ) )
				, 'name' => 'event_url'
				, 'label' => 'External URL'
				, 'type' => 'text'
			)		
			, 2 => array(
				'scope' => array( 'post-type' => array( 'Event' ) )
				, 'name' => 'cost'
				, 'label' => 'Cost'
				, 'type' => 'text'
			)		
		)
	));
}
 
add_action('init','bp_register_custom_fields');


/* Theme Options */  

function bp_options($options) {
 	
	$options[] = array(
		'name' 	=> __('Additional Banyan Project Options', 'largo'),
		'type'	=> 'info'
	);

	$options[] = array(
		'desc' 	=> __('<b>Default From Email Address.</b> The email address that transactional emails should be sent from. The blog title will be used for the from name.', 'largo'),
		'id' 	=> 'from_email',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Executive Director Name.</b> Who is the Executive Director of this organization? Used for transactional emails.', 'largo'),
		'id' 	=> 'ed_name',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Executive Director Email.</b> What is the Executive Director\'s email address? Used for transactional emails.', 'largo'),
		'id' 	=> 'ed_email',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Administrator Name.</b> Who is the Administrator of this organization? Used for transactional emails.', 'largo'),
		'id' 	=> 'admin_name',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Administrator Email.</b> What is the Administrator\'s email address? Used for transactional emails.', 'largo'),
		'id' 	=> 'admin_email',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Editor Name.</b> Who is the Editor of this organization? Used for transactional emails.', 'largo'),
		'id' 	=> 'editor_name',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Editor Email.</b> What is the Editor\'s email address? Used for transactional emails.', 'largo'),
		'id' 	=> 'editor_email',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	$options[] = array(
		'desc' 	=> __('<b>Location.</b> What is the colloquial name of the city or location in which you operate?', 'largo'),
		'id' 	=> 'location_col',
		'std' 	=> '',
		'type' 	=> 'text'
	);

	return $options;		
} 
 
add_filter('largo_options','bp_options'); 
 
 
 
 
