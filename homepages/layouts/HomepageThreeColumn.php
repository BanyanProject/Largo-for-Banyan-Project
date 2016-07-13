<?php

include_once get_template_directory() . '/homepages/homepage-class.php';

class HomepageThreeColumn extends Homepage {
	function __construct($options=array()) {
		$defaults = array(
			'name' => __('Three-column Layout', 'largo'),
			'description' => __('A three column homepage layout, featuring news articles in the left-hand column and events in the middle column.', 'Largo-BP'),
			'template' => get_stylesheet_directory() . '/homepages/templates/homepage-three-column.php',
			'prominenceTerms' 	=> array(
				array(
					'name' 			=> __( 'Top Story', 'largo' ),
					'description' 	=> __( 'Add this label to a post to make it the top story on the homepage', 'largo' ),
					'slug' 			=> 'top-story'
				)
			),
			'sidebars' => array(
				__( 'Homepage Middle Column', 'largo' )
			)
		);
		$options = array_merge($defaults, $options);
		parent::__construct($options);
	}

}
