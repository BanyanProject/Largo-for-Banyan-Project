<?php
if ( is_front_page() || is_home() || !of_get_option( 'show_sticky_nav' ) ): ?>

<div class="masthead">
	<div class="wrap-logo">
		<h1>
			<a href="/">
				<img src="/wp-content/themes/Largo-BP/img/masthead-01.png" alt="">
			</a>
		</h1>
	</div>
</div>
<?php endif;
