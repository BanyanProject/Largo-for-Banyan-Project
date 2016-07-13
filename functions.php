<?php

/* Configuration - Includes */

$path = ABSPATH . '/wp-content/themes/Largo-for-Banyan-Project/inc';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once('inc/database.php');
require_once('inc/largo-overrides.php');
require_once('inc/admin.php');
require_once('inc/clickstream.php');
require_once('inc/template.php');
require_once('inc/ad-rotate.php');
require_once('inc/curated-comments.php');
require_once('inc/article-feedback.php');
require_once('inc/nationbuilder.php');

/* Configuration - Required Variables */

define('SHOW_STICKY_NAV',true);
define('SHOW_GLOBAL_NAV',true);
define('SHOW_MAIN_NAV',false);
define('SHOW_SECONDARY_NAV',false);

/* Configuration - Timezone */
	
if (get_option('timezone_string'))
	date_default_timezone_set(get_option('timezone_string')); 
else 
	date_default_timezone_set('America/New_York');

/* Configuration - Widgets */

if (!class_exists('Banyan_Project_Calendar_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Calendar_Widget.php');

if (!class_exists('Banyan_Project_Events_Listing_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Events_Listing_Widget.php');

if (!class_exists('Banyan_Project_Related_Events_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Related_Events_Widget.php');

if (!class_exists('Banyan_Project_Related_Articles_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Related_Articles_Widget.php');

if (!class_exists('Banyan_Project_Email_Signup_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Email_Signup_Widget.php');

if (!class_exists('Banyan_Project_Recent_Blog_Posts_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Recent_Blog_Posts_Widget.php');

if (!class_exists('Banyan_Project_Category_Articles_Widget'))
	require_once('inc/widgets/class.Banyan_Project_Category_Articles_Widget.php');

/**
 * Register and unregister custom widgets
 */
function register_custom_widget() {
	register_widget( 'Banyan_Project_Calendar_Widget' );
	register_widget( 'Banyan_Project_Events_Listing_Widget' );
	register_widget( 'Banyan_Project_Related_Events_Widget' );
	register_widget( 'Banyan_Project_Related_Articles_Widget' );
	register_widget( 'Banyan_Project_Email_Signup_Widget' );
	register_widget( 'Banyan_Project_Recent_Blog_Posts_Widget' );
	register_widget( 'Banyan_Project_Category_Articles_Widget' );
	
	unregister_widget('largo_author_widget');
	unregister_widget('largo_donate_widget');
	unregister_widget('largo_facebook_widget');
	unregister_widget('largo_follow_widget');
	unregister_widget('largo_prev_next_post_links_widget');
	unregister_widget('largo_recent_posts_widget');
	unregister_widget('largo_related_posts_widget');
	unregister_widget('largo_sidebar_featured_widget');
	unregister_widget('largo_tag_list_widget');
}

add_action('widgets_init', 'register_custom_widget', 1);


/* Configuration - Homepage Layouts */

/**
 * Register and unregisters homepage layouts
 * @see "homepages/layouts/"
 */
function register_custom_homepage_layouts() {
	
	include_once __DIR__ . '/homepages/layouts/HomepageThreeColumn.php';
	register_homepage_layout('HomepageThreeColumn');

	include_once __DIR__ . '/homepages/layouts/HomepageLead.php';
	register_homepage_layout('HomepageLead');

	include_once __DIR__ . '/homepages/layouts/HomepageBanyan.php';
	register_homepage_layout('HomepageBanyan');


	unregister_homepage_layout('HomepageBlog');
	unregister_homepage_layout('HomepageSingle');
	unregister_homepage_layout('HomepageSingleWithFeatured');
	unregister_homepage_layout('HomepageSingleWithSeriesStories');
	unregister_homepage_layout('TopStories');
	unregister_homepage_layout('LegacyThreeColumn');	
	
}
add_action('init', 'register_custom_homepage_layouts', 100);

/* Required Plugins Alert */

add_action('admin_notices', 'showAdminMessages');

function showAdminMessages()
{
	$plugin_messages = array();

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	// AdRotate Pro
	if(!is_plugin_active( 'adrotate-pro/adrotate-pro.php' ))
	{
		$plugin_messages[] = 'This theme requires you to install the AdRotate Pro plugin, <a href="https://ajdg.solutions/products/adrotate-for-wordpress/">download it here</a>.';
	}

	// Developer's Custom Fields
	if(!is_plugin_active( 'developers-custom-fields/slt-custom-fields.php' ))
	{
		$plugin_messages[] = 'This theme requires you to install the Developer\'s Custom Fields plugin, <a href="https://wordpress.org/plugins/developers-custom-fields/">download it here</a>.';
	}

	// Theme my Login
	if(!is_plugin_active( 'theme-my-login/theme-my-login.php' ))
	{
		$plugin_messages[] = 'This theme requires you to install the Theme My Login plugin, <a href="https://wordpress.org/plugins/theme-my-login/">download it here</a>.';
	}

	if(count($plugin_messages) > 0)
	{
		echo '<div id="message" class="error">';

			foreach($plugin_messages as $message)
			{
				echo '<p><strong>'.$message.'</strong></p>';
			}

		echo '</div>';
	}
}

 
/* Custom URL type for events */

function bp_event_rule(){

	// flush_rules() if our rules are not yet included

	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['event/(\d+)$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

function bp_insert_rewrite_rules( $rules )
{
	$newrules = array();
	$newrules['event/(\d+)$'] = 'index.php?p=$matches[1]';
	return $newrules + $rules;
}

add_filter( 'rewrite_rules_array','bp_insert_rewrite_rules' );
add_action( 'after_switch_theme','bp_event_rule' );

/* CSS */

function child_stylesheet() {
	wp_dequeue_style( 'largo-stylesheet' );
	wp_dequeue_style( 'largo-child-styles' );

	$suffix = (LARGO_DEBUG)? '' : '.min';
	wp_enqueue_style( 'googlefonts' , 'https://fonts.googleapis.com/css?family=Merriweather:400,700,400italic|Source+Sans+Pro:400,700|Patua+One' );
	wp_enqueue_style( 'boostrap', get_stylesheet_directory_uri().'/css/bootstrap' . $suffix . '.css' );
	wp_enqueue_style( 'glyphicons', get_stylesheet_directory_uri().'/css/glyphicons.css' );
	wp_enqueue_style( 'bootstrap-datetimepicker', get_stylesheet_directory_uri().'/css/bootstrap-datetimepicker.min.css' );
	wp_enqueue_style( 'largo', get_stylesheet_directory_uri().'/css/largo' . $suffix . '.css' );
	wp_enqueue_style( 'largo-bp', get_stylesheet_directory_uri().'/css/banyan' . $suffix . '.css' );

}

add_action( 'wp_enqueue_scripts', 'child_stylesheet', 20 );

/* Javascript */

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




   



