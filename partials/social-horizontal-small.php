		<div class="wrap-sharetools-horizontal-small hidden-xs hidden-sm">

			<span class="sharetool sharetool-facebook st_facebook_custom" st_title="<?php the_title(); ?>" st_permalink="<?php the_permalink(); ?>">
				<div class="icon">
					<img width="18" height="18" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/facebook-gray-64.png">
				</div>
			</span>

			<span class="sharetool sharetool-twitter st_twitter_custom" st_title="<?php the_title(); ?>" st_permalink="<?php the_permalink(); ?>" st_via="<?php echo(bp_get_twitter_username()); ?>">
				<div class="icon">
					<img width="18" height="18" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/twitter-gray-64.png">
				</div>
			</span>

			<?php if (has_post_thumbnail()) : ?>
			<span class="sharetool sharetool-pinterest st_pinterest_custom" st_title="<?php the_title(); ?>" st_permalink="<?php the_permalink(); ?>">
				<div class="icon">
					<img width="18" height="18" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/pinterest-gray-64.png">
				</div>
			</span>
			<?php endif; ?>

			<span class="sharetool sharetool-googleplus st_googleplus_custom" st_title="<?php the_title(); ?>" st_permalink="<?php the_permalink(); ?>">
				<div class="icon">
					<img width="18" height="18" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/googleplus-gray-64.png">
				</div>
			</span>
			
		</div>
