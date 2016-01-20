<?php
if ( is_front_page() || is_home() || !of_get_option( 'show_sticky_nav' ) ): ?>
	<div class="global-nav-bg">
		<div class="global-nav">
			<nav id="top-nav">

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
				<div class="nav-right">
					<?php if ( of_get_option( 'show_header_social') ) { ?>
						<ul id="header-social" class="social-icons visible-desktop">
							<?php largo_social_links(); ?>
						</ul>
					<?php } ?>

					<?php if (!is_user_logged_in() || of_get_option( 'show_donate_button')) : ?>
						
						<ul class="menu">
							
						<?php if (!is_user_logged_in()) : ?>
							<li>
								<a href="/login">Login</a>
							</li>	

						<?php endif; ?>

						<?php if ( of_get_option( 'show_donate_button') ) :
								if ($donate_link = of_get_option('donate_link')) : ?>
								
								<li class="donate">
									<a class="donate-link" href="<?php echo esc_url($donate_link); ?>">
										<span><?php echo esc_html(of_get_option('donate_button_text')); ?></span>
									</a>
								</li>
						<?php endif; endif; ?>
						
						</ul>

					<?php endif; ?>
					
				</div>
			</nav>
		</div> <!-- /.global-nav -->
	</div> <!-- /.global-nav-bg -->
<?php endif;
