<?php 

if (is_front_page() && bp_adgroup_has_ads(1)) 
	bp_render_adgroup(1,'leaderboard');

/*
 * This code creates a page-specific ad group
 * - is_page() checks to see the current page is a page (rather than a post, category, etc)
 * - get_the_title() retrieves the page title
 * - bp_adgroup_has_ads() checks to see if an ad group has ads to display
 */

elseif (is_page() && get_the_title() == "Community Events Calendar" && bp_adgroup_has_ads(4))
	bp_render_adgroup(4,'leaderboard'); 


/*
 * This code render a category-specific ad group, based on the category_id and the adgroup_id
 * - is_category() checks to see the current page is a category page
 * - get_queried_object_id() checks the category_id
 * - bp_adgroup_has_ads() checks to see if an ad group has ads to display
 * - bp_render_adgroup() renders the ad group
 **/

elseif (is_category() && get_queried_object_id() == 19 && bp_adgroup_has_ads(5)) 
	bp_render_adgroup(5,'leaderboard');


/*
 * This code render a category-specific ad group for a news/blog/event post, based on the category_id and the adgroup_id
 * - is_sincle() checks to see the current page is a post
 * - bp_get_the_term_ids() lists the term_ids (category ids) for the page 
 * - bp_adgroup_has_ads() checks to see if an ad group has ads to display
 * - bp_render_adgroup() renders the ad group
 **/

elseif (is_single() && in_array(19, bp_get_the_term_ids()) && bp_adgroup_has_ads(5))
	bp_render_adgroup(5, 'leaderboard');


?>