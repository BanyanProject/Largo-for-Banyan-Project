<?php
if (is_category()) {
	$title = single_cat_title('', false) . ' Forum';
	$permalink = get_category_link(get_queried_object_id());	
}
?>

				<div class="wrap-sharetools-horizontal">
																	
						<span class="sharetool sharetool-facebook">
							
							<?php if (is_category()) : ?>
							<span class="wrap-shareicon st-sharethis st_facebook_custom" st_title="<?php echo($title); ?>" st_url="<?php echo($permalink); ?>">
							<?php else : ?>	
							<span class="wrap-shareicon st-sharethis st_facebook_custom" st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>">
							<?php endif; ?>
								
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/facebook-64.png">
									<span class="sharetool-text">Share</span>
								</div>											
							</span>
						</span>					
	
						<span class="sharetool sharetool-twitter">
							
							<?php if (is_category()) : ?>
							<span class="wrap-shareicon st-sharethis st_twitter_custom" st_title="<?php echo($title); ?>" st_url="<?php echo($permalink); ?>" st_via="<?php echo(bp_get_twitter_username()); ?>" >
							<?php else : ?>	
							<span class="wrap-shareicon st-sharethis st_twitter_custom" st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>" st_via="<?php echo(bp_get_twitter_username()); ?>">
							<?php endif; ?>
							
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/twitter-64.png">
									<span class="sharetool-text">Tweet</span>
								</div>											
							</span>
						</span>
										
						<?php if (!is_category() && has_post_thumbnail()) : ?>				
										
						<span class="sharetool sharetool-pinterest hidden-xs">
							<span class="wrap-shareicon st-sharethis st_pinterest_custom" >
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/pinterest-64.png">
									<span class="sharetool-text">Pin</span>
								</div>											
							</span>
						</span>
						
						<?php endif; ?>
	 
	 					<span class="sharetool sharetool-email hidden-xs">
							<?php if (is_category()) : ?>
							<a href="mailto:?subject=<?php echo($title); ?>&amp;body=<?php echo($permalink); ?>">
							<?php else : ?>	
							<a href="mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>">
							<?php endif; ?>
								
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/email-64.png">
									<span class="sharetool-text">Email</span>
								</div>
							</a>
						</span>
										
						<span class="sharetool sharetool-googleplus hidden-xs hidden-sm">
							<?php if (is_category()) : ?>
							<span class="wrap-shareicon st-sharethis st_googleplus_custom" st_title="<?php echo($title); ?>" st_url="<?php echo($permalink); ?>">
							<?php else : ?>	
							<span class="wrap-shareicon st-sharethis st_googleplus_custom" st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>">
							<?php endif; ?>
								
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/googleplus-64.png">
									<span class="sharetool-text">Google+</span>
								</div>											
							</span>
						</span>
						
						<span class="sharetool sharetool-linkedin hidden-xs hidden-sm">
							<?php if (is_category()) : ?>
							<span class="wrap-shareicon st-sharethis st_linkedin_custom" st_title="<?php echo($title); ?>" st_url="<?php echo($permalink); ?>">
							<?php else : ?>	
							<span class="wrap-shareicon st-sharethis st_linkedin_custom" st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>">
							<?php endif; ?>
								
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/linkedin-64.png">
									<span class="sharetool-text">LinkedIn</span>
								</div>											
							</span>
						</span>

						<?php if (is_category()) : ?>

						<span class="sharetool sharetool-rss">
							<a href="<?php echo $rss_link; ?>">
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/rss-64.png">
									<span class="sharetool-text">RSS</span>
								</div>											
							</a>
						</span>
						
						<?php endif; ?>
					
				</div>			
