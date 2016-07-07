<?php

/* General Template Functions */

/**
 * Retreives the Largo post-type
 */
function bp_get_post_type($id) {
	$types = wp_get_post_terms($id,'post-type');
	return $types[0];
}

/**
 * Determines whether the referenced custom field is valid and able to be displayed.
 */  
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
 * Return twitter username, based on twitter link in the Theme Option 
 */
function bp_get_twitter_username($prefix='') {
	$url = of_get_option('twitter_link');
	return $prefix . str_replace(array('https://twitter.com/', 'http://twitter.com/'), '', $url);
}   

/*
 * Returns an array of category (or term) IDs for the post
 */   
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

/**
 * Get the post to display at the top of the home single template.
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

