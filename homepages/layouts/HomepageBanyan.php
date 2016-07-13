<?php

include_once get_template_directory() . '/homepages/homepage-class.php';

class HomepageBanyan extends Homepage {
	function __construct($options=array()) {
		$defaults = array(
			'name' => __('BanyanProjec.coop Layout', 'largo'),
			'description' => __('A three column homepage layout for Banyan Project', 'Largo-BP'),
			'template' => get_stylesheet_directory() . '/homepages/templates/homepage-banyan.php',
			'sidebars' => array(
				__( 'Homepage Middle Column', 'largo' )
			)
		);
		$options = array_merge($defaults, $options);
		parent::__construct($options);
	}

}
