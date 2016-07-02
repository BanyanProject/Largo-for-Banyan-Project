				<div class="wrap-sharetools-vertical hidden-xs hidden-sm">
					<ul class="sharetools-list">
						
						<li class="sharetool sharetool-facebook-like">
							<div class="wrap-facebook-like">
								 <div class="fb-like" 
							        data-href="<?php the_permalink(); ?>" 
							        data-layout="button_count" 
							        data-action="like" 
							        data-show-faces="false">
	    						</div>
							</div>
						</li>
											
						<li class="sharetool sharetool-facebook">
							<span class="wrap-shareicon st-sharethis st_facebook_custom" st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>">
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/facebook-64.png">
									<span class="sharetool-text">Share</span>
								</div>											
							</span>
						</li>					
	
						<li class="sharetool sharetool-twitter">
							<span class="wrap-shareicon st-sharethis st_twitter_custom"  st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>  st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>" st_via="<?php echo(bp_get_twitter_username()); ?>" >
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/twitter-64.png">
									<span class="sharetool-text">Tweet</span>
								</div>											
							</span>
						</li>
										
						<?php if (has_post_thumbnail()) : ?>				
										
						<li class="sharetool sharetool-pinterest">
							<span class="wrap-shareicon st-sharethis st_pinterest_custom" st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>" >
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/pinterest-64.png">
									<span class="sharetool-text">Pin</span>
								</div>											
							</span>
						</li>
						
						<?php endif; ?>
	 
	 					<li class="sharetool sharetool-email">
							<a href="mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>">
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/email-64.png">
									<span class="sharetool-text">Email</span>
								</div>
							</a>
						</li>
										
						<li class="sharetool sharetool-googleplus">
							<span class="wrap-shareicon st-sharethis st_googleplus_custom"  st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>">
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/googleplus-64.png">
									<span class="sharetool-text">Google+</span>
								</div>											
							</span>
						</li>
						
						<li class="sharetool sharetool-linkedin">
							<span class="wrap-shareicon st-sharethis st_linkedin_custom"  st_title="<?php the_title(); ?>" st_url="<?php the_permalink(); ?>" >
								<div class="icon">
									<img width="24" height="24" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/linkedin-64.png">
									<span class="sharetool-text">LinkedIn</span>
								</div>											
							</a>
						</li>
						
					</ul>
					
				</div>			
