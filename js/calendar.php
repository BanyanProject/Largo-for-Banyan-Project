<?php

require_once('../../../../wp-load.php');

global $wpdb;

$querystr = "
select distinct 
	p.*
	, sd.meta_value as `start_date`
	, st.meta_value as `start_time`
	, ed.meta_value as `end_date`
	, et.meta_value as `end_time`
from wp_posts p 
	join wp_postmeta sd on p.ID = sd.post_id
	join wp_postmeta st on p.ID = st.post_id
	join wp_postmeta ed on p.ID = ed.post_id
	join wp_postmeta et on p.ID = et.post_id
where p.post_status = 'publish'
	and sd.meta_key = 'start_date'
	and sd.meta_value between date_sub(now(), interval 30 day) and date_add(now(), interval 180 day)
	and st.meta_key = 'start_time'
	and ed.meta_key = 'end_date' 
	and et.meta_key = 'end_time'
";

$events = $wpdb->get_results($querystr, OBJECT);
 
?>

var $ = jQuery.noConflict();

$(document).ready(function() {	
	$('#wrap-calendar').clndr({
		template: $('#clndr-template').html()
		, daysOfTheWeek: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
		, events: [		
<?php if ($events) : ?>
	<?php global $post; ?>
	<?php foreach ($events as $post): ?>
	<?php setup_postdata($post); ?>
				{ date: '<?php echo(date("Y-m-d",strtotime($post->start_date))); ?>', startTime: '<?php if ($post->start_time) echo(date("g:ia",strtotime($post->start_time))); ?>', endDate: '<?php if ($post->end_date) echo(date("Y-m-d",strtotime($post->end_date))); ?>', endTime: '<?php if ($post->end_time) echo(date("g:ia",strtotime($post->end_time))); ?>'
					, id: '<?php the_ID(); ?>', title: '<?php the_title(); ?>', url: '<?php echo(get_site_url() . '/event/' . get_the_ID()); ?>', excerpt: '<?php echo(esc_js($post->post_excerpt)); ?>' },
	<?php endforeach; ?>
<?php endif; ?>
		]
	});
});

function formatMMd($datestr) {	
	return $.datepicker.formatDate("MM d", new Date($datestr));
}


