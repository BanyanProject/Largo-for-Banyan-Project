<?php

/**
 * TODO: Add complete database schema
 */

global $largo_bp_db_version;
$largo_bp_db_version = '0.1';
 
function largo_bp_install() {
		
	global $wpdb, $largo_bp_db_version;

	$prefix = 'frm_';

	$submission = $prefix . "submission";
	
	// forms
	$contact = $prefix . "contact";

	$charsetCollate = $wpdb->get_charset_collate();

	$sql = "
		CREATE TABLE {$submission} (
		  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  form varchar(63) NOT NULL,
		  ip_address varchar(15) DEFAULT NULL,
		  user_agent varchar(255) DEFAULT NULL,
		  load_timestamp bigint(20) unsigned NOT NULL,
		  submit_timestamp bigint(20) unsigned NOT NULL,
		  session_id char(40) DEFAULT NULL,
		  user_id bigint(20) unsigned DEFAULT NULL,
		  nationbuilder_id bigint(20) unsigned DEFAULT NULL,
		  is_member tinyint(1) unsigned DEFAULT NULL,
		  utma varchar(255) DEFAULT NULL,
		  utmb varchar(255) DEFAULT NULL,
		  utmz varchar(255) DEFAULT NULL,
		  utmv varchar(255) DEFAULT NULL,
		  utmx varchar(255) DEFAULT NULL,
		  validate tinyint(1) unsigned NOT NULL,
		  validate_error_msg text,
		  PRIMARY KEY  (id),
		  KEY ip_address (ip_address),
		  KEY created (load_timestamp),
		  KEY utma (utma),
		  KEY utmb (utmb),
		  KEY utmz (utmz),
		  KEY utmv (utmv),
		  KEY utmx (utmx),
		  KEY form (form),
		  KEY session_id (session_id) USING BTREE,
		  KEY submit_timestamp (submit_timestamp),
		  KEY user_id (user_id),
		  KEY nationbuilder_id (nationbuilder_id)
		) {$charsetCollate};
		
		CREATE TABLE {$contact} (
		  id bigint(20) unsigned NOT NULL,
		  name varchar(63) NOT NULL,
		  email varchar(127) NOT NULL,
		  type varchar(31) DEFAULT NULL,
		  message text NOT NULL,
		  email_signup tinyint(1) DEFAULT '0',
		  mandrill tinyint(1) unsigned NOT NULL DEFAULT '0',
		  mandrill_error_code varchar(255) DEFAULT NULL,
		  mandrill_error_msg varchar(255) DEFAULT NULL,
		  nationbuilder tinyint(1) DEFAULT '0',
		  nationbuilder_error_msg varchar(255) DEFAULT NULL,
		  PRIMARY KEY (id),
		  KEY email (email)
		) {$charsetCollate};
		
			
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
		
	//add_option('largo_bp_db_version', $largo_bp_db_version);
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
