
			<div class="wrap-global-nav visible-md visible-lg">

				<nav id="global-nav" class="navbar clearfix">
	
					<span class="visuallyhidden">
						<a href="#main" title="<?php esc_attr_e( 'Skip to content', 'largo' ); ?>"><?php _e( 'Skip to content', 'largo' ); ?></a>
					</span>
														
					<?php
						$top_args = array(
							'theme_location' => 'global-nav',
							'depth'		 => 1,
							'container'	 => false,
						);
						largo_nav_menu($top_args);
					?>
					
					
					<!--
					<div class="nav-right">
						
						<div class="wrap-navbar-twitter">
							<a href="<?php echo(esc_attr(of_get_option('twitter_link'))); ?>" class="twitter-follow-button" data-show-count="false">Follow @BanyanProject</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
						</div>
						
						<div class="wrap-navbar-facebook">
							<div class="fb-like" data-href="<?php echo(esc_attr(of_get_option('facebook_link'))); ?>" data-layout="button" data-action="like" data-show-faces="false" data-share="true"></div>
						</div>
						
					</div>
					-->
					
				</nav>
			</div>
