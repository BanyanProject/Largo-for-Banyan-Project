<?php

if ($featured_post) {
	$post = $featured_post;	
	unset($featured_post);
}

get_template_part('partials/content', 'teaser');

?>
