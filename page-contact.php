<?php
/**
 * Description: ContactForm
 */
  
require_once('inc/class.FormSubmission.php');
  
wp_enqueue_script(
	'contact',
	'/wp-content/themes/Largo-for-Banyan-Project/js/contact.js',
	array('jquery'),
	'0.1',
	true
);
  
class ContactForm extends FormSubmission {

	protected $dbtable = 'frm_contact';	
}


// has user submitted form
if (is_array($_POST) && $_POST['submitted'] === '1') {
		
	$form = new ContactForm;
	
	// anti-spam token
	if ($form->requiresToken()) {
		$form->checkToken();
	}	
	
	if ($form->isValid()) {
		
		// validation
		$form->validate('full_name','required');
		$form->validate('full_name','stringLength',array('maxlength' => 60));
		$form->validate('email','required');
		$form->validate('email','stringLength',array('maxlength' => 60));
		$form->validate('email','email');
		$form->validate('message','required');
		$form->validate('message','stringLength',array('maxlength' => 5000));
		$form->validate('email_signup','boolean');
	}
	
	$form->transform();
	
	if ($form->isValid()) {
		
		$form->adminMsg('admin-contact');
		$form->adminMsg()->setFrom(get_bloginfo('name'), of_get_option('from_email'));
		$form->adminMsg()->setReplyTo($form->outputValue('name'), $form->outputValue('email'));
		
		
		$form->adminMsg()->setVariable('affiliate_name',get_bloginfo('name'));
		$form->adminMsg()->setVariable('full_name',$form->outputValue('full_name'));
		$form->adminMsg()->setVariable('sender_email',$form->outputValue('email'));		
		$form->adminMsg()->setVariable('permalink',get_permalink());
		$form->adminMsg()->setContentMain($form->outputValue('message'));
		$form->adminMsg()->send();
		
		$form->userMsg('user-contact');
		$form->userMsg()->setFrom(get_bloginfo('name'), of_get_option());
		$form->userMsg()->setTo($form->outputValue('full_name'), $form->outputValue('email'));
		$form->userMsg()->setVariable('affiliate_name',get_bloginfo('name'));
		$form->userMsg()->setContentMain($form->outputValue('message'));
		$form->userMsg()->send();
		
		$form->setMandrillFlags();
	}

	$form->persist();
			
	if ($form->isValid()) {
		header("Location: ". home_url("/contact/thank-you"));
		exit;
	} else
		$response = $form->formatErrorMsgHtml();
}

 
global $shown_ids;

add_filter( 'body_class', function( $classes ) {
	$classes[] = 'normal';
	return $classes;
} );
     
get_header();

?>

<div id="content" class="col-md-8 col-md-offset-2" role="main">
	
	<?php
		while ( have_posts() ) : the_post();
			
			$shown_ids[] = get_the_ID();
			
			?>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php edit_post_link(__('Edit This Page', 'largo'), '<h5 class="byline"><span class="edit-link">', '</span></h5>'); ?>
				</header><!-- .entry-header -->
			
				<section class="entry-content">
					<?php the_content(); ?>
					
					<?php if ($response) : ?>
					<div class="alert alert-danger" role="alert">
						<?php echo $response; ?>
					</div>
					<?php endif; ?>
										
					<form action="<?php the_permalink(); ?>" method="post" id="contact-form">
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="full_name">Name: <span class="required" title="This field is required.">*</span></label>
							    <input type="text" name="full_name" value="<?php echo esc_attr($_POST['full_name']); ?>">
							</div>
						</div>
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="email">Email: <span class="required" title="This field is required.">*</span></label>
							    <input type="text" name="email" value="<?php echo esc_attr($_POST['email']); ?>">
							</div>
						</div>

						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="message">Message: <span class="required" title="This field is required.">*</span></label>
							    <textarea type="text" name="message" rows="6"><?php echo esc_textarea($_POST['message']); ?></textarea>
							</div>
						</div>
	
						<div class="checkbox">
							<label for="email_signup">
								<input type="checkbox" name="email_signup" value="1" checked> Sign-up to receive weekly email updates from <?php  bloginfo('name');  ?>.
							</label>	
						</div>

						<input type="hidden" name="load_timestamp" value="<?php echo(time()); ?>">
						<input type="hidden" name="submitted" value="1">
						<input type="submit" value="Submit" class="btn btn-primary">
					</form>
					
				</section><!-- .entry-content -->
			</article><!-- #post-<?php the_ID(); ?> -->
		
			<?php
		
			if ( is_active_sidebar( 'article-bottom' ) ) {

				do_action( 'largo_before_post_bottom_widget_area' );

				echo '<div class="article-bottom nocontent">';
				dynamic_sidebar( 'article-bottom' );
				echo '</div>';

				do_action( 'largo_after_post_bottom_widget_area' );

			}

		endwhile;
	?>
</div>

<?php do_action( 'largo_after_content' ); ?>

<?php get_footer();
