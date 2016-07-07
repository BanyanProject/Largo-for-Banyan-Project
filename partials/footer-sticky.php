<?php if( is_singular( 'post' ) && !nb_is_member()): ?>
<div class="sticky-footer-holder">
	<div class="sticky-footer-container">
		<p>Take ownership of your hometown. <a href="/membership">Become a Member of <?php bloginfo('name'); ?></a>.</p>
		
		<div class="dismiss">
			<a href="#">
			<i class="icon-cancel"></i>
			</a>
		</div>
		
	</div>
</div>
<?php endif; ?>
