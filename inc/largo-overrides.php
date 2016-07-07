<?php

/* Largo Override Functions */

/**
 * Overrides Largo get_post_template, which doesn't allow child theme template overrides
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
 
/**
 *  Replaces largo_sidebar_span_class.  Updated for Bootstrap 3 and Banyan Project sidebar classes.
 */
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
