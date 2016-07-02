<?php

include_once get_template_directory() . '/homepages/homepage-class.php';

class HomepageLead extends Homepage {
	function __construct($options=array()) {
		$defaults = array(
			'name' => __('Three-column Layout with Lead Article', 'largo'),
			'description' => __('A three-column homepage layout, featuring news articles in the left-hand column and events in the middle column, and a lead article spanning two columns.', 'Largo-BP'),
			'template' => get_stylesheet_directory() . '/homepages/templates/homepage-lead.php',
			'prominenceTerms' 	=> array(
				array(
					'name' 			=> __( 'Homepage Top Story', 'largo' ),
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
