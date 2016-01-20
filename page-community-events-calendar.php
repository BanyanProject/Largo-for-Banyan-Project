<?php
/**
 * Description: Calendar Page
 */
    
wp_enqueue_script(
	'calendar',
	'/wp-content/themes/Largo-BP/js/calendar.php',
	array('jquery'),
	'0.1',
	true
);
  
global $wpdb;

// Query for upcoming events

$querystr = "
select distinct 
	p.*
	, sd.meta_value as `start_date`
	, st.meta_value as `start_time`
	, ed.meta_value as `end_date`
	, et.meta_value as `end_time`
	, lt.meta_value as `location_title`
from wp_posts p 
	join wp_postmeta sd on p.ID = sd.post_id
	join wp_postmeta st on p.ID = st.post_id
	join wp_postmeta ed on p.ID = ed.post_id
	join wp_postmeta et on p.ID = et.post_id
	join wp_postmeta lt on p.ID = lt.post_id
where p.post_status = 'publish'
	and sd.meta_key = 'start_date'
	and st.meta_key = 'start_time'
	and ed.meta_key = 'end_date' 
	and ed.meta_value between now() and date_add(now(), interval 7 day)
	and et.meta_key = 'end_time'
	and lt.meta_key = 'location_title'
order by `start_date`
limit 12
";

$events = $wpdb->get_results($querystr, OBJECT);
				 
global $shown_ids;

get_header();

?>

<div id="content" class="col-md-12" role="main">
	
	<?php
		while ( have_posts() ) : the_post();
			
			$shown_ids[] = get_the_ID();
			
			?>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php edit_post_link(__('Edit This Page', 'largo'), '<h5 class="byline"><span class="edit-link">', '</span></h5>'); ?>
				</header><!-- .entry-header -->
			
				<section class="entry-content">
					<?php the_content(); ?>
					
					<h4>Upcoming Events</h4>

					<div class="row wrap-upcoming-events">
					<?php foreach ($events as $event) : ?>
					
						<div class="col-sm-6 col-md-4">
							<a class="thumbnail clearfix" href="/event/<?php echo($event->ID); ?>">
																
								<h5><?php echo($event->post_title); ?></h5>
								
								<div class="teaser-dateline">
									<span class="upcoming-start-date"><?php echo(date("F j",strtotime($event->start_date))); ?></span>								
									<?php if ($event->start_date != $event->end_date) : ?>
									- <span class="upcoming-end-date"><?php echo(date("F j",strtotime($event->end_date))); ?></span>								
									<?php else: ?>
									<span class="upcoming-start-time"><?php echo(date("g:ia",strtotime($event->start_time))); ?></span>
									<?php if ($event->start_time != $event->end_time && $event->end_time != NULL) : ?>
									- <span class="upcoming-end-time"><?php echo(date("g:ia",strtotime($event->end_time))); ?></span>
									<?php endif; endif; ?>																		
								</div>
								
								<div class="teaser-event-location">
									<?php echo($event->location_title); ?>
								</div>		
								
								<?php echo(get_the_post_thumbnail($event->ID,'thumbnail',array('class' => 'alignright'))); ?>
								
								<?php if ($event->post_excerpt) : ?>						
								<p class="clearfix"><?php echo($event->post_excerpt); ?></p>
								<?php endif; ?>
								
								<p class="teaser-postscript">Click for more information.</p>
							</a>
						</div>
						
					<?php endforeach; ?>
					</div>
						
					
					<div id="wrap-calendar">						
					</div>

					<script type="text/template" id="clndr-template">
							<div class="clndr-controls">
								<div class="clndr-control-button">
									<span class="clndr-previous-button">&lsaquo;</span>
								</div>
								<div class="month"><%= month %> <%= year %></div>
								<div class="clndr-control-button">
									<span class="clndr-next-button">&rsaquo;</span>
								</div>
							</div>
							<div class="clndr-grid">
								<div class="days-of-the-week">
								<% _.each(daysOfTheWeek, function(day) { %>
										<div class="header-day"><%= day %></div>									
								<% }); %>
								</div>

									<% for(var i = 0; i < numberOfRows; i++){ %>
										<div class="week">											
											<% for(var j = 0; j < 7; j++){ var d = j + i * 7; %>						
												<div class="<%= days[d].classes %>">
													<span class="day-number clearfix"><%= days[d].day %></span>
													
													<% _.each(days[d].events, function(event) { %>
													<div class="wrap-clndr-event">	
														<a class="clndr-event" href="<%= event.url %>">
															<span class="clndr-start-time"><%= event.startTime %></span>
															<% if (event.endTime != '' && event.endTime != event.startTime && event.date == event.endDate) { %>
																- <span class="clndr-end-time"><%= event.endTime %></span>	
															<% } %> 
															<span class="clndr-title"></span><%= event.title %></span>
														</a>
	
														<% if (event.date != event.endDate) { %>
														<span class="clndr-multiday">This event runs until <%= formatMMd(event.endDate) %></span>
														<% } %>
													</div>													
													<% }); %>													
												</div>
											<% } %>										
										</div>
									<% } %>

							</div>
					</script>

										
				</section><!-- .entry-content -->
			</article><!-- #post-<?php the_ID(); ?> -->
		
			<?php
		
			if ( is_active_sidebar( 'article-bottom' ) ) {

				do_action( 'largo_before_post_bottom_widget_area' );

				echo '<div class="article-bottom nocontent">';
				dynamic_sidebar( 'article-bottom' );
				echo '</div>';

				do_action( 'largo_after_post_bottom_widget_area' );

			}

		endwhile;
	?>
</div>

<?php do_action( 'largo_after_content' ); ?>

<?php get_footer();
