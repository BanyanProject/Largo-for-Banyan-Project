<?php

/* Clickstream Tracking  */

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
	
	$res = $wpdb->insert('click_clickstream',$data);
}

add_action( 'parse_request','bp_clickstream_tracking');

?>