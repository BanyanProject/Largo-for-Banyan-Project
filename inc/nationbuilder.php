<?php

require_once('class.NationbuilderAPI.php');
require_once('class.NameParser.php');

/*
 * On login, retreive the nationbuilder person and membership objects and store as user metadata.
 */
function nb_on_login( $user_login, $user ) {

	$nbapi = new NationbuilderAPI;

	$person = array();
	
	// use the email as primary identifier rather than ID fields		
	$person['email'] = $user->user_email;		
	
	// update last login time, and retreive data
	$person['last_wordpress_login'] = date("Ymd");		
	$res = $nbapi->put('/api/v1/people/push',array('person' => $person));
	
	if (isset($res['person']['id'])) {
		
		// save person object ...
		update_user_meta( $user->ID, 'nb_person', $res['person']);

		// ... and id				
		$id = $res['person']['id'];
		update_user_meta( $user->ID, 'nb_id' , $id);					

		// get memberships	
		$res = $nbapi->get("/api/v1/people/{$id}/memberships");
		
		// seriously, Nationbuilder?		
		if (isset($res['result']['results'])) {
	
			// save memberships ...
			update_user_meta( $user->ID, 'nb_memberships', $res['result']['results']);
			
			// ... and a member flag	
			$m = 0;
			foreach ($res['result']['results'] as $membership) {
					
				if ($membership['status'] == 'active') {
					$m = 1;
					break;
				}	
			}
			
			// is member
			update_user_meta( $user->ID, 'nb_is_member' , $m);			
		}		
	}	
}

add_action('wp_login', 'nb_on_login', 10, 2);

/*
 * On registration, create record in Nationbuilder
 */
function nb_on_registration( $user_id ) {

	$user = get_userdata($user_id);
	$nbapi = new NationbuilderAPI;
	
	$name = get_user_meta($user_id, 'nickname', true);
	$parser = new NameParser($name);
		
	$person = array(
		'full_name' => $name
		, 'first_name' => $parser->getFirstName()
		, 'last_name' => $parser->getLastName()
		, 'email' => $user->user_email
		, 'last_wordpress_login' => date("Ymd")
		, 'parent_id' => NB_PARENT_ID
	);

	$res = $nbapi->put('/api/v1/people/push',array('person' => $person));
	
	// set first name and last name in Wordpress database
	update_user_meta($user_id, 'first_name', $parser->getFirstName());
	update_user_meta($user_id, 'last_name', $parser->getLastName());
	
	if (isset($res['person']['id']))
		$res = $nbapi->put("/api/v1/people/{$res['person']['id']}/taggings",array('tagging' => array('tag' => 'web-register')));		
		
}

add_action( 'user_register', 'nb_on_registration', 10, 1 );


function nb_get_user_meta($user_id=NULL, $key=NULL) {
	
	if (!is_numeric($user_id))
		$user_id = get_current_user_id();
	
	if ($user_id == 0 || !is_string($key))
		return false;
		
	$raw = get_user_meta($user_id, 'nb_person',true);

	if (!empty($raw))
	{
		$nb_person = maybe_unserialize($raw);
		
		if (isset($nb_person['id'])) {
			
			return $nb_person[$key];
			
		} else 
			return false;
	}
}

function nb_get_id($user_id=NULL) {
	
	if (!is_numeric($user_id))
		$user_id = get_current_user_id();
	
	if ($user_id == 0)
		return false;

	$id = get_user_meta($user_id, 'nb_id',true);
	
	if (is_numeric($id))
		return $id;
	else
		return false;	
}

function nb_is_member($user_id=NULL) {
	
	if (!is_numeric($user_id))
		$user_id = get_current_user_id();
	
	if ($user_id == 0)
		return false;

	$is_member = get_user_meta($user_id, 'nb_is_member',true);
	
	if (isset($is_member))
		return $is_member;
	else
		return false;	
}





?>