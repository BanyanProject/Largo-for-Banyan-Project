<?php
/**
 * The template for displaying content in the single.php template
 */
 
$type = bp_get_post_type($post->ID);
$custom = get_post_custom( $post->ID );
  
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'hnews item' ); ?> itemscope itemtype="http://schema.org/Article">

	<?php do_action('largo_before_post_header'); ?>

	<header>
		
		<!-- Top Tag -->
		<?php if ($type->slug == 'event' && bp_custom_field_valid($custom,'start_date')) : ?>
		<h5 class="top-tag"><?php echo(date("F j",strtotime($custom['start_date'][0]))); ?></h5>
		<?php elseif ($type->slug == 'event') : ?>
		<h5 class="top-tag">Event</h5>
		<?php elseif (!is_page()) : ?>
		<h5 class="top-tag"><?php largo_top_term(); ?></h5>
		<?php endif; ?>
		
		<!-- Title -->
 		<h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>
 
 		<!-- Subtitle -->
 		<?php if (bp_custom_field_valid($custom,'subtitle')) : ?>		
 		<h2 class="subtitle"><?php echo($custom['subtitle'][0]); ?></h2>
 		<?php endif; ?>

	</header><!-- / entry header -->

	<?php
		do_action('largo_after_post_header');

		largo_hero();

		do_action('largo_after_hero');
	?>

	<div class="wrap-sm-content">
		<div class="row">
			<div class="col-md-sm">
				
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
							<span class="wrap-shareicon st-sharethis st_facebook_custom" >
								<div class="icon">
									<span class="sharetool-text">Share</span>
								</div>											
							</span>
						</li>					
	
						<li class="sharetool sharetool-twitter">
							<span class="wrap-shareicon st-sharethis st_twitter_custom" st_via="<?php echo(AFFILIATE_TWITTER); ?>" >
								<div class="icon">
									<span class="sharetool-text">Tweet</span>
								</div>											
							</span>
						</li>
										
						<?php if (has_post_thumbnail()) : ?>				
										
						<li class="sharetool sharetool-pinterest">
							<span class="wrap-shareicon st-sharethis st_pinterest_custom" >
								<div class="icon">
									<span class="sharetool-text">Pin</span>
								</div>											
							</span>
						</li>
						
						<?php endif; ?>
	 
	 					<li class="sharetool sharetool-email">
							<a href="mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>">
								<div class="icon">
									<span class="sharetool-text">Email</span>
								</div>
							</a>
						</li>
										
						<li class="sharetool sharetool-googleplus">
							<span class="wrap-shareicon st-sharethis st_googleplus_custom">
								<div class="icon">
									<span class="sharetool-text">Google+</span>
								</div>											
							</span>
						</li>
						
						<li class="sharetool sharetool-linkedin">
							<span class="wrap-shareicon st-sharethis st_linkedin_custom">
								<div class="icon">
									<span class="sharetool-text">LinkedIn</span>
								</div>											
							</a>
						</li>
						
					</ul>
					
				</div>			
			</div>
			
			<div class="col-md-content">
				
				<!-- Byline and Dateline -->
				<?php if (in_array($type->slug,array('news','blog'))) : ?>
				<h5 class="byline"><?php largo_byline(); ?></h5>
				<?php endif; ?>
	
				<?php if ($type->slug == 'event') : ?>
		
				<p class="event-datetime">
					
					<?php if (bp_custom_field_valid($custom,'start_date')) : ?>
					<span class="event-start-date"><?php echo(date("l, F j",strtotime($custom['start_date'][0]))); ?></span>
					<?php endif; ?>
					
					<?php if (bp_custom_field_valid($custom,'end_date') && $custom['end_date'][0] != $custom['start_date'][0]) : ?>
					- <span class="event-end-date"><?php echo(date("l, F j",strtotime($custom['end_date'][0]))); ?></span>
					<?php endif; ?>
					
					<br>
				
				<?php if (bp_custom_field_valid($custom,'start_time')) : ?>
					
					<span class="event-start-time"><?php echo(date("g:i a",strtotime($custom['start_time'][0]))); ?></span>
					
					<?php if (bp_custom_field_valid($custom,'end_time') && $custom['end_time'][0] != $custom['start_time'][0]) : ?>
					- <span class="event-end-time"><?php echo(date("g:i a",strtotime($custom['end_time'][0]))); ?></span>
					<?php endif; ?>	
										
				</p>	
				
				<?php endif; endif; ?>	
				
				<p class="location">
					
					<?php if (bp_custom_field_valid($custom,'location_title')) : ?>
					<span class="event-location-title"><?php echo($custom['location_title'][0]); ?></span><br>
					<?php endif; ?>
					
					<?php if (bp_custom_field_valid($custom,'address')) : ?>
					<span class="event-location-address"><?php echo($custom['address'][0]); ?></span><br>
					<?php endif; ?>
					
					<?php if (bp_custom_field_valid($custom,'city')) : ?>
					<span class="event-location-city"><?php echo($custom['city'][0]); ?></span>, 
					<?php endif; ?>
					
					<?php if (bp_custom_field_valid($custom,'state_province')) : ?>
					<span class="event-location-province"><?php echo($custom['state_province'][0]); ?></span>
					<?php endif; ?>
					
				</p>
							
				<?php if ($type->slug == 'event' && (bp_custom_field_valid($custom, 'event_url') || bp_custom_field_valid($custom, 'event_email'))) : ?> 
					
				<div class="event-info">
					<?php if (bp_custom_field_valid($custom,'event_url')) : ?>
					<a class="event-url" href="<?php echo($custom['event_url'][0]); ?>" target="_blank"><?php echo($custom['event_url'][0]); ?></a>
					<?php endif; ?>
					
					<?php if (bp_custom_field_valid($custom,'event_email')) : ?>
					<p class="event-email">Contact <a class="event-emai-link" href="mailto:<?php echo($custom['event_email'][0]); ?>"><?php echo($custom['event_email'][0]); ?></a> for more information.</p>
					<?php endif; ?>
				</div>
							
				<?php endif; ?>
						
			
				<div class="entry-content clearfix" itemprop="articleBody">
					<?php largo_entry_content( $post ); ?>
				</div><!-- .entry-content -->
	
				<?php do_action('largo_after_post_content'); ?>
	
				<footer class="post-meta bottom-meta">
			
					<!-- Google Map Embed -->
					<?php if (bp_custom_field_valid($custom,'google_map_embed')) : ?>	
					<div class="wrap-google-map">
						<?php 
							slt_cf_gmap('output','gmap',unserialize($custom['google_map_embed'][0]),600,450);
						?>
					</div>
					<?php endif; ?>
					
					<!-- Google Map Description -->		
					<?php if (bp_custom_field_valid($custom,'google_map_description')) : ?>
					<p class="google-map-descrption">
						<?php echo($custom['google_map_description'][0]); ?>
					</p>		
					<?php endif; ?>
	
					<?php if (bp_custom_field_valid($custom,'shirttail')) : ?>			
					<p class="shirttail">
						<?php echo($custom['shirttail'][0]); ?>
					</p>	
					<?php endif; ?>
			
					<!-- Author Bio -->
					
					<?php if (in_array($type->slug,array('news','blog')) && get_the_author_meta('description') != NULL) : ?>
					<p class="author-bio"><?php the_author_meta('description'); ?></p>	
					<?php endif; ?>	
		
				    <?php if ( of_get_option( 'clean_read' ) === 'footer' ) : ?>
				    	
				    	<div class="clean-read-container clearfix">
				 			<a href="#" class="clean-read"><?php _e("View as 'Clean Read'", 'largo') ?></a>
				 		</div>
				 		
				 	<?php endif; ?>
			
				</footer><!-- /.post-meta -->
			
				<?php do_action('largo_after_post_footer'); ?>
				
			</div>
		</div>	
	</div>

