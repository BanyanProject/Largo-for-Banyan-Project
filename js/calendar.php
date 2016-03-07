<?php

require_once('../../../../wp-load.php');

global $wpdb;

if (isset($_GET['cid']) && is_numeric($_GET['cid'])) {

	$cid = sanitize_text_field($_GET['cid']);


	$querystr = "
	select distinct 
		p.*
		, sd.meta_value as `start_date`
		, ed.meta_value as `end_date`
		, lt.meta_value as `location_title`
		, a.meta_value as `address`
		, c.meta_value as `city`
	from wp_posts p 
		join wp_postmeta sd on p.ID = sd.post_id
			and sd.meta_key = 'start_date'
		join wp_postmeta ed on p.ID = ed.post_id
			and ed.meta_key = 'end_date' 
		join wp_postmeta lt on p.ID = lt.post_id
			and lt.meta_key = 'location_title'
		left join wp_postmeta a on p.ID = a.post_id
			and a.meta_key = 'address'
		left join wp_postmeta c on p.ID = c.post_id
			and c.meta_key = 'city'
		join wp_term_relationships r on p.ID = r.object_ID
	where p.post_status = 'publish'
		and ed.meta_value between date_sub(now(), interval 30 day) and date_add(now(), interval 180 day)
		and r.term_taxonomy_id = {$cid}
	";
	
} else {

	$querystr = "
	select distinct 
		p.*
		, sd.meta_value as `start_date`
		, ed.meta_value as `end_date`
		, lt.meta_value as `location_title`
		, a.meta_value as `address`
		, c.meta_value as `city`
	from wp_posts p 
		join wp_postmeta sd on p.ID = sd.post_id
			and sd.meta_key = 'start_date'
		join wp_postmeta ed on p.ID = ed.post_id
			and ed.meta_key = 'end_date' 
		join wp_postmeta lt on p.ID = lt.post_id
			and lt.meta_key = 'location_title'
		left join wp_postmeta a on p.ID = a.post_id
			and a.meta_key = 'address'
		left join wp_postmeta c on p.ID = c.post_id
			and c.meta_key = 'city'
	where p.post_status = 'publish'
		and ed.meta_value between date_sub(now(), interval 30 day) and date_add(now(), interval 180 day)
	";
}

$events = $wpdb->get_results($querystr, OBJECT);
 
?>

var $ = jQuery.noConflict();

$(document).ready(function() {	
	$('#wrap-calendar').clndr({
		template: $('#clndr-template').html()
		
		<?php if (isset($cid)) : ?>
		, daysOfTheWeek: ['S', 'M', 'T', 'W', 'T', 'F', 'S']
		<?php else : ?>
		, daysOfTheWeek: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
		<?php endif; ?>	
		, events: [		
<?php if ($events) : ?>
	<?php global $post; ?>
	<?php foreach ($events as $post): ?>
	<?php setup_postdata($post); ?>
				{ date: '<?php echo(date("Y-m-d",strtotime($post->start_date))); ?>', startTime: '<?php if ($post->start_date) echo(date("g:ia",strtotime($post->start_date))); ?>', endDate: '<?php if ($post->end_date) echo(date("Y-m-d",strtotime($post->end_date))); ?>', endTime: '<?php if ($post->end_date) echo(date("g:ia",strtotime($post->end_date))); ?>'
					, id: '<?php the_ID(); ?>', title: '<?php the_title(); ?>', url: '<?php echo(get_site_url() . '/event/' . get_the_ID()); ?>', excerpt: '<?php echo(esc_js($post->post_excerpt)); ?>', locationTitle: '<?php echo(esc_js($post->location_title)); ?>' },
	<?php endforeach; ?>
<?php endif; ?>
		]
	});
	

	$('[data-toggle="tooltip"]').tooltip();	
});

function formatMMd($datestr) {	
	return $.datepicker.formatDate("MM d", new Date($datestr));
}


