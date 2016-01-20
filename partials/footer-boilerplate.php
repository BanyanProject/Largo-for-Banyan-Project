<div id="boilerplate">
	<div class="row clearfix">
		<div class="col-md-12">
			<ul id="footer-social" class="social-icons">
				<?php largo_social_links(); ?>
			</ul>
		</div>

	</div>

	<div class="row clearfix">
		<div class="col-md-12">
			<div class="footer-bottom clearfix">

				<!-- If you enjoy this theme and use it on a production site we would appreciate it if you would leave the credit in place. Thanks :) -->
				<p class="footer-credit"><?php largo_copyright_message(); ?></p>
				<p class="footer-credit"><?php printf( __('This site built with <a href="%s">Project Largo</a> from <a href="%s">INN</a> and proudly powered by <a href="%s" rel="nofollow">WordPress</a>.', 'largo'),
						'http://largoproject.org',
						'http://inn.org',
						'http://wordpress.org'
					 );
				?></p>
			</div>
		</div>
	</div>

	<div class="row clearfix">
		<div class="col-md-12">
			<p class="back-to-top"><a href="#top"><?php _e('Back to top', 'largo'); ?> &uarr;</a></p>
			<?php largo_nav_menu(
				array(
					'theme_location' => 'footer-bottom',
					'container' => false,
					'depth' => 1
				) );
			?>
		</div>
	</div>
</div>
