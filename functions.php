<?php

/*
 * Configuration
 **/

/* Includes */

$path = ABSPATH . '/wp-content/themes/Largo-for-Banyan-Project/inc';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);


if (!class_exists('Banyan_Project_Calendar_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Calendar_Widget.php');

if (!class_exists('Banyan_Project_Events_Listing_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Events_Listing_Widget.php');

if (!class_exists('Banyan_Project_Related_Events_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Related_Events_Widget.php');

if (!class_exists('Banyan_Project_Related_Articles_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Related_Articles_Widget.php');

//if (!class_exists('Banyan_Project_Homepage_Advertising_Widget'))
//	require_once('inc/widgets/class.Banyan_Project_Homepage_Advertising_Widget.php');

if (!class_exists('Banyan_Project_Email_Signup_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Email_Signup_Widget.php');

if (!class_exists('Banyan_Project_Recent_Blog_Posts_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Recent_Blog_Posts_Widget.php');

if (!class_exists('Banyan_Project_Category_Articles_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Category_Articles_Widget.php');

/**
 * Register a custom homepage layout
 *
 * @see "homepages/layouts/your_homepage_layout.php"
 */
function register_custom_homepage_layouts() {
	
	include_once __DIR__ . '/homepages/layouts/HomepageThreeColumn.php';
	register_homepage_layout('HomepageThreeColumn');

	include_once __DIR__ . '/homepages/layouts/HomepageLead.php';
	register_homepage_layout('HomepageLead');

	unregister_homepage_layout('HomepageBlog');
	unregister_homepage_layout('HomepageSingle');
	unregister_homepage_layout('HomepageSingleWithFeatured');
	unregister_homepage_layout('HomepageSingleWithSeriesStories');
	unregister_homepage_layout('TopStories');
	unregister_homepage_layout('LegacyThreeColumn');	
	
}
add_action('init', 'register_custom_homepage_layouts', 100);


require_once('inc/database.php');
require_once('inc/curated-comments.php');
require_once('inc/article-feedback.php');
require_once('inc/nationbuilder.php');


/* Required Variables */

if (!defined('TIMEZONE'))
	define('TIMEZONE','America/New_York');	

if (!defined('CLICKSTREAM_TABLE'))
	define('CLICKSTREAM_TABLE','click_clickstream');

define('SHOW_GLOBAL_NAV',true);
define('SHOW_MAIN_NAV',false);
define('SHOW_SECONDARY_NAV',false);

/* Config */
	
if (get_option('timezone_string'))
	date_default_timezone_set(get_option('timezone_string')); 
else 
	date_default_timezone_set('America/New_York');
 
/**
 * Placeholders for NationBuilder membership functions
 */ 
function is_member() {
	return is_user_logged_in();	
}
 
/**
 * Meta boxes
 */
 
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

 
/**
 * Custom Fields for Control Panel
 */
 
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
 
/***
 * Additional Options for the Theme Options menu
 **/  
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
 
/**
 * Custom URL type for events
 */

// flush_rules() if our rules are not yet included
function bp_flush_rules(){
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['event/(\d+)$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

// Adding a new rule
function bp_insert_rewrite_rules( $rules )
{
	$newrules = array();
	$newrules['event/(\d+)$'] = 'index.php?p=$matches[1]';
	return $newrules + $rules;
}

add_filter( 'rewrite_rules_array','bp_insert_rewrite_rules' );
//add_filter( 'query_vars','bp_insert_query_vars' );
//add_action( 'wp_loaded','bp_flush_rules' );
add_action( 'after_switch_theme','bp_flush_rules' );

/**
 * Clickstream Tracking
 */
function bp_clickstream_tracking($query)
{	
	global $wpdb;		
	$data = array();	
	
	if (isset($_SERVER['REMOTE_ADDR']))		
		$data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		
	if (isset($_SERVER['HTTP_USER_AGENT']))
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

	$data['timestamp'] = time();
				
	// TODO: add member id, nationbuilder fields
	
	if (wp_get_session_token())
		$data['session_token'] = wp_get_session_token();
	
	if (is_user_logged_in()) {
		
		$data['user_id'] = get_current_user_id();
		
		if (defined('NB_SLUG'))
			$data['nationbuilder_slug'] = NB_SLUG;
		
		$data['nationbuilder_id'] = nb_get_id($data['user_id']);
		$data['is_member'] = nb_is_member($data['user_id']);
	}

	// google analytics fields
	if (isset($_COOKIE['__utma']))
		$data['utma'] = $_COOKIE['__utma'];
		
	if (isset($_COOKIE['__utmb']))
		$data['utmb'] = $_COOKIE['__utmb'];
		
	if (isset($_COOKIE['__utmz']))
		$data['utmz'] = $_COOKIE['__utmz'];
		
	if (isset($_COOKIE['___utmv']))
		$data['utmv'] = $_COOKIE['___utmv'];
		
	if (isset($_COOKIE['___utmx']))
		$data['utmx'] = $_COOKIE['___utmx'];	
	
	$data['request'] = $query->request;
	$data['matched_rule'] = $query->matched_rule;
	$data['matched_query'] = $query->matched_query;
	
	$res = $wpdb->insert(CLICKSTREAM_TABLE,$data);
}

add_action( 'parse_request','bp_clickstream_tracking');


/**
 * Retreive the Largo post-type
 */
function bp_get_post_type($id) {
	$types = wp_get_post_terms($id,'post-type');
	return $types[0];
}

function bp_custom_field_valid($array,$field) {
	if (isset($array[$field]) && is_array($array[$field]) && count($array[$field]) == 1)
		return true;
	else 
		return false;
}

/***
 * Checks to see if events exist for a given category within a timeframe.
 * Used to determine whether or not to display events calendar in sidebar of category pages.
 */

function bp_category_has_events() {

	global $wpdb;
	
	$querystr = "
	select distinct 
		p.*
		, sd.meta_value as `start_date`
		from wp_posts p 
		join wp_postmeta sd on p.ID = sd.post_id
		where p.post_status = 'publish'
		and sd.meta_key = 'start_date'
		and sd.meta_value between date_sub(now(), interval 7 day) and date_add(now(), interval 60 day)
		limit 1
		";

	$events = $wpdb->get_results($querystr, OBJECT);

	if (is_null($events) || count($events) == 0) 
		return false;
	else
		return true;
}


/**
 * Override Largo get_post_template, which doesn't allow child theme template overrides
 */
function get_post_template( $template ) {
	global $post;
	$custom_field = get_post_meta( $post->ID, '_wp_post_template', true );

	// check for child theme template
	if( !empty( $custom_field ) && file_exists( get_stylesheet_directory() . "/{$custom_field}") ) {
		return get_stylesheet_directory() . "/{$custom_field}"; 
	}

	// check for parent theme template
	if( !empty( $custom_field ) && file_exists( get_template_directory() . "/{$custom_field}") ) {
		return get_template_directory() . "/{$custom_field}"; }
}
 
 
function bp_sidebar_span_class() {
	global $post;

	if (is_single() || is_singular()) {
		$default_template = of_get_option( 'single_template' );

		$meta_field = ( is_single() ) ? '_wp_post_template' : '_wp_page_template';

		$custom_template = get_post_meta( $post->ID, $meta_field, true );

		if ( !empty( $custom_template ) ) {
			if ( $custom_template == 'single-one-column.php' )
				return 'col-md-2';
			else if ( $custom_template !== 'single-one-column.php' )
				return 'col-right-sidebar';
		}

		if ( $default_template == 'normal' )
			return 'col-md-2';
		else
			return 'col-right-sidebar';
	} else
		return 'col-right-sidebar';
}
 
/**
 * Return twitter handle, based on twitter link in the Theme Option 
 */
function bp_get_twitter_username($prefix='') {
	$url = of_get_option('twitter_link');
	return $prefix . str_replace(array('https://twitter.com/', 'http://twitter.com/'), '', $url);
}   
 
function bp_get_the_term_ids($post_id=NULL) {
	if ($post_id == NULL)
		$post = get_post();
	else 
		$post = get_post($post_id);
	
	$categories = get_the_category($post->ID);
	
	$term_ids = array();
	
	foreach($categories as $category) {
		$term_ids[] = $category->term_id;
	}
	
	return $term_ids;
} 
 
function bp_adgroup_has_ads($group) {
	
	if (strstr(adrotate_group($group),'<div'))
		return true;
	else
		return false;
} 
 
function bp_render_adgroup($group,$type,$disclosure=true) {
		
	if ($disclosure) :	

		$adwrap = "
			<div class=\"ad-outerwrap ad-outerwrap-{$type}\">
				<div class=\"ad-innerwrap ad-innerwrap-{$type}\">
					<div>Advertisement</div>
					<span>" . adrotate_group($group) . "</span>
				</div>			
			</div>
		";
	
	else :
		
		$adwrap = "
			<div class=\"ad-outerwrap ad-outerwrap-{$type}\">
				<div class=\"ad-innerwrap ad-innerwrap-{$type}\">
					<span>" . adrotate_group($group) . "</span>
				</div>			
			</div>
		";
			
	endif;	
	
	switch ($type) {
		
		case 'sidebar' : 
		
			echo "<aside class=\"widget clearfix\"><h3 class=\"widgettitle\">Our Sponsors</h3>";
			echo($adwrap);
			echo("</aside>");
			return;
		
		case 'leaderboard' :
		default :
			echo($adwrap);
			return;
	}	
} 
 
/**
 * Homepage: Get the post to display at the top of the home single template
 */
function bp_home_single_top() {
	$big_story = null;

	// Cache the terms
	$homepage_feature_term = get_term_by( 'name', __('Homepage Featured', 'largo'), 'prominence' );
	$top_story_term = get_term_by( 'name', __('Top Story', 'largo'), 'prominence' );

	// Get the posts that are both in 'Homepage Featured' and 'Top Story'
	$top_story_posts = get_posts(array(
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'prominence',
				'field' => 'term_id',
				'terms' => $top_story_term->term_id
			),
		),
		'posts_per_page' => 1
	));

	if ( !empty( $top_story_posts ) ) {
		return $top_story_posts[0];
	}

	// Fallback: get the posts that are in "Homepage Featured" but not "Top Story"
	$homepage_featured_posts = get_posts(array(
		'tax_query' => array(
			array(
				'taxonomy' => 'prominence',
				'field' => 'term_id',
				'terms' => $homepage_feature_term->term_id
			)
		),
		'posts_per_page' => 1
	));

	if ( !empty( $homepage_featured_posts ) ) {
		return $homepage_featured_posts[0];
	}

	// Double fallback: Get the most recent post
	$posts = get_posts( array(
		'orderby' => 'date',
		'order' => 'DESC',
		'posts_per_page' => 1
	) );

	if ( !empty( $posts ) ) {
		return $posts[0];
	}

	return null;
}


function bp_simple_excerpt( $the_post=null, $sentence_count = 5, $use_more = false, $more_link = '', $echo = true, $strip_tags = true, $strip_shortcodes = true ) {

		$the_post = get_post($the_post); // Normalize it into a post object

		if (!empty($the_post->post_excerpt)) {
			// if a post has a custom excerpt set, we'll use that
			$content = apply_filters('get_the_excerpt', $the_post->post_excerpt);
		} else if (is_home() && preg_match('/<!--more(.*?)?-->/', $the_post->post_content, $matches) > 0) {
			// if we're on the homepage and the post has a more tag, use that
			$parts = explode($matches[0], $the_post->post_content, 2);
			$content = $parts[0];
		} else {
			// otherwise we'll just do our best and make the prettiest excerpt we can muster
			$content = largo_trim_sentences($the_post->post_content, $sentence_count);
		}

		// optionally strip shortcodes and html
		$output = '';
		if ( $strip_tags && $strip_shortcodes )
			$output .= strip_tags( strip_shortcodes ( $content ) );
		else if ( $strip_tags )
			$output .= strip_tags( $content );
		else if ( $strip_shortcodes )
			$output .= strip_shortcodes( $content );
		else
			$output .= $content;

		if ( $echo )
			echo $output;

		return $output;
}



/**
 * Include compiled style.css
 */
function child_stylesheet() {
	wp_dequeue_style( 'largo-stylesheet' );
	wp_dequeue_style( 'largo-child-styles' );

	$suffix = (LARGO_DEBUG)? '' : '.min';
	wp_enqueue_style( 'googlefonts' , 'https://fonts.googleapis.com/css?family=Merriweather:400,700,400italic|Source+Sans+Pro:400,700|Patua+One' );
	wp_enqueue_style( 'boostrap', get_stylesheet_directory_uri().'/css/bootstrap' . $suffix . '.css' );
	wp_enqueue_style( 'glyphicons', get_stylesheet_directory_uri().'/css/glyphicons.css' );
	wp_enqueue_style( 'bootstrap-datetimepicker', get_stylesheet_directory_uri().'/css/bootstrap-datetimepicker.min.css' );
	wp_enqueue_style( 'largo', get_stylesheet_directory_uri().'/css/largo' . $suffix . '.css' );
	wp_enqueue_style( 'largo-bp', get_stylesheet_directory_uri().'/css/child' . $suffix . '.css' );

}
add_action( 'wp_enqueue_scripts', 'child_stylesheet', 20 );

/**
 * Register a custom widget
 *
 * @see "inc/widgets/your_simple_widget.php"
 */
function register_custom_widget() {
	register_widget( 'Banyan_Project_Calendar_Widget' );
	register_widget( 'Banyan_Project_Events_Listing_Widget' );
	register_widget( 'Banyan_Project_Related_Events_Widget' );
	register_widget( 'Banyan_Project_Related_Articles_Widget' );
	//register_widget( 'Banyan_Project_Homepage_Advertising_Widget' );
	register_widget( 'Banyan_Project_Email_Signup_Widget' );
	register_widget( 'Banyan_Project_Recent_Blog_Posts_Widget' );
	register_widget( 'Banyan_Project_Category_Articles_Widget' );
	
	unregister_widget('largo_author_bio_widget');
	unregister_widget('largo_donate_widget');
	unregister_widget('largo_facebook_widget');
	unregister_widget('largo_follow_widget');
	unregister_widget('largo_prev_next_posts_links_widget');
	unregister_widget('largo_related_posts_widget');
	unregister_widget('largo_sidebar_featured_widget');
	unregister_widget('largo_tag_list_widget');
}

add_action('widgets_init', 'register_custom_widget', 1);

/**
 * Include your theme's javascript
 *
 * @see "js/your_theme.js"
 */
function enqueue_custom_script() {
	
	$suffix = (LARGO_DEBUG)? '' : '.min';

	wp_enqueue_script(
		'jquery-ui'
		, 'http://code.jquery.com/ui/1.11.2/jquery-ui.js'
		, array('jquery')
		, '1.11.2'
		, true
	);

	wp_enqueue_script(
		'bootstrap'
		, '/wp-content/themes/Largo-for-Banyan-Project/js/bootstrap'. $suffix . '.js'
		, array('jquery')
		, '3.3.5'
		, true
	);
	
	wp_enqueue_script(
		'moment'
		,'/wp-content/themes/Largo-for-Banyan-Project/js/moment.js'
		,array()
		,'2.10.6'
		,true
	);
	
	wp_enqueue_script(
		'underscore'
		,'/wp-content/themes/Largo-for-Banyan-Project/js/underscore' . $suffix . '.js'
		,array()
		,'1.8.3'
		,true
	);

	wp_enqueue_script(
		'clndr'
		,'/wp-content/themes/Largo-for-Banyan-Project/js/clndr' . $suffix . '.js'
		,array()
		,'1.2.16'
		,true
	);

	wp_enqueue_script(
		'bootstrap-datetimepicker'
		,'/wp-content/themes/Largo-for-Banyan-Project/js/bootstrap-datetimepicker.min.js'
		,array('jquery','bootstrap')
		,'4.17.37'
		,true
	);

	wp_enqueue_script(
		'banyan'
		,'/wp-content/themes/Largo-for-Banyan-Project/js/banyan.js'
		,array()
		,'0.1'
		,true
	);

	
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script');
