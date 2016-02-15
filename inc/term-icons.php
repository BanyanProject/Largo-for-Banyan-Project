<?php
/**
 * Creates $largo['term-icons'] using the Largo_Term_Icons class defined herein
 *
 * Display the fields for selecting icons for terms in the "post-type" taxonomy
 *
 * @global $largo
 *
 */
 
require_once(ABSPATH . 'wp-content/themes/Largo/inc/term-icons.php'); 
 
class Banyan_Project_Term_Icons extends Largo_Term_Icons {

	function __construct() {
		global $wp_filesystem;

		if (empty($wp_filesystem)) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			WP_Filesystem();
		}

		add_action( 'edit_category_form_fields', array( $this, 'display_fields' ) );
		add_action( 'edit_tag_form_fields', array( $this, 'display_fields' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
		add_action( 'edit_terms', array( $this, 'edit_terms' ) );
		add_action( 'create_term', array( $this, 'edit_terms' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}


	/**
	 * Register the taxonomy post-type
	 */
	function register_taxonomy() {
		register_taxonomy( 'post-type', array( 'post' ), array(
			'label' => __( 'Post Types', 'largo' ),
			'labels' => array(
				'name' => __( 'Post Types Banyan', 'largo' ),
				'singular_name' => __( 'Post Type', 'largo' ),
				'all_items' => __( 'All Post Types', 'largo' ),
				'edit_item' => __( 'Edit Post Type', 'largo' ),
				'update_item' => __( 'Update Post Type', 'largo' ),
				'view_item' => __( 'View Post Type', 'largo' ),
				'add_new_item' => __( 'Add New Post Type', 'largo' ),
				'new_item_name' => __( 'New Post Type Name', 'largo' ),
				'search_items' => __( 'Search Post Type'),
			),
			'public' => true,
			'show_admin_column' => true,
			'hierarchical' => true,
		) );
	}
}

$largo['term-icons'] = new Banyan_Project_Term_Icons();
