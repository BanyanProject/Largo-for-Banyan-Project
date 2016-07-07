<?php

/**
 * TODO: Add complete database schema
 */

global $largo_bp_db_version;
$largo_bp_db_version = '1.0';
 
function largo_bp_install() {
		
	global $wpdb, $largo_bp_db_version;

	$form_prefix = 'frm_';

	$submission = $form_prefix . "submission";
	
	// forms
	$contact = $form_prefix . "contact";
	$donate = $form_prefix . "donate";
	$email_newsletter = $form_prefix . "email_newsletter";
	$feedback = $form_prefix . "feedback";
	$pledge = $form_prefix . "founding_membership_pledge";
	$integration_cron = $form_prefix . "integration_cron";
	$membership = $form_prefix . "membership";
	$volunteer = $form_prefix . "volunteer";

	$charsetCollate = $wpdb->get_charset_collate();

	$sql = "
		CREATE TABLE {$submission} (
		  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  form varchar(63) NOT NULL,
		  ip_address varchar(15) DEFAULT NULL,
		  user_agent varchar(255) DEFAULT NULL,
		  load_timestamp bigint(20) unsigned NOT NULL,
		  submit_timestamp bigint(20) unsigned NOT NULL,
		  session_token char(64) DEFAULT NULL,
		  user_id bigint(20) unsigned DEFAULT NULL,
		  nationbuilder_slug varchar(63) DEFAULT NULL,
		  nationbuilder_id bigint(20) unsigned DEFAULT NULL,
		  is_member tinyint(1) unsigned DEFAULT NULL,
		  utma varchar(255) DEFAULT NULL,
		  utmb varchar(255) DEFAULT NULL,
		  utmz varchar(255) DEFAULT NULL,
		  utmv varchar(255) DEFAULT NULL,
		  utmx varchar(255) DEFAULT NULL,
		  validate tinyint(1) unsigned NOT NULL DEFAULT '0',
		  validate_error_msg text COLLATE utf8_unicode_ci,
		  mandrill_admin tinyint(1) unsigned DEFAULT NULL,
		  mandrill_admin_status varchar(15) DEFAULT NULL,
		  mandrill_admin_reject_reason varchar(15) DEFAULT NULL,
		  mandrill_admin_exception varchar(63) DEFAULT NULL,
		  mandrill_admin_message varchar(255) DEFAULT NULL,
		  mandrill_admin__id varchar(63) DEFAULT NULL,
		  mandrill_user tinyint(1) unsigned DEFAULT NULL,
		  mandrill_user_status varchar(15) DEFAULT NULL,
		  mandrill_user_reject_reason varchar(15) DEFAULT NULL,
		  mandrill_user_exception varchar(63) DEFAULT NULL,
		  mandrill_user_message varchar(255) DEFAULT NULL,
		  mandrill_user__id varchar(63) DEFAULT NULL,
		  session_id char(40) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY ip_address (ip_address),
		  KEY created (load_timestamp),
		  KEY utma (utma),
		  KEY utmb (utmb),
		  KEY utmz (utmz),
		  KEY utmv (utmv),
		  KEY utmx (utmx),
		  KEY form (form),
		  KEY session_id (session_token) USING BTREE,
		  KEY submit_timestamp (submit_timestamp),
		  KEY user_id (user_id),
		  KEY nationbuilder_id (nationbuilder_id)
		) {$charsetCollate};
		
		CREATE TABLE {$contact} (
		  id bigint(20) unsigned NOT NULL,
		  full_name varchar(63) NOT NULL,
		  email varchar(127) NOT NULL,
		  type varchar(31) DEFAULT NULL,
		  message text NOT NULL,
		  email_signup tinyint(1) DEFAULT '0',
		  nationbuilder tinyint(1) DEFAULT '0',
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY email (email)
		) {$charsetCollate};
		
		CREATE TABLE {$donate} (
		  id bigint(20) unsigned NOT NULL,
		  full_name varchar(63) NOT NULL,
		  address varchar(127) DEFAULT NULL,
		  city varchar(63) DEFAULT NULL,
		  state_province char(2) CHARACTER SET latin1 DEFAULT NULL,
		  zip_postal varchar(10) DEFAULT NULL,
		  country char(2) NOT NULL,
		  email varchar(127) NOT NULL,
		  phone varchar(31) DEFAULT NULL,
		  amount double(7,2) unsigned NOT NULL,
		  recurring enum('annual','monthly','non-recurring') DEFAULT NULL,
		  cc char(4) NOT NULL,
		  exp_month char(2) NOT NULL,
		  exp_year char(4) NOT NULL,
		  email_signup tinyint(1) NOT NULL DEFAULT '0',
		  authorize tinyint(1) NOT NULL DEFAULT '0',
		  authorize_error_code varchar(2) DEFAULT NULL,
		  authorize_error_msg varchar(255) DEFAULT NULL,
		  authorize_subscription_id bigint(20) unsigned DEFAULT NULL,
		  nationbuiilder tinyint(1) unsigned DEFAULT NULL,
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY email (email)
		) {$charsetCollate};
		
		CREATE TABLE {$email_newsletter} (
		  id bigint(20) unsigned NOT NULL,
		  full_name varchar(63) DEFAULT NULL,
		  email varchar(127) NOT NULL,
		  nationbuilder tinyint(1) unsigned DEFAULT NULL,
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY email (email)
		) {$charsetCollate};
		
		CREATE TABLE {$feedback} (
		  id bigint(20) unsigned NOT NULL,
		  post_id bigint(20) unsigned NOT NULL,
		  relevant_respectful tinyint(1) DEFAULT NULL,
		  feedback text,
		  status enum('private','public') DEFAULT NULL,
		  nationbuilder tinyint(1) unsigned NOT NULL DEFAULT '0',
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id)
		) {$charsetCollate};
		
		CREATE TABLE {$pledge} (
		  id bigint(20) unsigned NOT NULL,
		  full_name varchar(63) NOT NULL,
		  address varchar(127) DEFAULT NULL,
		  city varchar(63) DEFAULT NULL,
		  state_province char(2) DEFAULT NULL,
		  zip_postal varchar(10) DEFAULT NULL,
		  country char(2) NOT NULL,
		  email varchar(127) NOT NULL,
		  phone varchar(31) DEFAULT NULL,
		  email_signup tinyint(1) unsigned NOT NULL DEFAULT '0',
		  nationbuilder tinyint(1) unsigned DEFAULT NULL,
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY email (email)
		) {$charsetCollate};
		
		CREATE TABLE {$integration_cron} (
		  id int(10) unsigned NOT NULL AUTO_INCREMENT,
		  cron varchar(32) CHARACTER SET utf8 NOT NULL,
		  timestamp int(10) unsigned NOT NULL,
		  count int(10) unsigned NOT NULL DEFAULT '0',
		  success int(10) unsigned NOT NULL DEFAULT '0',
		  errors int(10) unsigned NOT NULL DEFAULT '0',
		  log text CHARACTER SET utf8,
		  execution_time bigint(20) unsigned NOT NULL,
		  memory_usage bigint(20) unsigned NOT NULL,
		  PRIMARY KEY  (id),
		  KEY cron (cron),
		  KEY timestamp (timestamp)
		) {$charsetCollate};
		
		CREATE TABLE {$membership} (
		  id bigint(20) unsigned NOT NULL,
		  type varchar(15) NOT NULL DEFAULT 'founding',
		  recurring enum('annual','monthly','non-recurring') NOT NULL,
		  additional_donation tinyint(1) unsigned NOT NULL DEFAULT '0',
		  additional_amount double(7,2) unsigned DEFAULT NULL,
		  full_name varchar(63) NOT NULL,
		  address varchar(127) DEFAULT NULL,
		  city varchar(63) DEFAULT NULL,
		  state_province char(2) DEFAULT NULL,
		  zip_postal varchar(10) DEFAULT NULL,
		  country char(2) NOT NULL,
		  email varchar(127) NOT NULL,
		  phone varchar(31) DEFAULT NULL,
		  cc char(4) NOT NULL,
		  exp_month char(2) NOT NULL,
		  exp_year char(2) NOT NULL,
		  email_signup tinyint(1) unsigned NOT NULL DEFAULT '0',
		  total double(7,2) unsigned DEFAULT NULL,
		  authorize tinyint(1) NOT NULL DEFAULT '0',
		  authorize_subscription_id bigint(21) unsigned DEFAULT NULL,
		  authorize_error_code varchar(255) DEFAULT NULL,
		  authorize_error_msg varchar(255) DEFAULT NULL,
		  nationbuilder tinyint(1) unsigned DEFAULT NULL,
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY email (email)
		) {$charsetCollate};
		
		CREATE TABLE {$volunteer} (
		  id int(11) unsigned NOT NULL,
		  full_name varchar(63) NOT NULL,
		  email varchar(127) NOT NULL,
		  phone varchar(31) DEFAULT NULL,
		  address varchar(127) DEFAULT NULL,
		  city varchar(63) DEFAULT NULL,
		  state_province char(2) DEFAULT NULL,
		  zip_postal varchar(10) DEFAULT NULL,
		  country char(2) DEFAULT NULL,
		  skills_journalism tinyint(1) unsigned NOT NULL DEFAULT '0',
		  skills_organizing tinyint(1) unsigned NOT NULL DEFAULT '0',
		  skills_graphic_design tinyint(1) unsigned NOT NULL DEFAULT '0',
		  skills_photo tinyint(1) unsigned NOT NULL DEFAULT '0',
		  skills_video tinyint(1) unsigned NOT NULL DEFAULT '0',
		  skills_web tinyint(1) unsigned NOT NULL DEFAULT '0',
		  interests_write tinyint(1) unsigned NOT NULL DEFAULT '0',
		  interests_volunteer tinyint(1) unsigned NOT NULL DEFAULT '0',
		  interests_event tinyint(1) unsigned NOT NULL DEFAULT '0',
		  message text,
		  email_signup tinyint(1) unsigned NOT NULL DEFAULT '0',
		  nationbuilder tinyint(1) unsigned NOT NULL DEFAULT '0',
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY  (id),
		  KEY email (email)
		) {$charsetCollate};
		
		CREATE TABLE bp_comments (
		  comment_id bigint(20) unsigned NOT NULL,
		  term_id bigint(20) NOT NULL DEFAULT '-1',
		  comment_source enum('website','facebook','twitter') DEFAULT 'website',
		  member tinyint(1) unsigned DEFAULT '0',
		  comment_url varchar(255) DEFAULT NULL,
		  twitter_username varchar(255) DEFAULT NULL,
		  tweet_id bigint(20) unsigned DEFAULT NULL,
		  upvotes bigint(20) unsigned NOT NULL DEFAULT '0',
		  upvotes_users text COLLATE utf8_unicode_ci,
		  alerts bigint(20) unsigned NOT NULL DEFAULT '0',
		  alerts_users text COLLATE utf8_unicode_ci,
		  promoted tinyint(1) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY  (comment_id),
		  KEY comment_id (comment_id),
		  KEY term_id (term_id)
		) {$charsetCollate};
		
		CREATE TABLE click_clickstream (
		  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  ip_address varchar(15) DEFAULT NULL,
		  user_agent varchar(255) DEFAULT NULL,
		  timestamp bigint(20) unsigned NOT NULL,
		  session_token char(64) DEFAULT NULL,
		  user_id bigint(20) unsigned DEFAULT NULL,
		  nationbuilder_slug varchar(63) DEFAULT NULL,
		  nationbuilder_id bigint(20) unsigned DEFAULT NULL,
		  is_member tinyint(1) unsigned DEFAULT NULL,
		  utma varchar(255) DEFAULT NULL,
		  utmb varchar(255) DEFAULT NULL,
		  utmz varchar(255) DEFAULT NULL,
		  utmv varchar(255) DEFAULT NULL,
		  utmx varchar(255) DEFAULT NULL,
		  request varchar(255) NOT NULL,
		  matched_rule varchar(255) NOT NULL,
		  matched_query varchar(255) NOT NULL,
		  PRIMARY KEY  (id),
		  KEY ip_address (ip_address),
		  KEY created (timestamp),
		  KEY utma (utma),
		  KEY utmb (utmb),
		  KEY utmz (utmz),
		  KEY utmv (utmv),
		  KEY utmx (utmx),
		  KEY session_id (session_token) USING BTREE,
		  KEY user_id (user_id),
		  KEY nationbuilder_id (nationbuilder_id)
		) {$charsetCollate};
		

			
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
		
	update_option('largo_bp_db_version', $largo_bp_db_version);
}

add_action("after_switch_theme", "largo_bp_install", 10 ,  2);  

function largo_bp_update_db_check() {
    		
    global $largo_bp_db_version;	
    
    if ( get_site_option( 'largo_bp_db_version' ) != $largo_bp_db_version ) {
        largo_bp_install();
    }
}

add_action( 'plugins_loaded', 'largo_bp_update_db_check' );

?>
