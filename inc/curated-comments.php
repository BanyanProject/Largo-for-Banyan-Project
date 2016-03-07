<?php

function save_bp_comments( $cid, $approved ) {

    if ($approved) {
		
		global $wpdb;
		
		$data = array(
			'comment_id' => $cid
			, 'term_id' =>  500
		);
		
		$wpdb->insert('bp_comments', $data);
    }
}

add_action( 'comment_post', 'save_bp_comments', 10, 2 );




?>