<?php

if ((is_single() || is_singular()) && !largo_is_sidebar_required())
	return;

$showey_hidey_class = (of_get_option('showey-hidey'))? 'showey-hidey':'';
$span_class = bp_sidebar_span_class();

do_action('largo_before_sidebar'); ?>
<aside id="sidebar" class="<?php echo $span_class; ?> nocontent">
	<?php do_action('largo_before_sidebar_content'); ?>
	<div class="widget-area <?php echo $showey_hidey_class ?>" role="complementary">
		
		<?php
			get_template_part('partials/sidebar-ad-zone');
		
			do_action('largo_before_sidebar_widgets');

			if (is_archive() && !is_date()) {  	# category pages
				
				if (bp_category_has_events()) 	# with events, show category sidebar with event calendar						
					get_template_part('partials/sidebar', 'archive');
				else 							# without events show main sidebar
					get_template_part('partials/sidebar');
								
			} else if ((is_single() || is_singular())) {
				
				if (is_page()) {
					get_template_part('partials/sidebar'); # pages show the main sidebar
				} else				
					get_template_part('partials/sidebar', 'single'); # posts show the category sidebar
			} else
				get_template_part('partials/sidebar');

			do_action('largo_after_sidebar_widgets');
		?>
	</div>
	<?php do_action('largo_after_sidebar_content'); ?>
</aside>
<?php do_action('largo_after_sidebar');
