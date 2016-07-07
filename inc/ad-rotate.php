<?php

/* AdRotate-related functions */

/**
 * Determines if an adgroup is loaded with ads
 */ 
function bp_adgroup_has_ads($group) {
	
	if (strstr(adrotate_group($group),'<div'))
		return true;
	else
		return false;
} 
 
/**
 * Renders AdGroup 
 */
function bp_render_adgroup($group,$type,$disclosure=true) {
		
	if ($disclosure) :	

		$adwrap = "
			<div class=\"ad-outerwrap ad-outerwrap-{$type}\">
				<div class=\"ad-innerwrap ad-innerwrap-{$type}\">
					<div>Advertisement</div>
					<span>" . adrotate_group($group) . "</span>
				</div>			
			</div>
		";
	
	else :
		
		$adwrap = "
			<div class=\"ad-outerwrap ad-outerwrap-{$type}\">
				<div class=\"ad-innerwrap ad-innerwrap-{$type}\">
					<span>" . adrotate_group($group) . "</span>
				</div>			
			</div>
		";
			
	endif;	
	
	switch ($type) {
		
		case 'sidebar' : 
		
			echo "<aside class=\"widget clearfix\"><h3 class=\"widgettitle\">Our Sponsors</h3>";
			echo($adwrap);
			echo("</aside>");
			return;
		
		case 'leaderboard' :
		default :
			echo($adwrap);
			return;
	}	
} 
 
/**
 * Replaces largo_excerpt().  Does not apply the_content() filter.  Required for compatibility with AdRotate's post injection advertisements.
 */
function bp_simple_excerpt( $the_post=null, $sentence_count = 5, $use_more = false, $more_link = '', $echo = true, $strip_tags = true, $strip_shortcodes = true ) {

		$the_post = get_post($the_post); // Normalize it into a post object

		if (!empty($the_post->post_excerpt)) {
			// if a post has a custom excerpt set, we'll use that
			$content = apply_filters('get_the_excerpt', $the_post->post_excerpt);
		} else if (is_home() && preg_match('/<!--more(.*?)?-->/', $the_post->post_content, $matches) > 0) {
			// if we're on the homepage and the post has a more tag, use that
			$parts = explode($matches[0], $the_post->post_content, 2);
			$content = $parts[0];
		} else {
			// otherwise we'll just do our best and make the prettiest excerpt we can muster
			$content = largo_trim_sentences($the_post->post_content, $sentence_count);
		}

		// optionally strip shortcodes and html
		$output = '';
		if ( $strip_tags && $strip_shortcodes )
			$output .= strip_tags( strip_shortcodes ( $content ) );
		else if ( $strip_tags )
			$output .= strip_tags( $content );
		else if ( $strip_shortcodes )
			$output .= strip_shortcodes( $content );
		else
			$output .= $content;

		if ( $echo )
			echo $output;

		return $output;
}
