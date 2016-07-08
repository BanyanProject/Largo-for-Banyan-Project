							<article id="post-<?php echo $post->ID ?>" class="top-story clearfix">
					
								<header>	
						
										<?php if ($has_video = get_post_meta($post->ID, 'youtube_url', true)) { ?>
											<div class="embed-container">
												<iframe src="http://www.youtube.com/embed/<?php echo substr(strrchr($has_video, "="), 1 ); ?>?modestbranding=1" frameborder="0" allowfullscreen></iframe>
											</div>
										<?php } else { ?>
											<a class="head-image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a>
										<?php } ?>
			
										<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								</header>
								
								<div class="entry-content">
									<?php largo_excerpt($post, 5); ?>
								
									<?php get_template_part( 'partials/social', 'horizontal-small' ); ?>
									
									<?php if (largo_post_in_series()) {
										$feature = largo_get_the_main_feature();
										$feature_posts = largo_get_recent_posts_for_term($feature, 1, 1);
										if ($feature_posts) { ?>
											<ul> 
												<?php foreach ($feature_posts as $feature_post ) { ?>
													<li class="related-story">
														<a href="<?php echo esc_url( get_permalink( $feature_post->ID ) ); ?>"><?php echo get_the_title( $feature_post->ID ); ?></a>
													</li>
												<?php } ?>
											</ul>
										<?php }											
									} ?>
								</div>
		
								
							</article>
