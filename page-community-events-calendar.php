<?php
/**
 * Description: Calendar Page
 */

if (isset($_GET['cid']) && is_numeric($_GET['cid'])) {

	$cid = sanitize_text_field($_GET['cid']);
	
	wp_enqueue_script(
		'calendar',
		'/wp-content/themes/Largo-for-Banyan-Project/js/calendar.php?cid='.$cid,
		array('jquery'),
		'0.1',
		true
	);

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
		and ed.meta_value between now() and date_add(now(), interval 60 day)
		and r.term_taxonomy_id = {$cid}
	order by `start_date`
	limit 40
	";

} else {
		
	$cid = null;
		
	wp_enqueue_script(
		'calendar',
		'/wp-content/themes/Largo-for-Banyan-Project/js/calendar.php?',
		array('jquery'),
		'0.1',
		true
	);
	
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
		and ed.meta_value between now() and date_add(now(), interval 60 day)
	order by `start_date`
	limit 40
	";

} 
       
global $wpdb;

// Query for upcoming events

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

				<div class="container community-events-calendar">
					
					<div class="row wrap-calendar-intro ">
						<div class="col-md-8 col-md-offset-2">
							<?php get_template_part('partials/social', 'horizontal'); ?>
							
							<?php the_content(); ?>
											
						</div>
						
						<div class="col-md-4 col-md-offset-4">
							<form method="get" id="category-form">
								<label>Select events to show</label>
								<?php wp_dropdown_categories(array(
									'show_option_none'   => 'All Events',
									'option_none_value'  => '',
									'orderby'            => 'NAME', 
									'order'              => 'ASC',
									'show_count'         => false,
									'hide_empty'         => false, 
									'echo'               => true,
									'hierarchical'       => true, 
									'name'               => 'cid',
									'depth'              => 1,
									'taxonomy'           => 'category',
									'hide_if_empty'      => false,
									'selected'			 => $cid,
									'id'				 => 'category-select'
								)); ?>
								
							</form>
						</div>
					</div>

					<div class="row wrap-tabs">
						
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#upcoming-events" aria-controls="upcoming-events" role="tab" data-toggle="tab">Events Listing</a></li>
							<li role="presentation"><a href="#monthly-calendar" aria-controls="monthly-calendar" role="tab" data-toggle="tab">Monthly Calendar</a></li>
						</ul>
					
					</div>	

					<div class="row tab-content">
						<div role="tabpanel" class="tab-pane active" id="upcoming-events">
							<div class="col-md-8 col-md-offset-2 wrap-upcoming-events">	
								
								<?php 
									$d = null;
								
									foreach ($events as $event) : 
										if ($d != date('l F j, Y',strtotime($event->start_date))) {
											$d = date('l F j, Y',strtotime($event->start_date));
											echo('<h4 class="event-date">'. $d .'</h4>');
										}
								?>
									
									<div class="wrap-event row">
											
											
										<div class="col-xs-3">
											<div class="teaser-dateline">
												<span class="upcoming-start-time"><?php echo(date("g:ia",strtotime($event->start_date))); ?></span>
												<?php if ($event->start_date != $event->end_date && $event->end_date != NULL) : ?>
												- <span class="upcoming-end-time"><?php echo(date("g:ia",strtotime($event->end_date))); ?></span>
												<?php endif; ?>																		
											</div>
										</div>
											
										<div class="col-xs-9" >
											<h5 class="upcoming-title"><a href="/event/<?php echo($event->ID); ?>" data-toggle="tooltip" data-placement="right" title="<?php echo($event->post_excerpt); ?>"><?php echo($event->post_title); ?></a></h5>
											
											<div class="teaser-event-location">
												<?php 
													echo($event->location_title);
													if (isset($event->address)) echo(", ". $event->address);
													if (isset($event->city)) echo(", ". $event->city);
												?>
											
											</div>	
																							
										</div>
	
									</div>
										
																
								<?php endforeach; ?>
							</div>
						</div>
						<div role="tabpanel" class="tab-pane" id="monthly-calendar">
							<div class="wrap-monthly-calendar">
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
																<a class="clndr-event" href="<%= event.url %>" data-toggle="tooltip" data-placement="right" title="<%= event.excerpt %>">
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
							</div>				
						</div>
					</div>
				</div>
														
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