</article><!-- #post-<?php the_ID(); ?> -->

<?php if (in_array($type->slug, array('news','blog'))) : ?>

<div id="feedback-form">
	<h3><a href="#" data-toggle="modal" data-target="#article-feedback-window">Tell us what you think about this article.</a></h3>
	<p>
		<?php echo(AFFILIATE_NAME); ?> strives to create content that is relevant to your life, respectful of your values, and worthy of your trust.
		How did we do? 
	</p>
	
	<!-- Button trigger modal -->
	<div class="form-group">
		<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#article-feedback-window">
		  	Leave Feedback 
		</button>
	</div>
	
	<!-- Modal -->
	<div class="modal fade" id="article-feedback-window" tabindex="-1" role="dialog" aria-labelledby="article-feedback-label">
		<div class="modal-dialog" role="document">
		    <div class="modal-content">
		    	
			    <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h4 class="modal-title" id="article-feedback-label">Tell us what you think about this article.</h4>
			    </div>
			    
			    <div class="modal-body">
			    	<form id="article-feedback">
			    		
			    		<div class="form-group">
			    			Was this article relevant and respectful?
							<label class="radio-inline">
							  <input type="radio" name="relevant_respectful" id="rr-yes" value="1"> Yes
							</label>
							<label class="radio-inline">
							  <input type="radio" name="relevant_respectful" id="rr-no" value="0"> No
							</label>
						</div>
						
						<div class="form-group">
							<p>Please tell us why or why not. Or suggest related topics for <?php echo(AFFILIATE_NAME); ?> to cover.</p>
							<textarea class="form-control" rows="4" name="feedback"></textarea>
						</div>
						
						<div class="form-group">
							<select class="form-control" name="status">
								<option value="private">send my comments to the editor, and keep them private</option>
								<option value="public">send my comments to the editor, and publish them in the comments forum</option>
							</select>
						</div>
						
					</form>
			    </div>
			    
			    <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			        <button type="button" class="btn btn-primary">Send My Feedback</button>
			    </div>
		    </div>
		</div>
	</div>
		
</div>

<?php endif; ?>

<?php if (in_array($type->slug, array('news','blog','event'))) : ?>
	
<div id="comments-forum">
	<h3><?php largo_top_term(array('post' => get_the_ID(),'echo' => true,'link' => false)); ?> Forum</h3>
</div>

<?php endif; ?>

