<div class="sticky-nav-wrapper-banyan nocontent">
	<div class="sticky-nav-holder-banyan show" data-hide-at-top="false">

		<?php do_action( 'largo_before_sticky_nav_container' ); ?>

		<div class="sticky-nav-container">
			
			<nav id="sticky-nav" class="sticky-navbar navbar clearfix">
				
				<span class="visuallyhidden">
					<a href="#main" title="<?php esc_attr_e( 'Skip to content', 'largo' ); ?>"><?php _e( 'Skip to content', 'largo' ); ?></a>
				</span>
				<div class="container">
					<div class="nav-right">

						<ul id="header-extras">

							<li id="sticky-nav-search">
								<a href="#" class="toggle">
									<i class="icon-search" title="<?php esc_attr_e('Search', 'largo'); ?>" role="button"></i>
								</a>
								<form class="form-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
									<div class="input-append">
										<span class="text-input-wrapper">
											<input type="text" placeholder="<?php esc_attr_e('Search', 'largo'); ?>"
												class="input-medium appendedInputButton search-query" value="" name="s" />
										</span>
										<button type="submit" class="search-submit btn"><?php _e('Go', 'largo'); ?></button>
									</div>
								</form>
							</li>

							<?php if (!is_user_logged_in()) : ?>
								<li>
									<a href="/login">Log In</a>
								</li>						
							<?php endif; ?>

							<?php
							if ( !nb_is_member() && of_get_option( 'show_donate_button') ) {
								if ($donate_link = of_get_option('donate_link')) { ?>
								<li class="donate">
									<a class="donate-link" href="<?php echo esc_url($donate_link); ?>">
										<span><i class="icon-heart"></i><?php echo esc_html(of_get_option('donate_button_text')); ?></span>
									</a>
								</li><?php
								}
							} ?>
						</ul>
					</div>

					<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
					<a class="btn btn-navbar toggle-nav-bar" title="<?php esc_attr_e('More', 'largo'); ?>">
						<div class="bars">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</div>
					</a>

					<div class="nav-left">
							<ul>
								<li class="home-logo">
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
										<img src="/wp-content/themes/Largo-for-Banyan-Project/img/logo-knockout-01.png">
									</a>
								</li>

							</ul>
					</div>
					<div class="nav-shelf">

						<div class="wrap-navbar-twitter hidden-sm hidden-xs">
							<a href="<?php echo(esc_attr(of_get_option('twitter_link'))); ?>" class="twitter-follow-button" data-show-count="false">Follow <?php echo(bp_get_twitter_username('@')); ?></a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
						</div>
						
						<div class="wrap-navbar-facebook hidden-sm hidden-xs">
							<div class="fb-like" data-href="<?php echo(esc_attr(of_get_option('facebook_link'))); ?>" data-layout="button" data-action="like" data-show-faces="false" data-share="true"></div>
						</div>

						<ul class="nav">
							<li class="<?php echo (of_get_option('sticky_header_logo') == '' ? 'home-link' : 'home-logo' ) ?>">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
									<img src="/wp-content/themes/Largo-for-Banyan-Project/img/logo-knockout-01.png">
								</a>
							</li>
							<?php

								$args = array(
								'theme_location' => 'main-nav',
								'depth'		 => 0,
								'container'	 => false,
								'items_wrap' => '%3$s',
								'menu_class' => 'nav',
								'walker'	 => new Bootstrap_Walker_Nav_Menu()
								);
								largo_nav_menu($args);
	
								if ( !nb_is_member() && of_get_option( 'show_donate_button') ) {
									if ($donate_link = of_get_option('donate_link')) { ?>
									<li class="donate">
										<a class="donate-link" href="<?php echo esc_url($donate_link); ?>">
											<span><?php echo esc_html(of_get_option('donate_button_text')); ?></span>
										</a>
									</li><?php
									}
								}
	
							?>

						</ul>
					</div>
				</div>
			</nav>
		</div>
	</div>
</div>
