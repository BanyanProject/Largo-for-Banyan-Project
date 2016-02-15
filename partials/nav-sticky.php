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
									<a href="/login">Login</a>
								</li>						
							<?php endif; ?>

							<?php
							if ( of_get_option( 'show_donate_button') ) {
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
						<?php
							if ( of_get_option( 'show_sitename_in_sticky_nav', 1 ) ) {
								echo '<li class="site-name"><a href="/">' . get_bloginfo('name') . '</a></li>';
							} else if ( of_get_option( 'sticky_header_logo' ) == '' ) { ?>
								<li class="home-link"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php largo_home_icon( 'icon-white' ); ?></a></li>
							<?php } else { ?>
								<li class="home-logo">
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
									<img src="/wp-content/uploads/2015/10/sticky-header-logo-275.png"></a>
								</li>

						<?php } ?>
					</div>
					<div class="nav-shelf">
					<ul class="nav">
						<li class="<?php echo (of_get_option('sticky_header_logo') == '' ? 'home-link' : 'home-logo' ) ?>">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
								<img src="/wp-content/uploads/2015/10/sticky-header-logo-275.png"></a>
							</a>
						</li>
						<?php
							if ( of_get_option( 'show_sitename_in_sticky_nav', 1 ) )
								echo '<li class="site-name"><a href="/">' . get_bloginfo('name') . '</a></li>';

							$args = array(
							'theme_location' => 'main-nav',
							'depth'		 => 0,
							'container'	 => false,
							'items_wrap' => '%3$s',
							'menu_class' => 'nav',
							'walker'	 => new Bootstrap_Walker_Nav_Menu()
							);
							largo_nav_menu($args);

							if ( of_get_option( 'show_donate_button') ) {
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
